<?php


namespace Modules\Task\App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Task\App\Models\Task;

class TaskDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Task $task
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Task Due Reminder - ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('This is a reminder that you have a task due soon.')
            ->line('**Task Title:** ' . $this->task->title)
            ->line('**Description:** ' . ($this->task->description ?? 'No description provided'))
            ->line('**Due Date:** ' . $this->task->due_date->format('M d, Y'))
            ->line('**Priority:** ' . $this->getPriorityLabel())
            ->line('Please make sure to complete this task on time.')
            ->action('View Task Details', $this->getTaskUrl())
            ->line('Thank you for using our task management system!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'due_date' => $this->task->due_date->toDateString(),
            'priority' => $this->task->priority,
            'message' => 'Task "' . $this->task->title . '" is due on ' . $this->task->due_date->format('M d, Y'),
            'type' => 'task_due_reminder'
        ];
    }

    /**
     * Get priority label for display
     *
     * @return string
     */
    private function getPriorityLabel(): string
    {
        return match ($this->task->priority) {
            0 => 'Low',
            1 => 'Medium',
            2 => 'High',
            default => 'Unknown'
        };
    }

    /**
     * Get the URL to view task details
     *
     * @return string
     */
    private function getTaskUrl(): string
    {
        // You can customize this URL based on your frontend application
        return config('app.frontend_url', config('app.url')) . '/tasks/' . $this->task->id;
    }
}
