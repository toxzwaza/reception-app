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

    'teams' => [
        'default_webhook_url' => env('TEAMS_DEFAULT_WEBHOOK_URL'),
        'interview_webhook_url' => env('TEAMS_INTERVIEW_WEBHOOK_URL'),
        'departments' => [
            1 => env('TEAMS_DEPT_SALES_WEBHOOK_URL'),      // 営業部
            2 => env('TEAMS_DEPT_GENERAL_WEBHOOK_URL'),    // 総務部
            3 => env('TEAMS_DEPT_ACCOUNTING_WEBHOOK_URL'), // 経理部
            4 => env('TEAMS_DEPT_HR_WEBHOOK_URL'),         // 人事部
            5 => env('TEAMS_DEPT_DEV_WEBHOOK_URL'),        // 開発部
            6 => env('TEAMS_DEPT_MARKETING_WEBHOOK_URL'),  // マーケティング部
        ],
    ],

];
