<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = ['review_id', 'user_id', 'reasons'];

    protected $casts = [
        'reasons' => 'array',
    ];

    protected function asJson($value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}
