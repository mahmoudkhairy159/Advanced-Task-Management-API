<?php
namespace Modules\Task\App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Task\App\Models\Task;
use Modules\Task\App\Enums\TaskStatusEnum;
use Modules\Task\App\Notifications\TaskDueNotification;

class SendTaskNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:send-notifications
                          {--type=due : Type of notification (due, overdue, weekly-summary)}
                          {--hours=24 : Hours before due date for due notifications}
                          {--user-id=* : Specific user IDs to process}
                          {--admin-id=* : Specific admin IDs to process}
                          {--dry-run : Run without sending actual notifications}
                          {--force : Force send even if recently sent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send various types of task notifications to users and admins';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No notifications will be sent');
        }

        $this->info("Starting {$type} notification process...");

        try {
            return match ($type) {
                'due' => $this->sendDueNotifications(),
                'overdue' => $this->sendOverdueNotifications(),
                'weekly-summary' => $this->sendWeeklySummary(),
                default => $this->invalidType($type)
            };
        } catch (\Exception $e) {
            $this->error('Error during notification process: ' . $e->getMessage());
            Log::error('Task notification process failed', [
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return self::FAILURE;
        }
    }

    /**
     * Send due date notifications
     *
     * @return int
     */
    private function sendDueNotifications(): int
    {
        $hours = (int) $this->option('hours');
        $isDryRun = $this->option('dry-run');

        $tasks = $this->getTasksDueSoon($hours);

        if ($tasks->isEmpty()) {
            $this->info("No tasks found due within {$hours} hours.");
            return self::SUCCESS;
        }

        $this->info("Processing {$tasks->count()} task(s) due within {$hours} hours...");

        $notificationsSent = 0;
        $progressBar = $this->output->createProgressBar($tasks->count());
        $progressBar->start();

        foreach ($tasks as $task) {
            try {
                if (!$isDryRun) {
                    $task->assignable->notify(new TaskDueNotification($task));
                }
                $notificationsSent++;
            } catch (\Exception $e) {
                Log::error('Failed to send due notification', [
                    'task_id' => $task->id,
                    'error' => $e->getMessage()
                ]);
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("Sent {$notificationsSent} due date notifications.");

        return self::SUCCESS;
    }

    /**
     * Send overdue notifications
     *
     * @return int
     */
    private function sendOverdueNotifications(): int
    {
        $isDryRun = $this->option('dry-run');

        $tasks = Task::with(['assignable'])
            ->whereIn('status', [TaskStatusEnum::STATUS_PENDING, TaskStatusEnum::STATUS_IN_PROGRESS])
            ->where('due_date', '<', Carbon::now())
            ->whereHas('assignable')
            ->get();

        if ($tasks->isEmpty()) {
            $this->info("No overdue tasks found.");
            return self::SUCCESS;
        }

        $this->info("Processing {$tasks->count()} overdue task(s)...");

        // Update status to overdue and send notifications
        $updated = 0;
        $notificationsSent = 0;

        foreach ($tasks as $task) {
            try {
                if (!$isDryRun) {
                    // Update status to overdue
                    $task->update(['status' => TaskStatusEnum::STATUS_OVERDUE]);
                    $updated++;

                    // Send notification (you can create a separate OverdueNotification class)
                    $task->assignable->notify(new TaskDueNotification($task));
                    $notificationsSent++;
                }
            } catch (\Exception $e) {
                Log::error('Failed to process overdue task', [
                    'task_id' => $task->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Updated {$updated} tasks to overdue status and sent {$notificationsSent} notifications.");

        return self::SUCCESS;
    }

    /**
     * Send weekly summary (placeholder for future implementation)
     *
     * @return int
     */
    private function sendWeeklySummary(): int
    {
        $this->info("Weekly summary notifications not yet implemented.");
        return self::SUCCESS;
    }

    /**
     * Handle invalid notification type
     *
     * @param string $type
     * @return int
     */
    private function invalidType(string $type): int
    {
        $this->error("Invalid notification type: {$type}");
        $this->line("Available types: due, overdue, weekly-summary");
        return self::FAILURE;
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

        $query = Task::with(['assignable'])
            ->whereIn('status', [TaskStatusEnum::STATUS_PENDING, TaskStatusEnum::STATUS_IN_PROGRESS])
            ->where('due_date', '<=', $targetTime)
            ->where('due_date', '>', $now)
            ->whereHas('assignable');

        // Filter by specific users/admins if provided
        $userIds = $this->option('user-id');
        $adminIds = $this->option('admin-id');

        if (!empty($userIds) || !empty($adminIds)) {
            $query->where(function ($q) use ($userIds, $adminIds) {
                if (!empty($userIds)) {
                    $q->orWhere(function ($subQ) use ($userIds) {
                        $subQ->where('assignable_type', 'Modules\User\App\Models\User')
                            ->whereIn('assignable_id', $userIds);
                    });
                }
                if (!empty($adminIds)) {
                    $q->orWhere(function ($subQ) use ($adminIds) {
                        $subQ->where('assignable_type', 'Modules\Admin\App\Models\Admin')
                            ->whereIn('assignable_id', $adminIds);
                    });
                }
            });
        }

        return $query->get();
    }
}