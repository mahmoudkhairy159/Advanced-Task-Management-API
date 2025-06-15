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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->unique();

            // User Information
            $table->text('bio')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Non-binary', 'Prefer not to say'])->nullable();

            // Preferences
            $table->enum('mode', ['dark', 'light', 'device_mode'])->default('light');
            $table->enum('sound_effects', ['on', 'off'])->default('on');
            $table->string('language', 10)->default('en');
            $table->enum('allow_related_notifications', ['on', 'off'])->default('on');
            $table->enum('send_email_notifications', ['on', 'off'])->default('on');

         

            // Indexing
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('user_profiles');
    }
};