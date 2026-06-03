<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function adminIndex()
    {
        return response()->json([
            'coupons' => Coupon::orderBy('created_at', 'desc')->get(),
        ]);
    }

    public function adminShow($id)
    {
        return response()->json([
            'coupon' => Coupon::findOrFail($id),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'                => 'required|string|unique:coupons,code',
            'discount_type'       => 'required|in:fixed,percent',
            'discount_value'      => ['required', 'numeric', 'min:0', Rule::when($request->input('discount_type') === 'percent', 'max:100')],
            'min_order_amount'    => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'max_uses'            => 'nullable|integer|min:1',
            'expires_at'          => 'nullable|date|after:now',
            'is_active'           => 'boolean',
        ]);

        $coupon = Coupon::create($validated);

        return response()->json(['coupon' => $coupon], 201);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $validated = $request->validate([
            'code'                => 'required|string|unique:coupons,code,' . $id,
            'discount_type'       => 'required|in:fixed,percent',
            'discount_value'      => ['required', 'numeric', 'min:0', Rule::when($request->input('discount_type') === 'percent', 'max:100')],
            'min_order_amount'    => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'max_uses'            => 'nullable|integer|min:1',
            'expires_at'          => 'nullable|date',
            'is_active'           => 'boolean',
        ]);

        $coupon->update($validated);

        return response()->json(['coupon' => $coupon]);
    }

    public function toggleActive($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);

        return response()->json(['is_active' => $coupon->is_active]);
    }

    public function batchUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'ids'    => 'required|array',
            'ids.*'  => 'integer|exists:coupons,id',
            'status' => 'required|boolean',
        ]);

        Coupon::whereIn('id', $validated['ids'])
              ->update(['is_active' => $validated['status']]);

        return response()->json(['message' => '批量更新成功']);
    }

    public function validateCoupon(Request $request)
    {
        $request->validate([
            'code'     => 'required|string',
            'subtotal' => 'nullable|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', strtoupper(trim($request->code)))
                        ->where('is_active', true)
                        ->first();

        if (!$coupon) {
            return response()->json(['message' => '折扣碼無效或已停用'], 422);
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return response()->json(['message' => '折扣碼已過期'], 422);
        }

        if ($coupon->max_uses !== null && $coupon->used_count >= $coupon->max_uses) {
            return response()->json(['message' => '折扣碼已達使用上限'], 422);
        }

        $alreadyUsed = CouponUsage::where('coupon_id', $coupon->id)
                                   ->where('user_id', auth()->id())
                                   ->exists();
        if ($alreadyUsed) {
            return response()->json(['message' => '您已使用過此折扣碼'], 422);
        }

        $subtotal = (float)($request->subtotal ?? 0);

        if ($coupon->min_order_amount !== null && $subtotal < (float)$coupon->min_order_amount) {
            $min = number_format((float)$coupon->min_order_amount, 0);
            return response()->json(['message' => "訂單金額需達 NT$ {$min} 才可使用此折扣碼"], 422);
        }

        if ($coupon->discount_type === 'fixed') {
            $discountAmount = min((float)$coupon->discount_value, $subtotal);
        } else {
            $discountAmount = $subtotal * ((float)$coupon->discount_value / 100);
            if ($coupon->max_discount_amount !== null) {
                $discountAmount = min($discountAmount, (float)$coupon->max_discount_amount);
            }
        }

        return response()->json([
            'coupon' => [
                'id'             => $coupon->id,
                'code'           => $coupon->code,
                'discount_type'  => $coupon->discount_type,
                'discount_value' => (float)$coupon->discount_value,
            ],
            'discount_amount' => (int)round($discountAmount),
        ]);
    }
}
