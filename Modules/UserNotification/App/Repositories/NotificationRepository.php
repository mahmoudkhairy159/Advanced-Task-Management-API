<?php

namespace Modules\UserNotification\App\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\User\App\Models\User;
use Modules\UserNotification\App\Models\UserDatabaseNotification;
use Prettus\Repository\Eloquent\BaseRepository;

class NotificationRepository extends BaseRepository
{

    public function model()
    {
        return UserDatabaseNotification::class;
    }
    public function getAll()
    {

        return $this->model
            ->where('notifiable_type', User::class)
            ->with(['notifiable' => function ($query) {
                $query->select('id', 'name', 'image');
            },])->orderBy('created_at', 'DESC');
    }

    public function getOneById($id)
    {
        return $this->model
            ->where('notifiable_type', User::class)
            ->where('id', $id)
            ->with(['notifiable' => function ($query) {
                $query->select('id', 'name', 'image');
            },])
            ->first();
    }







    public function getUserNotificationsByUserId(int $userId)
    {


        return $this->model
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)
            ->orderBy('created_at', 'DESC');
    }
    public function getUnreadUserNotificationsCountByUserId(int $userId)
    {


        return $this->model
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $userId)
            ->unread()
            ->count();
    }











    public function deleteOne(string $id)
    {
        try {
            DB::beginTransaction();
            $userId = auth()->id();
            $notification = $this->model
                ->where('notifiable_type', User::class)
                ->where('notifiable_id', $userId)->findOrFail($id);
            $deleted = $notification->delete();
            DB::commit();
            return  $deleted;
        } catch (\Throwable $th) {
            DB::rollBack();
            return false;
        }
    }

    public function markAsRead(string $id)
    {
        try {
            DB::beginTransaction();
            $userId = auth()->id();
            $notification = $this->model
                ->where('notifiable_type', User::class)
                ->where('notifiable_id', $userId)->findOrFail($id);
            $notification->markAsRead();
            DB::commit();
            return  true;
        } catch (\Throwable $th) {
            DB::rollBack();
            return false;
        }
    }
    public function markAllAsRead()
    {
        try {
            DB::beginTransaction();
            $userId = auth()->id();
            $notifications = $this->model
                ->where('notifiable_type', User::class)
                ->where('notifiable_id', $userId)
                ->unread()
                ->get();
                foreach ($notifications as $notification){
                    $notification->markAsRead();
                }
            DB::commit();
            return  true;
        } catch (\Throwable $th) {
            DB::rollBack();
            return false;
        }
    }
    public function markAsUnread(string $id)
    {
        try {
            DB::beginTransaction();
            $userId = auth()->id();
            $notification = $this->model
                ->where('notifiable_type', User::class)
                ->where('notifiable_id', $userId)->findOrFail($id);
            $notification->markAsUnread();
            DB::commit();
            return  true;
        } catch (\Throwable $th) {
            dd($th->getMessage());
            DB::rollBack();
            return false;
        }
    }
}
