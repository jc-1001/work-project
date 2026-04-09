<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB; //事務處理

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // 1. 驗證資料（根據前端傳的 payload 結構）
        $validated = $request->validate([
            'customer.name'    => 'required|string',
            'customer.phone'   => 'required|string',
            'customer.address' => 'required|string',
            'total'            => 'required|numeric',
            'paymentMethod'    => 'required',
            'bill'             => 'nullable',
            'items'            => 'required|array', // 確認有商品清單
        ]);

        try {
            // 使用 DB Transaction，確保訂單跟明細「要嘛一起成功，要嘛一起失敗」
            return DB::transaction(function () use ($request) {
                
                // 2. 建立訂單主檔 (Orders)
                $order = Order::create([
                    'user_id'        => auth('sanctum')->id(),
                    'order_number'   => 'ORD' . date('YmdHis') . rand(100, 999),
                    'name'           => $request->customer['name'],
                    'phone'          => $request->customer['phone'],
                    'address'        => $request->customer['address'],
                    'total_amount'   => $request->total,
                    'payment_method' => $request->paymentMethod,
                    'invoice_type' => $request->bill ?? 'Option1',
                    'tax_id'         => $request->taxId ?? null, // 統編
                    'carrier'        => $request->carrier ?? null, // 載具
                ]);

                // 3. 建立訂單明細 (Order Items)
                foreach ($request->items as $item) {
                    $order->items()->create([
                        'product_name' => $item['name'],
                        'price'        => (int)$item['price'],
                        'quantity'     => $item['quantity'],
                    ]);
                }

                return response()->json([
                    'message' => '訂單已成功送出！',
                    'order_id' => $order->id
                ], 201);
            });

        } catch (\Exception $e) {
            return response()->json([
                'message' => '訂單送出失敗，請稍後再試',
                'error' => $e->getMessage()
            ], 500);
        }
       
    }
}