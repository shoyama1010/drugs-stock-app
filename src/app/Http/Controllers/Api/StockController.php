<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockLot;
use App\Models\Location;
use App\Models\StockLotLocation;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Transaction;
use App\Http\Requests\StockInRequest;
use App\Http\Requests\StockOutRequest;

class StockController extends Controller
{
    public function index()
    {
        $stocks = DB::table('stock_lot_locations')
            ->join('stock_lots', 'stock_lot_locations.stock_lot_id', '=', 'stock_lots.id')
            ->join('products', 'stock_lots.product_id', '=', 'products.id')
            ->join('locations', 'stock_lot_locations.location_id', '=', 'locations.id')
            ->select(
                'products.id as product_id',
                'products.name',
                'products.sku',
                'locations.id as location_id', // 在庫一覧から棚を選ぶため
                'locations.zone', // 👈追加'locations.zone'
                'locations.aisle',   // ←必須
                'locations.shelf',
                DB::raw('SUM(stock_lot_locations.quantity_remaining) as total_stock'),
                DB::raw('MAX(stock_lot_locations.updated_at) as updated_at') // 👈追加
            )
            ->groupBy(
                'products.id',
                'products.name',
                'products.sku',
                'locations.id', // 在庫一覧から棚を選ぶため
                'locations.zone', // 👈追加
                'locations.aisle',   // ←必須
                'locations.shelf'
            )
            ->orderBy('products.id')
            ->orderBy('locations.zone')
            ->orderBy('locations.aisle')
            ->orderBy('locations.shelf')
            ->get();
        return response()->json($stocks);
    }

