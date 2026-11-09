<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile', 11)->unique();
            $table->string('email')->nullable()->unique();
            $table->string('gender', 10);
            $table->string('blood_group', 3)->index();
            $table->string('occupation', 20);
            $table->date('date_of_birth');
            $table->date('last_donated_at')->nullable();
            $table->foreignId('division_id')->constrained();
            $table->foreignId('district_id')->constrained();
            $table->foreignId('thana_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address')->nullable();
            $table->string('password');
            $table->string('role', 20)->default('donor')->index();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->index(['blood_group', 'district_id']);
        });

        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('mobile', 11)->index();
            $table->string('purpose', 30);
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('otp_codes');
        Schema::dropIfExists('sessions');
    }
};
