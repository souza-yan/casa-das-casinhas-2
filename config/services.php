<?php

return [

    'asaas' => [
        'url' => env('ASAAS_URL', 'https://api-sandbox.asaas.com'),
        'token' => env('ASAAS_TOKEN'),
        'webhook_token' => env('ASAAS_WEBHOOK_TOKEN'),
    ],

    'melhor_envio' => [
        'url' => env('MELHOR_ENVIO_URL', 'https://sandbox.melhorenvio.com.br'),
        'token' => env('MELHOR_ENVIO_TOKEN'),
        'origin_zip_code' => env('MELHOR_ENVIO_ORIGIN_ZIP_CODE'),
        'user_agent' => env('MELHOR_ENVIO_USER_AGENT', 'Casa das Casinhas contato@example.com'),
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
