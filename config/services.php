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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],
    'bkash' => [
        'base_url'  => env('BKASH_SANDBOX_BASE_URL', 'https://tokenized.sandbox.bka.sh/v1.2.0-beta'),
        'checkout_base_url' => env('BKASH_CHECKOUT_BASE_URL', 'https://checkout.sandbox.bka.sh/v1.2.0-beta'),
        'app_key'   => env('BKASH_SANDBOX_APP_KEY'),
        'app_secret'=> env('BKASH_SANDBOX_APP_SECRET'),
        'username'  => env('BKASH_SANDBOX_USERNAME'),
        'password'  => env('BKASH_SANDBOX_PASSWORD'),
        'agreement_callback_url'  => env('BKASH_AGREEMENT_CALLBACK_URL'),
        'payment_callback_url'  => env('BKASH_PAYMENT_CALLBACK_URL'),

    ],
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
