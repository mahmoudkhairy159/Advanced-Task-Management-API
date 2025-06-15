<?php

namespace Modules\User\App\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $otpCode;
    protected $minutes;
    protected $logoUrl;
    protected $websiteName;

    /**
     * Create a new message instance.
     *
     * @param string $otpCode
     * @param int $minutes
     */
    public function __construct(string $otpCode, int $minutes = 5)
    {
        $this->otpCode = $otpCode;
        $this->minutes = $minutes;
        // $this->logoUrl = asset('logo.png'); // Adjust logo path
        $this->logoUrl = asset('logo2.png'); // Adjust logo path
        $this->websiteName = config('app.name');   // Application name from config
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject(__('user::app.auth.otp.otp_email_subject'))
            ->markdown('emails.user_otp', [
                'otpCode' => $this->otpCode,
                'minutes' => $this->minutes,
                'logoUrl' => $this->logoUrl,
                'websiteName' => $this->websiteName,
            ]);
    }
}
