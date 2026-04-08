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
        'total_price',
        'status',
        'shipping_address',
        'phone',
    ];

    // 如果妳的資料表名稱是 orders（複數），Laravel 會自動對應。
    // 如果資料表名稱比較特別，可以手動指定：
    // protected $table = 'orders';


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}