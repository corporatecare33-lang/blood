<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('bn_name');
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained()->cascadeOnDelete();
            $table->string('name')->unique();
            $table->string('bn_name');
        });

        Schema::create('thanas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('bn_name')->nullable();
            $table->timestamps();
            $table->unique(['district_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thanas');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('divisions');
    }
};
