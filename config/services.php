<?php

return [

    'asaas' => [
        'url' => env('ASAAS_URL', 'https://api-sandbox.asaas.com'),
        'token' => env('ASAAS_TOKEN'),
        'webhook_token' => env('ASAAS_WEBHOOK_TOKEN'),
        'user_agent' => env('ASAAS_USER_AGENT', 'Casa das Casinhas/1.0'),
        'success_url' => env('ASAAS_SUCCESS_URL'),
        'allowed_payment_hosts' => ['sandbox.asaas.com', 'www.asaas.com', 'asaas.com'],
    ],

    'superfrete' => [
        'url' => env('SUPERFRETE_URL', 'https://sandbox.superfrete.com'),
        'token' => env('SUPERFRETE_TOKEN'),
        'origin' => [
            'name' => env('SUPERFRETE_ORIGIN_NAME'),
            'document' => env('SUPERFRETE_ORIGIN_DOCUMENT'),
            'postal_code' => env('SUPERFRETE_ORIGIN_POSTAL_CODE'),
            'address' => env('SUPERFRETE_ORIGIN_ADDRESS'),
            'number' => env('SUPERFRETE_ORIGIN_NUMBER'),
            'complement' => env('SUPERFRETE_ORIGIN_COMPLEMENT'),
            'district' => env('SUPERFRETE_ORIGIN_DISTRICT'),
            'city' => env('SUPERFRETE_ORIGIN_CITY'),
            'state_abbr' => env('SUPERFRETE_ORIGIN_STATE'),
        ],
        'non_commercial' => env('SUPERFRETE_NON_COMMERCIAL', true),
        'user_agent' => env('SUPERFRETE_USER_AGENT', 'Casa das Casinhas contato@example.com'),
    ],

    'whatsapp' => [
        'url' => env('WHATSAPP_URL', 'https://graph.facebook.com/v22.0'),
        'token' => env('WHATSAPP_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'recipient' => env('WHATSAPP_RECIPIENT'),
        'template' => env('WHATSAPP_TEMPLATE', 'pedido_pago'),
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
        'app_secret' => env('WHATSAPP_APP_SECRET'),
    ],

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

];
