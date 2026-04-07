<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 建立幾個基礎分類 
        // 自動遞增的 id 從 1 開始，所以這裡不需要指定 id，讓資料庫自動生成就好
        $categories = [
            ['name' => '食物與飲料'],
            ['name' => '數位商品'],
            ['name' => '服飾與配件'],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::updateOrCreate(
                ['name' => $category['name']], //查詢條件
                $category   //要更新或創建的資料
                );
        }
    }
}
