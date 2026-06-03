<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\CouponUsage;
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
            'paymentMethod'    => 'required|string|in:Credit card,ATM,cvs,cod',
            'bill'             => 'nullable|string',
            'taxId'            => 'nullable|string',
            'carrier'          => 'nullable|string',
            'coupon_code'      => 'nullable|string',
            'items'            => 'required|array|min:1',
            'items.*.id'       => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:10',
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

                $shippingFee    = (int) config('services.shipping.fee');
                $discountAmount = 0;
                $coupon         = null;

                if (!empty($validated['coupon_code'])) {
                    $coupon = Coupon::where('code', strtoupper(trim($validated['coupon_code'])))
                                    ->where('is_active', true)
                                    ->lockForUpdate()
                                    ->first();

                    if ($coupon) {
                        $isExpired   = $coupon->expires_at && $coupon->expires_at->isPast();
                        $isAtLimit   = $coupon->max_uses !== null && $coupon->used_count >= $coupon->max_uses;
                        $alreadyUsed = CouponUsage::where('coupon_id', $coupon->id)
                                                   ->where('user_id', auth()->id())
                                                   ->exists();
                        $belowMin    = $coupon->min_order_amount !== null
                                       && $totalAmount < (float)$coupon->min_order_amount;

                        if (!$isExpired && !$isAtLimit && !$alreadyUsed && !$belowMin) {
                            if ($coupon->discount_type === 'fixed') {
                                $discountAmount = min((float)$coupon->discount_value, $totalAmount);
                            } else {
                                $discountAmount = $totalAmount * ((float)$coupon->discount_value / 100);
                                if ($coupon->max_discount_amount !== null) {
                                    $discountAmount = min($discountAmount, (float)$coupon->max_discount_amount);
                                }
                            }
                            $discountAmount = (int)round($discountAmount);
                        } else {
                            $coupon = null;
                        }
                    }
                }

                $order = Order::create([
                    'user_id'         => auth()->id(),
                    'order_number'    => 'ORD' . date('YmdHis') . rand(100, 999),
                    'name'            => $validated['customer']['name'],
                    'phone'           => $validated['customer']['phone'],
                    'address'         => $validated['customer']['address'],
                    'total_amount'    => $totalAmount - $discountAmount + $shippingFee,
                    'subtotal_amount' => $totalAmount,
                    'shipping_fee'    => $shippingFee,
                    'discount_amount' => $discountAmount,
                    'coupon_id'       => $coupon?->id,
                    'payment_method'  => $validated['paymentMethod'],
                    'invoice_type'    => $validated['bill'] ?? '個人電子發票',
                    'tax_id'          => $validated['taxId'] ?? null,
                    'carrier'         => $validated['carrier'] ?? null,
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

                if ($coupon) {
                    $coupon->increment('used_count');
                    CouponUsage::create([
                        'coupon_id' => $coupon->id,
                        'user_id'   => auth()->id(),
                        'order_id'  => $order->id,
                    ]);
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
