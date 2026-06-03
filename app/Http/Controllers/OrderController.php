<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

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
                    'invoice_type'   => $validated['bill'] ?? '個人電子發票',
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
