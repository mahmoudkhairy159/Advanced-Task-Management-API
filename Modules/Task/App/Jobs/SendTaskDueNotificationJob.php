<?php


namespace Modules\Task\App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Task\App\Models\Task;
use Modules\Task\App\Notifications\TaskDueNotification;
use Throwable;

class SendTaskDueNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly int $taskId,
        public readonly int $assignableId,
        public readonly string $assignableType
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Retrieve the task
            $task = Task::with(['assignable'])->find($this->taskId);

            if (!$task) {
                Log::warning('Task not found for notification', [
                    'task_id' => $this->taskId
                ]);
                return;
            }

            // Verify the assignable entity exists
            if (!$task->assignable) {
                Log::warning('Assignable entity not found for task notification', [
                    'task_id' => $this->taskId,
                    'assignable_id' => $this->assignableId,
                    'assignable_type' => $this->assignableType
                ]);
                return;
            }

            // Verify the assignable entity matches what we expect
            if (
                $task->assignable_id !== $this->assignableId ||
                $task->assignable_type !== $this->assignableType
            ) {
                Log::warning('Task assignable entity mismatch', [
                    'task_id' => $this->taskId,
                    'expected_assignable_id' => $this->assignableId,
                    'expected_assignable_type' => $this->assignableType,
                    'actual_assignable_id' => $task->assignable_id,
                    'actual_assignable_type' => $task->assignable_type
                ]);
                return;
            }

            // Check if task is still in a state that requires notification
            if (!in_array($task->status, [0, 1], true)) { // PENDING or IN_PROGRESS
                Log::info('Skipping notification for task with completed/overdue status', [
                    'task_id' => $this->taskId,
                    'status' => $task->status
                ]);
                return;
            }

            // Verify the assignable entity has an email
            if (!property_exists($task->assignable, 'email') || empty($task->assignable->email)) {
                Log::warning('Assignable entity has no email address', [
                    'task_id' => $this->taskId,
                    'assignable_id' => $this->assignableId,
                    'assignable_type' => $this->assignableType
                ]);
                return;
            }

            // Send the notification
            $task->assignable->notify(new TaskDueNotification($task));

            Log::info('Task due notification sent successfully', [
                'task_id' => $this->taskId,
                'assignable_id' => $this->assignableId,
                'assignable_type' => $this->assignableType,
                'email' => $task->assignable->email
            ]);

        } catch (Throwable $e) {
            Log::error('Failed to send task due notification', [
                'task_id' => $this->taskId,
                'assignable_id' => $this->assignableId,
                'assignable_type' => $this->assignableType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw the exception so Laravel can handle retries
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Task due notification job failed permanently', [
            'task_id' => $this->taskId,
            'assignable_id' => $this->assignableId,
            'assignable_type' => $this->assignableType,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);
    }
}