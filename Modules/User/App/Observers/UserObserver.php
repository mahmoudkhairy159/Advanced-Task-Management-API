<?php

namespace Modules\User\App\Observers;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Modules\User\App\Models\User;

class UserObserver  implements ShouldHandleEventsAfterCommit
{


    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        if (! $user->profile) {
            // Custom logic when a user is created, if needed
            $user->profile()->create([
                'bio' => !request()->bio ? '' : request()->bio, // Default value or as needed
                'mode' => !request()->mode ? 'light' : request()->mode, // Default value
                'sound_effects' => !request()->sound_effects ? 'on' : request()->sound_effects, // Default value
                'language' => !request()->language ? 'en' : request()->language, // Default value
                'birth_date' => !request()->birth_date ? null : request()->birth_date, // Default or null if not provided
                'gender' => !request()->gender ? null : request()->gender, // Default or null if not provided
                'allow_related_notifications'=> !request()->allow_related_notifications? 'on' : request()->allow_related_notifications,
                'send_email_notifications'=> !request()->send_email_notifications? 'on' : request()->send_email_notifications,
            ]);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Custom logic when a user is updated, if needed
    }

    /**
     * Handle the User "deleted" event (soft delete).
     */
    public function deleted(User $user): void
    {
        // if (!$user->isForceDeleting()) {
        //     // Soft delete related models
            $this->deleteRelatedModels($user, false);
        // }
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        // Custom logic when a user is restored, if needed
        $this->restoreRelatedModels($user);
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        // Force delete related models
        $this->deleteRelatedModels($user, true);
    }

    /**
     * Delete or soft delete related models.
     *
     * @param  \Modules\User\App\Models\User  $user
     * @param  bool  $forceDelete
     * @return void
     */
    protected function deleteRelatedModels(User $user, bool $forceDelete): void
    {
        $relations = $this->getUserRelations();
        foreach ($relations as $relation) {
            $related = $user->$relation();

            if ($forceDelete) {
                $related->forceDelete();
            } else {
                $related->delete();
            }
        }
    }
    /**
     * Restore related models when user is restored.
     *
     * @param  \Modules\User\App\Models\User  $user
     * @return void
     */
    protected function restoreRelatedModels(User $user): void
    {
        $relations = $this->getUserRelations();

        foreach ($relations as $relation) {
            $related = $user->$relation()->onlyTrashed();

            if ($related) {
                $related->restore();
            }
        }
    }
    /**
     * Get all user-related model relations.
     *
     * @return array
     */
    protected function getUserRelations(): array
    {
        return [
            'fcmTokens',
            'profile',
            'phone',
            'otps'
        ];
    }
}
