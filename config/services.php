<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'kavenegar' => [
        'enabled' => env('KAVENEGAR_SMS_ENABLED', false),
        'api_key' => env('KAVENEGAR_API_KEY'),
        'otp' => [
            'template' => env('KAVENEGAR_OTP_TEMPLATE'),
        ],
        'workspace_invite' => [
            'template' => env('KAVENEGAR_WORKSPACE_INVITE_TEMPLATE'),
            'token' => env('KAVENEGAR_WORKSPACE_INVITE_TOKEN', 'invitation_code'),
            'token2' => env('KAVENEGAR_WORKSPACE_INVITE_TOKEN2', 'role_name'),
            'token3' => env('KAVENEGAR_WORKSPACE_INVITE_TOKEN3', 'expires_at'),
            'token10' => env('KAVENEGAR_WORKSPACE_INVITE_TOKEN10', 'workspace_name'),
            'token20' => env('KAVENEGAR_WORKSPACE_INVITE_TOKEN20', 'inviter_name'),
        ],
    ],

];
