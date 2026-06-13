<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    protected $fillable = [
        'title',
        'image_path',
        'link_url',
        'countdown_seconds',
        'display_start_at',
        'display_end_at',
        'is_active',
    ];

    protected $casts = [
        'display_start_at' => 'datetime',
        'display_end_at'   => 'datetime',
        'is_active'        => 'boolean',
    ];

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }
}
