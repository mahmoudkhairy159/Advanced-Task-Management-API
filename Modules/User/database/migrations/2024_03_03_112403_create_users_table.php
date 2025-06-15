<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable()->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('image')->nullable();
            $table->boolean('status')->default(1)->index();
            $table->boolean('active')->default(1)->index();
            $table->boolean('blocked')->default(0)->index();
            
            $table->timestamp('password_updated_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('banned_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->rememberToken();
            $table->string('fcm_token')->nullable();
            $table->timestamps();

            // Full-Text Search
            $table->fullText(['name', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};