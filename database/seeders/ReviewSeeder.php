<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::whereHas('roles', fn ($q) => $q->where('name', 'user'))->pluck('id');

        if ($userIds->isEmpty()) {
            return;
        }

        $contents = [
            '商品品質非常好，包裝也很用心，下次還會再購買！',
            '整體來說很滿意，顏色跟圖片一致，只是運送時間稍微久了一點。',
            '還可以，符合期待，但沒有特別驚艷。',
            '品質普通，價格稍高，但整體可接受。',
            '非常推薦！物超所值，朋友也說讚。',
            '第一次購買，感覺不錯，會繼續支持。',
            '包裝很精緻，送禮自用兩相宜。',
            '商品與描述相符，快速出貨，很滿意。',
        ];

        foreach ($userIds as $userId) {
            Review::firstOrCreate(
                ['product_id' => 1, 'user_id' => $userId],
                [
                    'rating'  => rand(3, 5),
                    'content' => $contents[array_rand($contents)],
                ]
            );
        }
    }
}
