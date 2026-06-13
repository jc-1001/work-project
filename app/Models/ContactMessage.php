<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'category',
        'description',
        'status',
        'reply_content',
        'replied_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    public static array $categories = [
        'product' => '商品問題',
        'order'   => '訂單問題',
        'other'   => '其他',
    ];

    public function getCategoryLabelAttribute(): string
    {
        return self::$categories[$this->category] ?? $this->category;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
