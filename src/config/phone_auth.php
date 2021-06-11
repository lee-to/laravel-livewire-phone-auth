<?php

return [
    "auth" => [
        "class_user" => "App\\Models\\User",
        "guard" => "web",
        "createUser" => true,
        "loginAfter" => true,
        "redirectTo" => false,
    ],

    "emitSendCode" => false,
    "emitBefore" => false,
    "emitAfter" => false,

    "code_length" => 4,
    "code_digits_only" => false,
    "verify_code_dynamic" => false,
    "limit_send_count" => 3,
    "next_send_after" => 30,
    "expire_seconds" => 240,
    "flushCode" => true,

    "custom_phone_field_name" => "phone", //If you need to use without a form

    "custom_fields" => ["name", "email"],
    "custom_fields_rules" => [
        "name" => "required",
        "email" => "required|email",
    ],

    "sms_service" => [
        "class" => \Leeto\PhoneAuth\SmsServiceExample::class,
        "settings" => [],
    ]
];
