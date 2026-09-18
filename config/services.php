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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'qpdf' => [
        'binary' => env('QPDF_BINARY', 'qpdf'),
    ],

    'pdftk' => [
        'binary' => env(
            'PDFTK_BINARY',
            'pdftk'
        ),
    ],

    'click_and_sign' => [

        'enabled' => env(
            'CLICK_AND_SIGN_ENABLED',
            false
        ),

        'url' => env(
            'CLICK_AND_SIGN_URL',
            'https://api.lleida.net/cs/v1'
        ),

        'user' => env(
            'CLICK_AND_SIGN_USER'
        ),

        'api_key' => env(
            'CLICK_AND_SIGN_API_KEY'
        ),

        'config_id' => env(
            'CLICK_AND_SIGN_CONFIG_ID'
        ),

        'callback_passphrase' => env(
            'CLICK_AND_SIGN_CALLBACK_PASSPHRASE'
        ),

        'company_name' => env(
            'CLICK_AND_SIGN_COMPANY_NAME'
        ),

        'company_vat' => env(
            'CLICK_AND_SIGN_COMPANY_VAT'
        ),
        'sms_sender' => env(
            'CLICK_AND_SIGN_SMS_SENDER',
            'Rembrandt'
        ),

        'callback_url' => env(
            'CLICK_AND_SIGN_CALLBACK_URL'
        ),
    ],

];
