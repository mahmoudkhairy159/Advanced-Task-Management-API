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

        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('image')->nullable();
            $table->boolean('status')->default(1);
            $table->boolean('blocked')->default(0);
            $table->unsignedBigInteger('role_id')->nullable();
            $table->timestamp('password_updated_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('role_id')->references('id')->on(table: 'roles')->onDelete('set null');

            // Add foreign keys for referential integrity
            $table->rememberToken();
            $table->timestamps();
            $table->fullText(['name', 'phone', 'email']);
            $table->index('role_id');
            $table->index('created_by');
            $table->index('updated_by');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropFullText(['name', 'phone', 'email']); // removing full-text index
        });
        Schema::dropIfExists('admins');
    }
};
