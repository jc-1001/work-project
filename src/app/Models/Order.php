<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // 告訴 Laravel 這些欄位是可以被「批量寫入」的
    // 這裡的名稱要跟妳資料庫表的欄位一模一樣
    protected $fillable = [
        'user_id',
        'order_number',
        'name',
        'phone',
        'address',
        'total_amount',
        'payment_method',
        'invoice_type',
        'tax_id',
        'carrier'
    ];

    /**
     * 關聯到使用者 (多對一)
     * 訂單有記錄是誰買的
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 關聯到訂單明細 (一對多)
     * 能執行 $order->items()->create(...) 的關鍵
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
