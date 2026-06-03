<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'orders' => $orders,
        ]);
    }

    public function adminIndex()
    {
        $orders = Order::with(['user', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'orders' => $orders,
        ]);
    }

    public function adminShow($id)
    {
        $order = Order::with(['user', 'items.product'])
            ->findOrFail($id);

        return response()->json([
            'order' => $order,
        ]);
    }

    private const TRANSITIONS = [
        'pending'  => 'shipping',
        'shipping' => 'completed',
    ];

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', Rule::in(['shipping', 'completed'])],
        ]);

        $next = self::TRANSITIONS[$order->status] ?? null;

        if ($next !== $request->status) {
            return response()->json(['message' => "狀態無法從 {$order->status} 轉換為 {$request->status}"], 422);
        }

        $order->update(['status' => $request->status]);

        return response()->json(['message' => '狀態已更新', 'status' => $order->status]);
    }

    public function batchUpdateStatus(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array|max:100',
            'ids.*'  => 'integer|exists:orders,id',
            'status' => ['required', Rule::in(['shipping', 'completed'])],
        ]);

        $targetStatus = $request->status;
        $requiredCurrent = array_search($targetStatus, self::TRANSITIONS);

        if ($requiredCurrent === false) {
            return response()->json(['message' => '無效的目標狀態'], 422);
        }

        try {
            DB::transaction(function () use ($request, $targetStatus, $requiredCurrent) {
                $orders = Order::whereIn('id', $request->ids)
                    ->lockForUpdate()
                    ->get(['id', 'status']);

                $invalidCount = $orders->where('status', '!=', $requiredCurrent)->count();

                if ($invalidCount > 0) {
                    throw new \RuntimeException("有 {$invalidCount} 筆訂單狀態不符，無法批次更新");
                }

                Order::whereIn('id', $request->ids)->update(['status' => $targetStatus]);
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => '批次更新成功']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer.name'    => 'required|string',
            'customer.phone'   => 'required|string',
            'customer.address' => 'required|string',
            'paymentMethod'    => 'required|string|in:credit_card,atm,cvs,cod',
            'bill'             => 'nullable|string',
            'taxId'            => 'nullable|string',
            'carrier'          => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.id'       => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            return DB::transaction(function () use ($validated) {

                $itemIds = collect($validated['items'])->pluck('id');

                $products = Product::whereIn('id', $itemIds)
                    ->where('is_active', 1)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($validated['items'] as $item) {
                    $product = $products->get($item['id']);
                    if (!$product) {
                        throw new \Exception("商品 ID {$item['id']} 不存在或已下架");
                    }
                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("商品「{$product->name}」庫存不足（剩餘 {$product->stock}）");
                    }
                }

                $totalAmount = collect($validated['items'])->sum(function ($item) use ($products) {
                    return $products->get($item['id'])->price * $item['quantity'];
                });

                $order = Order::create([
                    'user_id'        => auth()->id(),
                    'order_number'   => 'ORD' . date('YmdHis') . rand(100, 999),
                    'name'           => $validated['customer']['name'],
                    'phone'          => $validated['customer']['phone'],
                    'address'        => $validated['customer']['address'],
                    'total_amount'   => $totalAmount,
                    'payment_method' => $validated['paymentMethod'],
                    'invoice_type'   => $validated['bill'] ?? Order::DEFAULT_INVOICE_TYPE,
                    'tax_id'         => $validated['taxId'] ?? null,
                    'carrier'        => $validated['carrier'] ?? null,
                ]);

                foreach ($validated['items'] as $item) {
                    $product = $products->get($item['id']);
                    $order->items()->create([
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'price'        => (float)$product->price,
                        'quantity'     => $item['quantity'],
                    ]);
                    $product->decrement('stock', $item['quantity']);
                }

                return response()->json([
                    'message'  => '訂單已成功送出！',
                    'order_id' => $order->id,
                ], 201);
            });
        } catch (\Exception $e) {
            $userFacingMessages = ['不存在或已下架', '庫存不足'];
            $isUserFacing = collect($userFacingMessages)->contains(fn($k) => str_contains($e->getMessage(), $k));

            return response()->json([
                'message' => $isUserFacing ? $e->getMessage() : '訂單建立失敗，請稍後再試',
            ], 422);
        }
    }
}