    // 🟢 入庫
    public function store(StockInRequest $request)
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated) {
            $requestedQuantity = (int) $validated['quantity'];
            $remainingQuantity = $requestedQuantity;

            // ① 棚番号を分解する
            $formattedShelf = strtoupper(trim($validated['shelf']));
            $parts = explode('-', $formattedShelf);

            if (count($parts) !== 3) {
                return response()->json([
                    'message' => '棚番号の形式が不正です。例: A-1-01'
                ], 422);
            }

            [$zone, $aisle, $shelf] = $parts;

            // aisle と shelf を整形
            $aisle = (string) intval($aisle); // 01 → 1
            $shelf = str_pad((string) intval($shelf), 2, '0', STR_PAD_LEFT); // 1 → 01

            // ② 指定棚を取得
            $primaryLocation = Location::where('zone', $zone)
                ->where('aisle', $aisle)
                ->where('shelf', $shelf)
                ->first();

            if (!$primaryLocation) {
                return response()->json([
                    'message' => '棚番号が存在しません'
                ], 422);
            }

            // ③ 指定棚が使用中か確認
            $primaryUsed = StockLotLocation::where('location_id', $primaryLocation->id)
                ->where('quantity_remaining', '>', 0)
                ->exists();

            if ($primaryUsed) {
                return response()->json([
                    'message' => '指定した棚は使用中です。空棚を指定してください。'
                ], 422);
            }

            // ④ 指定棚 + 他の空棚候補
            $candidateLocations = collect([$primaryLocation]);

            $otherEmptyLocations = Location::where('id', '!=', $primaryLocation->id)
                ->whereNotIn('id', function ($query) {
                    $query->select('location_id')
                        ->from('stock_lot_locations')
                        ->where('quantity_remaining', '>', 0);
                })
                ->orderBy('zone')
                ->orderBy('aisle')
                ->orderBy('shelf')
                ->get();

            $candidateLocations = $candidateLocations->concat($otherEmptyLocations);

            // ⑤ 入庫割当
            $allocations = [];

            foreach ($candidateLocations as $location) {
                if ($remainingQuantity <= 0) {
                    break;
                }

                $capacity = (int) ($location->capacity ?? 0);

                if ($capacity <= 0) {
                    continue;
                }

                $putQuantity = min($remainingQuantity, $capacity);

                if ($putQuantity > 0) {
                    $allocations[] = [
                        'location_id' => $location->id,
                        'quantity' => $putQuantity,
                    ];

                    $remainingQuantity -= $putQuantity;
                }
            }

            if ($remainingQuantity > 0) {
                return response()->json([
                    'message' => '空棚の容量が不足しているため、すべて入庫できません。',
                    'requested_quantity' => $requestedQuantity,
                    'unallocated_quantity' => $remainingQuantity,
                ], 422);
            }

            // ⑥ ロット作成
            $lot = StockLot::create([
                'product_id' => $validated['product_id'],
                'lot_number' => $validated['lot_number'],
                'quantity_total' => $requestedQuantity,
                'received_at' => now(),
                'expiry_date' => $validated['expiry_date'] ?? null,
            ]);

            // ⑦ 在庫ロケーション作成
            foreach ($allocations as $allocation) {
                StockLotLocation::create([
                    'stock_lot_id' => $lot->id,
                    'location_id' => $allocation['location_id'],
                    'quantity_initial' => $allocation['quantity'],
                    'quantity_remaining' => $allocation['quantity'],
                ]);

                Transaction::create([
                    'product_id' => $validated['product_id'],
                    'stock_lot_id' => $lot->id,
                    'user_id' => auth()->id(),
                    // 'user_id' => 1,
                    'type' => 'in',
                    'quantity' => $allocation['quantity'],
                    'location_id' => $allocation['location_id'],
                    'note' => '入庫',
                ]);
            }

            // ⑧ 結果返却
            $allocationResults = collect($allocations)->map(function ($allocation) {
                $location = Location::find($allocation['location_id']);

                return [
                    'location_id' => $allocation['location_id'],
                    'shelf' => $location
                        ? ($location->zone . '-' . $location->aisle . '-' . $location->shelf)
                        : null,
                    'quantity' => $allocation['quantity'],
                ];
            });

            return response()->json([
                'message' => '入庫完了',
                'lot_id' => $lot->id,
                'allocations' => $allocationResults,
            ], 200);
        });
    }

    // 🟢 出庫機能
    public function stockOut(StockOutRequest $request)
    {
        $validated = $request->validated();

        $productId  = $validated['product_id'];
        $locationId = $validated['location_id'];
        $quantity   = (int) $validated['quantity'];
        $reason     = $validated['reason'];

        return DB::transaction(function () use ($productId, $locationId, $quantity, $reason) {
            $slotLocations = StockLotLocation::with('stockLot')
                ->where('location_id', $locationId)
                ->where('quantity_remaining', '>', 0)
                ->whereHas('stockLot', function ($query) use ($productId) {
                    $query->where('product_id', $productId);
                })
                ->join('stock_lots', 'stock_lot_locations.stock_lot_id', '=', 'stock_lots.id')
                ->orderBy('stock_lots.received_at', 'asc')
                ->select('stock_lot_locations.*')
                ->get();

            if ($slotLocations->isEmpty()) {
                return response()->json([
                    'message' => '指定した棚に対象商品の在庫がありません。'
                ], 422);
            }

            $totalStock = $slotLocations->sum('quantity_remaining');

            if ($totalStock < $quantity) {
                return response()->json([
                    'message' => '指定した棚の在庫数が不足しています。'
                ], 422);
            }

            $remainingToRemove = $quantity;

            foreach ($slotLocations as $slotLocation) {
                if ($remainingToRemove <= 0) {
                    break;
                }

                $available = (int) $slotLocation->quantity_remaining;
                $removeQty = min($remainingToRemove, $available);

                $slotLocation->update([
                    'quantity_remaining' => $available - $removeQty,
                ]);

                Transaction::create([
                    'product_id'  => $productId,
                    'stock_lot_id' => $slotLocation->stock_lot_id,
                    'user_id' => auth()->id(),
                    // 'user_id'     => 1,
                    'type'        => 'out',
                    'quantity'    => $removeQty,
                    'location_id' => $locationId,
                    'note'        => $reason,
                ]);

                $remainingToRemove -= $removeQty;
            }

            return response()->json([
                'message' => '出庫完了',
            ], 200);
        });
    }

}
