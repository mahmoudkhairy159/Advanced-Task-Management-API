@component('mail::message')
# {{ __('user::app.auth.otp.otp_email_subject') }}

<div style="text-align: center; margin-bottom: 20px;">
    <img src="{{ $logoUrl }}" alt="{{ $websiteName }}" style="max-width: 150px;">
</div>

{{ __('user::app.auth.otp.your_otp_code_is', ['otp' => $otpCode]) }}

{{ __('user::app.auth.otp.otp_code_valid_for_x_minutes', ['minutes' => $minutes]) }}

@component('mail::button', ['url' => config('app.url')])
{{ __('user::app.auth.otp.visit_website') }}
@endcomponent

Thanks,<br>
{{ $websiteName }}
@endcomponent
