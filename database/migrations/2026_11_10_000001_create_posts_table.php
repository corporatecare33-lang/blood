<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 160);
            $table->string('slug', 120)->unique();
            $table->string('excerpt', 300)->nullable();
            $table->longText('body');
            $table->string('cover_path')->nullable();
            $table->string('cover_thumb_path')->nullable();
            $table->unsignedSmallInteger('cover_width')->nullable();
            $table->unsignedSmallInteger('cover_height')->nullable();
            $table->string('cover_alt', 160)->nullable();
            $table->boolean('is_published')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
