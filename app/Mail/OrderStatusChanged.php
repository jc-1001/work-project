<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    private static array $statusLabels = [
        'pending'   => '已成立',
        'shipping'  => '已出貨',
        'completed' => '已完成',
        'cancelled' => '已取消',
        'returned'  => '已退貨',
    ];

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        $label = self::$statusLabels[$this->order->status] ?? $this->order->status;

        return new Envelope(
            subject: "訂單 {$this->order->order_number} {$label}通知",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order_status_changed',
        );
    }
}
