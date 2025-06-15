<?php

namespace Modules\UserNotification\App\Http\Controllers\Api;

use Exception;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\UserNotification\App\Enums\NotificationTypeEnum;
use Modules\UserNotification\App\Repositories\NotificationRepository;
use Modules\UserNotification\App\Transformers\Api\UserNotification\UserNotificationCollection;

class UserNotificationController extends Controller
{
    use ApiResponseTrait;


    protected $userNotificationRepository;

    protected $_config;
    protected $guard;
    protected $per_page;

    public function __construct(NotificationRepository $userNotificationRepository)
    {
        $this->guard = 'user-api';
        request()->merge(['token' => 'true']);
        Auth::setDefaultDriver($this->guard);
        $this->_config = request('_config');
        $this->userNotificationRepository = $userNotificationRepository;
        $this->per_page = config('pagination.default');
        // permissions
        $this->middleware('auth:' . $this->guard);
    }


    public function getMyUserNotifications()
    {
        try {
            $userId = auth()->guard($this->guard)->id();
            $ownedUserNotifications = $this->userNotificationRepository->getUserNotificationsByUserId($userId)->paginate($this->per_page);
            $data=[
                'notifications' => new UserNotificationCollection($ownedUserNotifications),
                'unread_notifications_count'=>  $this->userNotificationRepository->getUnreadUserNotificationsCountByUserId($userId)
            ];
            return $this->successResponse( $data);
        } catch (Exception $e) {
            //dd($e->getMessage());
            return $this->errorResponse(
                [$e->getMessage()],
                __('app.something-went-wrong'),
                500
            );
        }
    }
    public function getUserNotificationTypes()
    {
        try {
            $data = NotificationTypeEnum::getConstants();
            return $this->successResponse($data);
        } catch (Exception $e) {
            return $this->errorResponse(
                [],
                __('app.something-went-wrong'),
                500
            );
        }
    }





    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {

            $deleted = $this->userNotificationRepository->deleteOne($id);

            if ($deleted) {
                return $this->messageResponse(
                    __('usernotification::app.userNotifications.deleted-successfully'),
                    true,
                    200
                );
            } {
                return $this->messageResponse(
                    __('usernotification::app.userNotifications.deleted-failed'),
                    false,
                    400
                );
            }
        } catch (Exception $e) {
            return $this->errorResponse(
                [],
                __('app.something-went-wrong'),
                500
            );
        }
    }
    public function markAllAsRead()
    {
        try {

            $markAsRead = $this->userNotificationRepository->markAllAsRead();

            if ($markAsRead) {
                return $this->messageResponse(
                    __('usernotification::app.userNotifications.marked-as-read-successfully'),
                    true,
                    200
                );
            } {
                return $this->messageResponse(
                    __('usernotification::app.userNotifications.marked-as-read-failed'),
                    false,
                    400
                );
            }
        } catch (Exception $e) {
            return $this->errorResponse(
                [],
                __('app.something-went-wrong'),
                500
            );
        }
    }
    public function markAsRead($id)
    {
        try {

            $markAsRead = $this->userNotificationRepository->markAsRead($id);

            if ($markAsRead) {
                return $this->messageResponse(
                    __('usernotification::app.userNotifications.marked-as-read-successfully'),
                    true,
                    200
                );
            } {
                return $this->messageResponse(
                    __('usernotification::app.userNotifications.marked-as-read-failed'),
                    false,
                    400
                );
            }
        } catch (Exception $e) {
            return $this->errorResponse(
                [],
                __('app.something-went-wrong'),
                500
            );
        }
    }
    public function markAsUnread($id)
    {
        try {

            $markAsUnread = $this->userNotificationRepository->markAsUnread($id);

            if ($markAsUnread) {
                return $this->messageResponse(
                    __('usernotification::app.userNotifications.marked-as-unread-successfully'),
                    true,
                    200
                );
            } {
                return $this->messageResponse(
                    __('usernotification::app.userNotifications.marked-as-unread-failed'),
                    false,
                    400
                );
            }
        } catch (Exception $e) {
            return $this->errorResponse(
                [],
                __('app.something-went-wrong'),
                500
            );
        }
    }
}
