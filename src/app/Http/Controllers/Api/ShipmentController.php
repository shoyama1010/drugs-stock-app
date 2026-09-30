<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\StockLotLocation;
use App\Models\Store;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ShipmentController extends Controller
{
    public function stores(Request $request)
    {
        $this->ensureWarehouseUser($request);

        return response()->json(Store::query()->orderBy('store_code')->get());
    }

    public function index(Request $request)
    {
        $this->ensureWarehouseUser($request);

        $query = Shipment::query()->with($this->relations())->latest('requested_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return response()->json(
            $query->get()->map(fn (Shipment $shipment) => $this->formatShipment($shipment))
        );
    }

    public function store(Request $request)
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('products', 'id')->where('is_active', true),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        $shipment = DB::transaction(function () use ($validated, $request) {
            $shipment = Shipment::create([
                'store_id' => $validated['store_id'],
                'user_id' => $request->user()->id,
                'status' => Shipment::STATUS_REQUESTED,
                'requested_at' => now(),
                'note' => $validated['note'] ?? null,
            ]);

            $shipment->update([
                'shipment_number' => sprintf('SHP-%s-%06d', now()->format('Ymd'), $shipment->id),
            ]);

            foreach ($validated['items'] as $item) {
                $shipment->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'shipped_quantity' => 0,
                ]);
            }

            return $shipment;
        });

        return response()->json(
            $this->formatShipment($shipment->load($this->relations())),
            201
        );
    }

    public function show(Request $request, Shipment $shipment)
    {
        $this->ensureWarehouseUser($request);

        return response()->json(
            $this->formatShipment($shipment->load($this->relations()))
        );
    }

    public function confirmOut(Request $request, Shipment $shipment)
    {
        $this->ensureWarehouseUser($request);

        $shipment = DB::transaction(function () use ($shipment, $request) {
            $lockedShipment = Shipment::query()
                ->whereKey($shipment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedShipment->status !== Shipment::STATUS_REQUESTED) {
                throw ValidationException::withMessages([
                    'status' => ['出庫確定できるのは出荷指示済みの伝票だけです。'],
                ]);
            }

            $lockedShipment->load([
                'items' => fn ($query) => $query->orderBy('product_id')->orderBy('id'),
                'store',
            ]);

            foreach ($lockedShipment->items as $item) {
                $stockLocations = StockLotLocation::query()
                    ->join('stock_lots', 'stock_lot_locations.stock_lot_id', '=', 'stock_lots.id')
                    ->where('stock_lots.product_id', $item->product_id)
                    ->where('stock_lot_locations.quantity_remaining', '>', 0)
                    ->orderBy('stock_lots.received_at')
                    ->orderBy('stock_lots.id')
                    ->orderBy('stock_lot_locations.location_id')
                    ->orderBy('stock_lot_locations.id')
                    ->select('stock_lot_locations.*')
                    ->lockForUpdate()
                    ->get();

                if ($stockLocations->sum('quantity_remaining') < $item->quantity) {
                    throw ValidationException::withMessages([
                        'stock' => ["商品ID {$item->product_id} の在庫が不足しています。"],
                    ]);
                }

                $remaining = (int) $item->quantity;

                foreach ($stockLocations as $stockLocation) {
                    if ($remaining === 0) {
                        break;
                    }

                    $available = (int) $stockLocation->quantity_remaining;
                    $quantity = min($remaining, $available);

                    $stockLocation->update([
                        'quantity_remaining' => $available - $quantity,
                    ]);

                    $item->allocations()->create([
                        'stock_lot_id' => $stockLocation->stock_lot_id,
                        'location_id' => $stockLocation->location_id,
                        'quantity' => $quantity,
                    ]);

                    Transaction::create([
                        'product_id' => $item->product_id,
                        'stock_lot_id' => $stockLocation->stock_lot_id,
                        'user_id' => $request->user()->id,
                        'type' => 'out',
                        'quantity' => $quantity,
                        'location_id' => $stockLocation->location_id,
                        'store_id' => $lockedShipment->store_id,
                        'shipment_id' => $lockedShipment->id,
                        'shipment_item_id' => $item->id,
                        'note' => "出荷 {$lockedShipment->shipment_number} / {$lockedShipment->store->name}",
                    ]);

                    $remaining -= $quantity;
                }

                $item->update(['shipped_quantity' => $item->quantity]);
            }

            $lockedShipment->update([
                'status' => Shipment::STATUS_CONFIRMED,
                'confirmed_by' => $request->user()->id,
                'confirmed_at' => now(),
            ]);

            return $lockedShipment;
        });

        return response()->json(
            $this->formatShipment($shipment->load($this->relations()))
        );
    }

    public function slip(Request $request, Shipment $shipment)
    {
        $this->ensureWarehouseUser($request);

        if (!in_array($shipment->status, [
            Shipment::STATUS_CONFIRMED,
            Shipment::STATUS_SHIPPED,
            Shipment::STATUS_DELIVERED,
        ], true)) {
            throw ValidationException::withMessages([
                'status' => ['出荷伝票は出庫確定後に発行できます。'],
            ]);
        }

        return response()->json(
            $this->formatShipment($shipment->load($this->relations()))
        );
    }

    public function dispatch(Request $request, Shipment $shipment)
    {
        $this->ensureAdmin($request);

        if ($shipment->status !== Shipment::STATUS_CONFIRMED) {
            throw ValidationException::withMessages([
                'status' => ['配送開始できるのは出庫確定済みの伝票だけです。'],
            ]);
        }

        $shipment->update([
            'status' => Shipment::STATUS_SHIPPED,
            'shipped_at' => now(),
        ]);

        return response()->json(
            $this->formatShipment($shipment->load($this->relations()))
        );
    }

    public function deliver(Request $request, Shipment $shipment)
    {
        $this->ensureAdmin($request);

        if ($shipment->status !== Shipment::STATUS_SHIPPED) {
            throw ValidationException::withMessages([
                'status' => ['配送完了できるのは配送中の伝票だけです。'],
            ]);
        }

        $shipment->update([
            'status' => Shipment::STATUS_DELIVERED,
            'delivered_by' => $request->user()->id,
            'delivered_at' => now(),
        ]);

        return response()->json(
            $this->formatShipment($shipment->load($this->relations()))
        );
    }

    private function relations(): array
    {
        return [
            'store',
            'creator',
            'confirmer',
            'deliverer',
            'items.product',
            'items.allocations.stockLot',
            'items.allocations.location',
        ];
    }

    private function formatShipment(Shipment $shipment): array
    {
        return [
            'id' => $shipment->id,
            'shipment_number' => $shipment->shipment_number,
            'status' => $shipment->status,
            'status_label' => $this->statusLabel($shipment->status),
            'store' => $shipment->store,
            'created_by' => $shipment->creator?->name,
            'confirmed_by' => $shipment->confirmer?->name,
            'delivered_by' => $shipment->deliverer?->name,
            'requested_at' => $shipment->requested_at?->toISOString(),
            'confirmed_at' => $shipment->confirmed_at?->toISOString(),
            'shipped_at' => $shipment->shipped_at?->toISOString(),
            'delivered_at' => $shipment->delivered_at?->toISOString(),
            'note' => $shipment->note,
            'total_quantity' => $shipment->items->sum('quantity'),
            'items' => $shipment->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name,
                    'sku' => $item->product?->sku,
                    'quantity' => $item->quantity,
                    'shipped_quantity' => $item->shipped_quantity,
                    'allocations' => $item->allocations->map(function ($allocation) {
                        return [
                            'lot_number' => $allocation->stockLot?->lot_number,
                            'location' => $allocation->location
                                ? "{$allocation->location->zone}-{$allocation->location->aisle}-{$allocation->location->shelf}"
                                : null,
                            'quantity' => $allocation->quantity,
                        ];
                    })->values(),
                ];
            })->values(),
        ];
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            Shipment::STATUS_REQUESTED => '出荷指示済み',
            Shipment::STATUS_CONFIRMED => '出庫確定済み',
            Shipment::STATUS_SHIPPED => '配送中',
            Shipment::STATUS_DELIVERED => '配送完了',
            default => $status,
        };
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403, '管理者権限が必要です。');
    }

    private function ensureWarehouseUser(Request $request): void
    {
        $user = $request->user();
        $isAdmin = $user?->role === 'admin';
        $isActiveStaff = $user?->role === 'staff' && $user->is_active;

        abort_unless(
            $isAdmin || $isActiveStaff,
            403,
            '出庫権限がありません。'
        );
    }
}
