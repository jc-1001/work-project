<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('image_path', 500);
            $table->string('link_url', 500)->default('/shop');
            $table->unsignedSmallInteger('countdown_seconds')->default(10);
            $table->dateTime('display_start_at');
            $table->dateTime('display_end_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
