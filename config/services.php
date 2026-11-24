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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Mobile Money Services (Sénégal)
    |--------------------------------------------------------------------------
    */

    'wave' => [
        'api_key' => env('WAVE_API_KEY'),
        'api_secret' => env('WAVE_API_SECRET'),
        'webhook_secret' => env('WAVE_WEBHOOK_SECRET'),
        'base_url' => env('WAVE_BASE_URL', 'https://api.wave.com'),
        'mode' => env('WAVE_MODE', 'sandbox'), // sandbox | production
    ],

    'orange_money' => [
        'client_id' => env('ORANGE_MONEY_CLIENT_ID'),
        'client_secret' => env('ORANGE_MONEY_CLIENT_SECRET'),
        'webhook_token' => env('ORANGE_MONEY_WEBHOOK_TOKEN'),
        'merchant_code' => env('ORANGE_MONEY_MERCHANT_CODE'),
        'base_url' => env('ORANGE_MONEY_BASE_URL', 'https://api.orange.com'),
    ],

    'free_money' => [
        'api_key' => env('FREE_MONEY_API_KEY'),
        'merchant_id' => env('FREE_MONEY_MERCHANT_ID'),
        'base_url' => env('FREE_MONEY_BASE_URL', 'https://api.freemoney.sn'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS & Messaging Services
    |--------------------------------------------------------------------------
    */

    'sms' => [
        'provider' => env('SMS_PROVIDER', 'twilio'), // twilio | infobip | orange_sms
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'phone_number' => env('TWILIO_PHONE_NUMBER'),
    ],

    'infobip' => [
        'api_key' => env('INFOBIP_API_KEY'),
        'base_url' => env('INFOBIP_BASE_URL', 'https://api.infobip.com'),
    ],

    'orange_sms' => [
        'client_id' => env('ORANGE_SMS_CLIENT_ID'),
        'client_secret' => env('ORANGE_SMS_CLIENT_SECRET'),
        'access_token' => env('ORANGE_SMS_ACCESS_TOKEN'),
        'sender_address' => env('ORANGE_SMS_SENDER', 'SICOFIL'),
    ],

    'whatsapp' => [
        'api_key' => env('WHATSAPP_API_KEY'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    ],

];
