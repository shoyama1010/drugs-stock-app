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
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'lot_number' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'shelf' => ['required', 'string', 'max:255'],
            'expiry_date' => ['nullable', 'date'],
        ], [
            'product_id.required' => '商品を選択してください。',
            'product_id.exists' => '選択した商品が存在しません。',
            'lot_number.required' => 'ロット番号を入力してください。',
            'quantity.required' => '数量を入力してください。',
            'quantity.integer' => '数量は整数で入力してください。',
            'quantity.min' => '数量は1以上で入力してください。',
            'shelf.required' => '棚番号を入力してください。',
            'expiry_date.date' => '期限日は正しい日付形式で入力してください。',
        ]);

        return DB::transaction(function () use ($request) {
            $requestedQuantity = (int) $request->quantity;
            $remainingQuantity = $requestedQuantity;

            // ① 棚番号を分解する
            $formattedShelf = strtoupper(trim($request->shelf));
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
                'product_id' => $request->product_id,
                'lot_number' => $request->lot_number,
                'quantity_total' => $requestedQuantity,
                'received_at' => now(),
                'expiry_date' => $request->expiry_date,
            ]);

            // ⑦ 在庫ロケーション作成
            foreach ($allocations as $allocation) {
                StockLotLocation::create([
                    'stock_lot_id' => $lot->id,
                    'location_id' => $allocation['location_id'],
                    'quantity_initial' => $allocation['quantity'],
                    'quantity_remaining' => $allocation['quantity'],
                ]);

                // 各棚に割り当てた分ごとに履歴記録
                Transaction::create([
                    'product_id' => $request->product_id,
                    'stock_lot_id' => $lot->id,
                    // 'user_id' => auth()->id(),
                    'user_id' => 1, // 仮でadminユーザーIDを指定
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
    public function stockOut(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'location_id' => ['required', 'integer', 'exists:locations,id'], // 在庫一覧から棚を選ぶ
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ], [
            'product_id.required' => '商品を選択してください。',
            'product_id.exists' => '選択した商品が存在しません。',
            'location_id.required' => '棚を選択してください。', // 在庫一覧から棚を選ぶ
            'quantity.required' => '出庫数を入力してください。',
            'quantity.integer' => '出庫数は整数で入力してください。',
            'quantity.min' => '出庫数は1以上で入力してください。',
            'reason.string' => '出庫理由は文字列で入力してください。',
            'reason.max' => '出庫理由は1000文字以内で入力してください。',
        ]);

        return DB::transaction(function () use ($request) {
            $product = Product::find($request->product_id);

            if (!$product) {
                return response()->json([
                    'message' => '商品が存在しません。'
                ], 404);
            }

            $location = Location::find($request->location_id);

            if (!$location) {
                return response()->json([
                    'message' => '棚が存在しません。'
                ], 404);
            }

            // 指定棚にある、この商品の在庫合計
            $locationStock = StockLotLocation::join('stock_lots', 'stock_lot_locations.stock_lot_id', '=', 'stock_lots.id')
                ->where('stock_lots.product_id', $request->product_id)
                ->where('stock_lot_locations.location_id', $request->location_id)
                ->sum('stock_lot_locations.quantity_remaining');

            if ($locationStock < $request->quantity) {
                return response()->json([
                    'message' => '指定した棚の在庫数が不足しています。',
                    'location_stock' => $locationStock,
                    'requested_quantity' => (int) $request->quantity,
                ], 422);
            }

            // 指定棚にある,FIFOで古いロット順に取得
            $lotLocations = StockLotLocation::join('stock_lots', 'stock_lot_locations.stock_lot_id', '=', 'stock_lots.id')
                ->where('stock_lots.product_id', $request->product_id)
                ->where('stock_lot_locations.location_id', $request->location_id) // 在庫一覧から棚を選ぶ
                ->where('stock_lot_locations.quantity_remaining', '>', 0)
                ->orderBy('stock_lots.received_at', 'asc')
                ->orderBy('stock_lots.id', 'asc')
                ->select(
                    'stock_lot_locations.id',
                    'stock_lot_locations.stock_lot_id',
                    'stock_lot_locations.location_id',
                    'stock_lot_locations.quantity_remaining',
                    'stock_lots.received_at',
                    'stock_lots.lot_number'
                )
                ->lockForUpdate()
                ->get();

            $remainingToShip = (int) $request->quantity;
            $deductions = [];

            foreach ($lotLocations as $lotLocation) {
                if ($remainingToShip <= 0) {
                    break;
                }

                $available = (int) $lotLocation->quantity_remaining;
                $deductQty = min($available, $remainingToShip);

                StockLotLocation::where('id', $lotLocation->id)->update([
                    'quantity_remaining' => $available - $deductQty,
                    'updated_at' => now(),
                ]);

                Transaction::create([
                    'product_id' => $request->product_id,
                    'stock_lot_id' => $lotLocation->stock_lot_id,
                    // 'user_id' => auth()->id() ?? 1,
                    'user_id' => 1, // 仮でadminユーザーIDを指定
                    'type' => 'out',
                    'quantity' => $deductQty,
                    'location_id' => $lotLocation->location_id,
                    'note' => $request->reason,
                ]);

                $deductions[] = [
                    'stock_lot_location_id' => $lotLocation->id,
                    'stock_lot_id' => $lotLocation->stock_lot_id,
                    'lot_number' => $lotLocation->lot_number,
                    'location_id' => $lotLocation->location_id,
                    'deducted_quantity' => $deductQty,
                ];

                $remainingToShip -= $deductQty;
            }

            // 出庫後の残在庫
            $afterStock = StockLotLocation::join('stock_lots', 'stock_lot_locations.stock_lot_id', '=', 'stock_lots.id')
                ->where('stock_lots.product_id', $request->product_id)
                ->where('stock_lot_locations.location_id', $request->location_id) // 在庫一覧から棚を選ぶ
                ->sum('stock_lot_locations.quantity_remaining');

            $alertMessage = null;
            $status = '十分';

            $minStock = (int) ($product->min_stock ?? 0);

            if ($afterStock === 0) {
                $status = '在庫切れ';
                $alertMessage = '在庫がありません。補充が必要です。';
            } elseif ($minStock > 0 && $afterStock <= $minStock) {
                $status = '少量';
                $alertMessage = '在庫が少なくなっています。倉庫から補充してください。';
            }

            return response()->json([
                'message' => '出庫が完了しました。',
                'product_id' => $product->id,
                'product_name' => $product->name,
                'location_id' => $location->id, // 在庫一覧から棚を選ぶ
                'location_code' => $location->zone . '-' . $location->aisle . '-' . $location->shelf, // 在庫一覧から棚を選ぶ
                'shipped_quantity' => (int) $request->quantity,
                'remaining_stock' => $afterStock,
                'status' => $status,
                'alert_message' => $alertMessage,
                'deductions' => $deductions,
            ], 200);
        });
    }
}
