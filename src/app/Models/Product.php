<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// 為什麼一定要創這個檔？
//1. Eloquent ORM：有了這個 Model，妳在 Controller 裡只要寫 Product::all() 就能拿到所有商品，不用再寫複雜的 SELECT * FROM products。

//2. Seeder 與 Factory：妳剛才想寫入 30 筆假資料，Product::factory(30)->create() 這行指令背後高度依賴這個 Model。

//3. API 回傳：當妳之後要在 Vue 商城顯示商品時，妳會需要這個 Model 來把資料庫的數據轉成 JSON 格式回傳給前端。


class Product extends Model
{
    use HasFactory;

    // 指定資料表名稱（如果妳的表名是 products 且符合慣例，其實可省略，但寫著比較保險）
    protected $table = 'products';

    // 允許被批量寫入的欄位（這對妳剛才要寫入 30 筆假資料非常重要！）
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'stock',
        'image',
        'is_active',
    ];

}
