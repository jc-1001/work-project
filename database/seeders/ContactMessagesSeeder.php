<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use Illuminate\Database\Seeder;

class ContactMessagesSeeder extends Seeder
{
    public function run(): void
    {
        ContactMessage::create([
            'name'        => '王小明',
            'email'       => 'test@example.com',
            'category'    => 'order',
            'description' => '我的訂單已經超過一週還沒收到，請問目前的配送狀態如何？',
            'status'      => 'pending',
        ]);
    }
}
