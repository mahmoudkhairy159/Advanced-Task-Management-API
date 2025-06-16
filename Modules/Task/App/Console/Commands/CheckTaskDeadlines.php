<?php

namespace Modules\Task\App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Task\App\Models\Task;
use Modules\Task\App\Enums\TaskStatusEnum;
use Modules\Task\App\Notifications\TaskDueNotification;

class CheckTaskDeadlines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:check-deadlines
                          {--hours=24 : Number of hours before due date to send notification}
                          {--dry-run : Run without sending actual notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for tasks approaching their due dates and send email notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting task deadline check...');

        $hours = (int) $this->option('hours');
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No notifications will be sent');
        }

        try {
            $tasks = $this->getTasksDueSoon($hours);

            if ($tasks->isEmpty()) {
                $this->info("No tasks found due within {$hours} hours.");
                return self::SUCCESS;
            }

            $this->info("Found {$tasks->count()} task(s) due within {$hours} hours.");

            $notificationsSent = 0;
            $errors = 0;

            foreach ($tasks as $task) {
                try {
                    $this->processTaskNotification($task, $isDryRun);
                    $notificationsSent++;

                    $this->line("✓ Processed task: {$task->title} (Due: {$task->due_date->format('M d, Y H:i')})");
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("✗ Failed to process task {$task->title}: " . $e->getMessage());
                    Log::error('Task notification failed', [
                        'task_id' => $task->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            $this->newLine();
            $this->info("Task deadline check completed!");
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Tasks Found', $tasks->count()],
                    ['Notifications Processed', $notificationsSent],
                    ['Errors', $errors],
                ]
            );

            Log::info('Task deadline check completed', [
                'tasks_found' => $tasks->count(),
                'notifications_sent' => $notificationsSent,
                'errors' => $errors,
                'hours' => $hours,
                'dry_run' => $isDryRun
            ]);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Fatal error during task deadline check: ' . $e->getMessage());
            Log::error('Fatal error in task deadline check', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return self::FAILURE;
        }
    }

    /**
     * Get tasks that are due soon
     *
     * @param int $hours
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getTasksDueSoon(int $hours): \Illuminate\Database\Eloquent\Collection
    {
        $now = Carbon::now();
        $targetTime = $now->copy()->addHours($hours);

        return Task::with(['assignable'])
            ->whereIn('status', [TaskStatusEnum::STATUS_PENDING, TaskStatusEnum::STATUS_IN_PROGRESS])
            ->where('due_date', '<=', $targetTime)
            ->where('due_date', '>', $now)
            ->whereHas('assignable') // Ensure assignable entity exists
            ->get();
    }

    /**
     * Process notification for a single task
     *
     * @param Task $task
     * @param bool $isDryRun
     * @return void
     * @throws \Exception
     */
    private function processTaskNotification(Task $task, bool $isDryRun = false): void
    {
        $assignable = $task->assignable;

        if (!$assignable) {
            throw new \Exception("Task {$task->id} has no assignable entity");
        }

        if (!property_exists($assignable, 'email') || empty($assignable->email)) {
            throw new \Exception("Assignable entity for task {$task->id} has no email address");
        }

        if (!$isDryRun) {
            $assignable->notify(new TaskDueNotification($task));
        }
    }
}