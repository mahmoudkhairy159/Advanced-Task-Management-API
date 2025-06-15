<?php

namespace Modules\UserNotification\App\Traits;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Modules\User\App\Models\User;
use Modules\UserNotification\App\Enums\NotificationTypeEnum;
use Modules\UserNotification\App\Notifications\CustomNotification;

trait NotificationTrait
{
    /**
     * Default notification data structure.
     *
     * @var array
     */
    private array $notificationData = [
        "title" => null,
        "body" => null,
        "action_by_id" => null,
        "action_by_name" => null,
        "notification_type" => null,
        "action_id" => null,
        "action_slug" => null,
        "root_id" => null,
        "root_slug" => null,
        "parent_id" => null,
        "parent_slug" => null,
        "image_url" => null,
        'notifiable_id' => null,
        'created_at' => null,
    ];

    /**
     * Prepare and send notification to a user if they are not the authenticated user.
     *
     * @param Model $model The model related to the notification
     * @param User $notifiable The user to notify
     * @param string $notificationType The type of notification
     * @param int|null $rootId The root ID
     * @param string|null $rootSlug The root slug
     * @param int|null $parentId The parent ID
     * @param string|null $parentSlug The parent slug
     * @param string|null $imageUrl URL to an image related to the notification
     * @return void
     */
    protected function prepareAndSendNotification(
        Model $model,
        User $notifiable,
        string $notificationType,
        ?int $rootId = null,
        ?string $rootSlug = null,
        ?int $parentId = null,
        ?string $parentSlug = null,
        ?string $imageUrl = null
    ): void {
        $authUserId = auth()->guard('user-api')->id();

        // Only send notification if the recipient is not the authenticated user
        if ($notifiable->id !== $authUserId) {
            $this->setNotificationData([
                'notification_type' => $notificationType,
                'action_id' => $model->id,
                'action_slug' => $model->slug ?? null,
                'parent_id' => $parentId,
                'parent_slug' => $parentSlug,
                'root_id' => $rootId,
                'root_slug' => $rootSlug,
                'image_url' => $imageUrl,
                'notifiable_id' => $notifiable->id,
                'created_at' => now(),
            ]);

            $this->sendNotification($notifiable);
        }
    }

    /**
     * Set notification data based on provided array.
     *
     * @param array $data The data to set
     * @return void
     */
    public function setNotificationData(array $data): void
    {
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->notificationData)) {
                $this->notificationData[$key] = $value;
            }
        }
    }

    /**
     * Send a notification to a user.
     *
     * @param User $user The user to notify
     * @return bool Whether the notification was sent successfully
     */
    public function sendNotification(User $user): bool
    {
        try {
            $actionBy = auth()->guard('user-api')->user();

            if (!$actionBy) {
                throw new Exception('Authenticated user not found.');
            }

            $this->setNotificationData([
                'action_by_id' => $actionBy->id,
                'action_by_name' => $actionBy->name,
            ]);

            // Generate notification data based on the type
            $this->generateNotificationData();

            $user->notify(new CustomNotification($this->notificationData));

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Generate notification data based on notification type.
     *
     * @return void
     */
    private function generateNotificationData(): void
    {
        $notificationType = $this->notificationData['notification_type'];
        $methodName = 'generate' . $this->formatNotificationType($notificationType) . 'NotificationData';

        // Check if the specific method exists
        if (method_exists($this, $methodName)) {
            $this->$methodName();
        } else {
            $this->generateDefaultNotificationData();
        }
    }

    /**
     * Format notification type string for method name.
     *
     * @param string $type The notification type
     * @return string The formatted type
     */
    private function formatNotificationType(string $type): string
    {
        // Convert VOTE_CASTED to VoteCasted
        return str_replace(' ', '', ucwords(strtolower(str_replace('_', ' ', $type))));
    }

    /**
     * Generate notification data for vote casted.
     *
     * @return void
     */
    private function generateVoteCastedNotificationData(): void
    {
        $this->setNotificationData([
            'title' => __('usernotification::app.vote_casted.title'),
            'body' => __('usernotification::app.vote_casted.body', ['name' => $this->notificationData['action_by_name']])
        ]);
    }

    /**
     * Generate notification data for survey casted.
     *
     * @return void
     */
    private function generateSurveyCastedNotificationData(): void
    {
        $this->setNotificationData([
            'title' => __('usernotification::app.survey_casted.title'),
            'body' => __('usernotification::app.survey_casted.body', ['name' => $this->notificationData['action_by_name']])
        ]);
    }

    /**
     * Generate notification data for project liked.
     *
     * @return void
     */
    private function generateProjectLikedNotificationData(): void
    {
        $this->setNotificationData([
            'title' => __('usernotification::app.project_liked.title'),
            'body' => __('usernotification::app.project_liked.body', ['name' => $this->notificationData['action_by_name']])
        ]);
    }

    /**
     * Generate notification data for project commented.
     *
     * @return void
     */
    private function generateProjectCommentedNotificationData(): void
    {
        $this->setNotificationData([
            'title' => __('usernotification::app.project_commented.title'),
            'body' => __('usernotification::app.project_commented.body', ['name' => $this->notificationData['action_by_name']])
        ]);
    }

    /**
     * Generate notification data for project comment replied.
     *
     * @return void
     */
    private function generateProjectCommentRepliedNotificationData(): void
    {
        $this->setNotificationData([
            'title' => __('usernotification::app.project_comment_replied.title'),
            'body' => __('usernotification::app.project_comment_replied.body', ['name' => $this->notificationData['action_by_name']])
        ]);
    }

    /**
     * Generate default notification data.
     *
     * @return void
     */
    private function generateDefaultNotificationData(): void
    {
        $this->setNotificationData([
            'title' => __('usernotification::app.default.title'),
            'body' => __('usernotification::app.default.body')
        ]);
    }
}
