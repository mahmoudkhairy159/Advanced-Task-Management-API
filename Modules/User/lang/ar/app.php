<?php

return [
    "users" => [
        "users" => "المستخدمون",
        "user" => "مستخدم",
        "show" => "عرض المستخدمين",
        "create" => "إنشاء مستخدم",
        "update" => "تحديث مستخدم",
        "delete" => "حذف مستخدم",
        "destroy" => "تدمير مستخدم",
        "created-successfully" => "تم إنشاء المستخدم بنجاح",
        "updated-successfully" => "تم تحديث المستخدم بنجاح",
        "deleted-successfully" => "تم حذف المستخدم بنجاح",
        "followed-successfully" => "تم متابعة المستخدم بنجاح",
        "unFollowed-successfully" => "تم إلغاء متابعة المستخدم بنجاح",
        "created-failed" => "فشل إنشاء المستخدم",
        "updated-failed" => "فشل تحديث المستخدم",
        "deleted-failed" => "فشل حذف المستخدم",
        "followed-failed" => "فشل متابعة المستخدم",
        "unFollowed-failed" => "فشل إلغاء متابعة المستخدم",
        "current-password-incorrect" => "كلمة المرور الحالية للمستخدم غير صحيحة",
    ],
    "userProfiles" => [
        "userProfiles" => "ملفات تعريف المستخدمين",
        "role" => "ملف تعريف المستخدم",
        "show" => "عرض ملفات تعريف المستخدمين",
        "create" => "إنشاء ملف تعريف مستخدم",
        "update" => "تحديث ملف تعريف مستخدم",
        "delete" => "حذف ملف تعريف مستخدم",
        "destroy" => "تدمير ملف تعريف مستخدم",
        "created-successfully" => "تم إنشاء ملف تعريف المستخدم بنجاح",
        "updated-successfully" => "تم تحديث ملف تعريف المستخدم بنجاح",
        "deleted-successfully" => "تم حذف ملف تعريف المستخدم بنجاح",
        "created-failed" => "فشل إنشاء ملف تعريف المستخدم",
        "updated-failed" => "فشل تحديث ملف تعريف المستخدم",
        "deleted-failed" => "فشل حذف ملف تعريف المستخدم",
    ],

    'auth' => [
        'otp' => [
            'your_otp_code_is' => 'رمز OTP الخاص بك هو : :otp',
            'otp_code_valid_for_x_minutes' => 'رمز OTP هذا صالح لمدة :minutes دقيقة.',
            'otp_email_subject' => 'رمز ال OTP لجيوفيكا',
            'ignore_message' => 'إذا لم تطلب هذا الرمز، يرجى تجاهل هذا البريد الإلكتروني. لا يلزم اتخاذ أي إجراء إضافي.',
            'greeting_message' => 'مرحبًا،  لقد طلبت رمز OTP للتحقق.',
            'footer_message' => 'تم إرسال هذا البريد الإلكتروني إليك من قبل :website لأغراض التحقق. إذا كان لديك أي أسئلة، يرجى الاتصال بالدعم.',

        ],
        'register' => [
            'success_register_message' => 'تم التسجيل بنجاح، وتم إرسال رمز التحقق عبر البريد الإلكتروني.',
        ],
        'login' => [
            'invalid_email_or_password' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة',
            'your_account_is_blocked' => 'تم حظر حسابك',
            'your_account_is_inactive' => 'حسابك غير نشط',
            'logged_in_successfully' => 'تم تسجيل الدخول بنجاح',
            'logged_in_successfully_and_Verification_code_sent' => 'تم تسجيل الدخول بنجاح وتم إرسال رمز التحقق عبر البريد الإلكتروني.',
            'logged_in_successfully_and_Verification_code_already_sent' => 'تم تسجيل الدخول بنجاح وتم إرسال رمز التحقق عبر البريد الإلكتروني بالفعل.',
        ],
        'verification' => [
            'invalid_otp' => 'رمز OTP غير صحيح',
            'valid_otp' => 'رمز OTP صحيح',
            'verification_failed' => 'فشل التحقق',
            'already_verified' => 'تم التحقق بالفعل',
            'verified_successfully' => 'تم التحقق بنجاح',
            'cant_resend_verification_otp_code' => 'لا يمكن إعادة إرسال رمز التحقق OTP',
            'verification_otp_code_resend_successfully' => 'تمت إعادة إرسال رمز التحقق OTP بنجاح',
            'already_sent_verification_otp_code' => 'تم إرسال رمز التحقق OTP إلى بريدك الإلكتروني بالفعل',
        ],
        'forgotPassword' => [
            'user_not_found' => 'لم يتم العثور على مستخدم باستخدام عنوان البريد الإلكتروني هذا.',
            'otp_code_email_sent_successfully' => 'تم إرسال رمز OTP لإعادة تعيين كلمة المرور بنجاح',
        ],
        'resetPassword' => [
            'reset-successfully' => 'تم إعادة تعيين كلمة المرور بنجاح',
            'reset-failed' => 'تعذر إعادة تعيين كلمة المرور. يرجى المحاولة لاحقاً.',
        ],
        'logout' => [
            'logout_successfully' => 'تم تسجيل الخروج بنجاح.',
            'otp_code_email_sent_successfully' => 'تم إرسال رمز OTP لإعادة تعيين كلمة المرور بنجاح',
        ],
        'reset_password_otp' => [
            'subject' => 'رمز OTP لإعادة تعيين كلمة المرور',
            'greeting_message' => 'لقد طلبت إعادة تعيين كلمة المرور الخاصة بك.',
            'your_otp_code_is' => 'رمز OTP الخاص بك هو: :otp',
            'otp_code_valid_for_x_minutes' => 'هذا الرمز صالح لمدة :minutes دقيقة.',
            'ignore_message' => 'إذا لم تطلب إعادة تعيين كلمة المرور، يرجى تجاهل هذا البريد الإلكتروني.',
            'footer_message' => 'شكرًا لاستخدامك :website.',
        ],


    ]
];
