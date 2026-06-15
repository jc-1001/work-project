<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $product = Product::first();

        $order = Order::create([
            'user_id'        => $user?->id,
            'order_number'   => 'ORD-' . now()->format('Ymd') . '-0001',
            'name'           => '測試使用者',
            'phone'          => '0912345678',
            'address'        => '台北市信義區松仁路100號',
            'total_amount'   => $product ? $product->price * 2 : 1000,
            'payment_method' => 'Credit card',
            'invoice_type'   => '個人電子發票',
            'tax_id'         => null,
            'carrier'        => null,
        ]);

        if ($product) {
            $order->items()->create([
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'price'        => $product->price,
                'quantity'     => 2,
            ]);
        }
    }
}
