<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // 這裡就是接收前端傳來的 user_id, total_price 等資料
        $validated = $request->validate([
            'user_id' => 'required',
            'total_price' => 'required|numeric',
            'shipping_address' => 'required|string',
            'phone' => 'required|string',
        ]);

        $order = Order::create([
            'user_id'          => $request->user_id,
            'total_price'      => $request->total_price,
            'status'           => 'pending',
            'shipping_address' => $request->shipping_address,
            'phone'            => $request->phone,
        ]);

        return response()->json([
            'message' => '訂單成功送出！',
            'order' => $order
        ], 201);
    }
}