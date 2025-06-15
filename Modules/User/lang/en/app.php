<?php

return [
    'users' => [
        'users' => 'Users',
        'user' => 'User',
        'show' => 'Show Users',
        'create' => 'create a User',
        'update' => 'update a User',
        'delete' => 'delete a User',
        'destroy' => 'destroy a User',
        'created-successfully' => 'User created successfully',
        'updated-successfully' => 'User updated successfully',
        'deleted-successfully' => 'User deleted successfully',
        'followed-successfully' => 'User followed successfully',
        'unFollowed-successfully' => 'User unFollowed successfully',
        'created-failed' => 'User created failed',
        'updated-failed' => 'User updated failed',
        'deleted-failed' => 'User deleted failed',
        'followed-failed' => 'User followed failed',
        'unFollowed-failed' => 'User unFollowed failed',
        'current-password-incorrect' => 'User current password incorrect',
        'already-banned' => 'User already banned',
        'already-unbanned' => 'User already unbanned',
        'banned-successfully' => 'User banned successfully',
        'banned-failed' => 'User banned failed',
        'unbanned-successfully' => 'User unbanned successfully',
        'unbanned-failed' => 'User unbanned failed',
    ],
    'userProfiles' => [
        'userProfiles' => 'UserProfiles',
        'role' => 'UserProfile',
        'show' => 'Show UserProfiles',
        'create' => 'create a UserProfile',
        'update' => 'update a UserProfile',
        'delete' => 'delete a UserProfile',
        'destroy' => 'destroy a UserProfile',
        'created-successfully' => 'UserProfile created successfully',
        'updated-successfully' => 'UserProfile updated successfully',
        'deleted-successfully' => 'UserProfile deleted successfully',
        'created-failed' => 'UserProfile created failed',
        'updated-failed' => 'UserProfile updated failed',
        'deleted-failed' => 'UserProfile deleted failed',
    ],

    'auth' => [

        'otp' => [

            'your_otp_code_is' => 'Your OTP Code Is : :otp',
            'otp_code_valid_for_x_minutes' => 'This OTP Code  is valid for :minutes minutes.',
            'otp_email_subject' => 'Safqa OTP Code',
            'ignore_message' => 'If you did not request this code, please ignore this email. No further action is required.',
            'greeting_message' => 'Hello, ! You requested an OTP code for verification.',
            'footer_message' => 'This email was sent to you by :website for verification purposes. If you have any questions, please contact support.',
            'visit_website'=> 'Visit Website'

        ],
        'register' => [
            'success_register_message' => 'Registration successful, And The Verification code sent via email. '
        ],
        'login' => [
            'invalid_email_or_password' => 'Invalid Email or Password',
            'your_account_is_blocked' => 'Your Account is blocked',
            'your_account_is_inactive' => 'Your Account is inactive',
            'logged_in_successfully' => 'Logged In Successfully',
            'logged_in_successfully_and_Verification_code_sent' => 'Logged In Successfully And The Verification code sent via email. ',
            'logged_in_successfully_and_Verification_code_already_sent' => 'Logged In Successfully And The Verification code already sent via email. ',

        ],
        'verification' => [
            'invalid_otp' => 'Invalid otp',
            'valid_otp' => 'Valid otp',
            'verification_failed' => 'Verification Failed',
            'already_verified' => 'Already Verified',
            'verified_successfully' => 'Verification Successfully',
            'cant_resend_verification_otp_code' => 'Cant Resend Verification OTP Code',
            'verification_otp_code_resend_successfully' => 'Verification OTP Code Resend SuccessFully',
            'already_sent_verification_otp_code' => 'verification otp code is already sent to your email'
        ],
        'forgotPassword' => [
            'user_not_found' => 'User not found with this email address.',
            'otp_code_email_sent_successfully' => 'OTP code for reset password sent successfully',
        ],
        'resetPassword' => [
            'reset-successfully' => 'Password reset successfully',
            'reset-failed' => 'Unable to reset password. Please try again later.y',
        ],

        'logout' => [
            'logout_successfully' => 'User Logged out successfully.',
            'otp_code_email_sent_successfully' => 'OTP code for reset password sent successfully',
        ],

        'reset_password_otp' => [
            'subject' => 'Reset Password OTP Code',
            'greeting_message' => 'You requested to reset your password.',
            'your_otp_code_is' => 'Your OTP code is: :otp',
            'otp_code_valid_for_x_minutes' => 'This OTP code is valid for :minutes minutes.',
            'ignore_message' => 'If you did not request a password reset, please ignore this email.',
            'footer_message' => 'Thank you for using :website.',
        ],

    ]
];
