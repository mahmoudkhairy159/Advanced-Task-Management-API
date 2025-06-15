<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Task\App\Enums\TaskPriorityEnum;
use Modules\Task\App\Enums\TaskStatusEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->fullText(['title', 'description']); // Add full-text index for search
            $table->date('due_date');
            $table->tinyInteger('status')->default(TaskStatusEnum::STATUS_PENDING);
            $table->tinyInteger('priority')->default(TaskPriorityEnum::PRIORITY_LOW);
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better query performance
            $table->index('status');
            $table->index('due_date');

            // Add polymorphic relationship columns
            $table->morphs('assignable'); // This will create assignable_type and assignable_id
            $table->morphs('creator'); // This will create creator_type and creator_id
            $table->morphs('updater'); // This will create updater_type and updater_id

            // Add indexes for better performance
            $table->index(['assignable_type', 'assignable_id']);
            $table->index(['creator_type', 'creator_id']);
            $table->index(['updater_type', 'updater_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('tasks');
    }
};