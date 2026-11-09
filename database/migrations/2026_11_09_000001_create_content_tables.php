<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('patient_name');
            $table->string('blood_group', 3)->index();
            $table->unsignedTinyInteger('bags');
            $table->dateTime('needed_at')->index();
            $table->string('hospital');
            $table->foreignId('district_id')->constrained();
            $table->string('contact_name');
            $table->string('contact_mobile', 11);
            $table->text('note')->nullable();
            $table->string('status', 20)->default('open')->index();
            $table->timestamps();
        });

        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('caption', 500)->nullable();
            $table->string('image_path');
            $table->string('thumb_path');
            $table->unsignedSmallInteger('width');
            $table->unsignedSmallInteger('height');
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('about_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_sections');
        Schema::dropIfExists('gallery_items');
        Schema::dropIfExists('blood_requests');
    }
};
