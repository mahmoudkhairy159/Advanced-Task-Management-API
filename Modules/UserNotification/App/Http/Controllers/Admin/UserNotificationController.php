<?php

namespace Modules\UserNotification\App\Http\Controllers\Admin;

use Exception;
use App\Traits\ApiResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\UserNotification\App\Enums\NotificationTypeEnum;
use Modules\UserNotification\App\Repositories\NotificationRepository;
use Modules\UserNotification\App\Transformers\Admin\UserNotification\UserNotificationCollection;
use Modules\UserNotification\App\Transformers\Admin\UserNotification\UserNotificationResource;

class UserNotificationController extends Controller
{
    use ApiResponseTrait;


    protected $userNotificationRepository;

    protected $_config;
    protected $guard;

    public function __construct(NotificationRepository $userNotificationRepository)
    {
        $this->guard = 'admin-api';
        request()->merge(['token' => 'true']);
        Auth::setDefaultDriver($this->guard);
        $this->_config = request('_config');
        $this->userNotificationRepository = $userNotificationRepository;
        // permissions
        $this->middleware('auth:' . $this->guard);
        $this->middleware(['permission:userNotifications.show'])->only(['index', 'show']);
        $this->middleware(['permission:userNotifications.destroy'])->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = $this->userNotificationRepository->getAll()->paginate();
            return $this->successResponse(new UserNotificationCollection($data));
        } catch (Exception $e) {
            return $this->errorResponse(
                [$e->getMessage(), $e->getCode()],
                __('app.something-went-wrong'),
                500
            );
        }
    }

    public function getByUserId($userId)
    {
        try {
            $data = $this->userNotificationRepository->getUserNotificationsByUserId($userId)->paginate();
            return $this->successResponse(new UserNotificationCollection($data));
        } catch (Exception $e) {
            return $this->errorResponse(
                [],
                __('app.something-went-wrong'),
                500
            );
        }
    }



    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        try {
            $data = $this->userNotificationRepository->getOneById($id);
            return $this->successResponse(new UserNotificationResource($data));
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

            $deleted = $this->userNotificationRepository->delete($id);

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

}
