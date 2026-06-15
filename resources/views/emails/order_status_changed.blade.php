@php
$labels = ['pending' => '已成立', 'shipping' => '已出貨', 'completed' => '已完成', 'cancelled' => '已取消', 'returned' => '已退貨'];
$label = $labels[$order->status] ?? $order->status;
$subtotal = (float) ($order->subtotal_amount ?? $order->total_amount);
$shipping = (float) ($order->shipping_fee ?? config('services.shipping.fee'));
$discount = (float) ($order->discount_amount ?? 0);
$finalTotal = $subtotal + $shipping - $discount;
@endphp

<x-mail::message>
# 訂單狀態更新通知

親愛的 **{{ $order->name }}**，您好：

您的訂單 **{{ $order->order_number }}** 目前 **{{ $label }}**。

請查看以下訂單詳情：

<x-mail::table>
| 商品名稱 | 單價 | 數量 | 小計 |
|:--------|-----:|-----:|-----:|
@foreach ($order->items as $item)
| {{ $item->product_name }} | NT$ {{ number_format($item->price) }} | {{ $item->quantity }} | NT$ {{ number_format($item->price * $item->quantity) }} |
@endforeach
</x-mail::table>

<x-mail::table>
| | |
|:---|---:|
| 商品小計 | NT$ {{ number_format($subtotal) }} |
@if ($discount > 0)
| 代碼折扣 | - NT$ {{ number_format($discount) }} |
@endif
| 運費 | NT$ {{ number_format($shipping) }} |

| | |
|:---|---:|
| **總金額** | **NT$ {{ number_format($finalTotal) }}** |
</x-mail::table>

如有任何問題，請聯繫客服。

<x-slot:subcopy>
此為系統自動發送，請勿直接回覆此信。
</x-slot:subcopy>
</x-mail::message>