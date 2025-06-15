<?php

namespace Modules\User\App\Repositories;

use Modules\User\App\Models\UserOTP;
use Prettus\Repository\Eloquent\BaseRepository;

class OtpRepository extends BaseRepository
{
    public function model()
    {
        return UserOTP::class;
    }
    public function storeOtp($userId, $otp, $expiresAt)
    {
        return UserOTP::create([
            'user_id' => $userId,
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ]);
    }

    public function getByUserId($userId)
    {

        $this->deleteExpired();
        return UserOTP::where('user_id', $userId)->latest()->first();
    }

    public function checkOtpCodeExpirationByUserId($userId)
    {
        $this->deleteExpired();
        return UserOTP::where('user_id', $userId)->where('expires_at', '>', now())->exists();
    }



    public function deleteExpired()
    {
        UserOTP::where('expires_at', '<', now())->delete();
    }
}
