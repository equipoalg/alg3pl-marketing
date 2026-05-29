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
        'webhook_url' => env('SLACK_WEBHOOK_URL'),
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model'   => env('ANTHROPIC_MODEL', 'claude-3-5-haiku-20241022'),
        'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4.1-mini'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com'),
    ],

    'ai' => [
        'provider' => env('AI_PROVIDER', env('ONUX_AI_PROVIDER', 'openai')),
    ],

    'qwen' => [
        'api_key' => env('QWEN_API_KEY', env('DASHSCOPE_API_KEY')),
        'base_url' => env('QWEN_BASE_URL', 'https://dashscope-us.aliyuncs.com/compatible-mode/v1'),
        'model' => env('QWEN_MODEL', 'qwen-plus'),
    ],

    'google_ads' => [
        'developer_token'      => env('GOOGLE_ADS_DEVELOPER_TOKEN'),
        'customer_id'          => env('GOOGLE_ADS_CUSTOMER_ID'),
        'login_customer_id'    => env('GOOGLE_ADS_LOGIN_CUSTOMER_ID'),
        'oauth_client_id'      => env('GOOGLE_ADS_OAUTH_CLIENT_ID'),
        'oauth_client_secret'  => env('GOOGLE_ADS_OAUTH_CLIENT_SECRET'),
        'refresh_token'        => env('GOOGLE_ADS_REFRESH_TOKEN'),
    ],

    'google' => [
        'credentials_path' => env('GOOGLE_APPLICATION_CREDENTIALS'),
        'analytics_account' => env('GOOGLE_ANALYTICS_ACCOUNT'),
    ],

    'webhook' => [
        'fluent_forms_secret' => env('WEBHOOK_FLUENT_FORMS_SECRET'),
    ],

    'whatsapp' => [
        'provider' => env('WHATSAPP_PROVIDER', 'meta'),
        'api_token' => env('WHATSAPP_API_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'webhook_verify_token' => env('WHATSAPP_WEBHOOK_VERIFY_TOKEN', env('WHATSAPP_VERIFY_TOKEN')),
        'graph_version' => env('WHATSAPP_GRAPH_VERSION', 'v25.0'),
        'd360_api_key' => env('WHATSAPP_360DIALOG_API_KEY', env('D360_API_KEY')),
        'd360_api_base' => env('WHATSAPP_360DIALOG_API_BASE', 'https://waba-v2.360dialog.io'),
        'twilio_account_sid' => env('TWILIO_ACCOUNT_SID'),
        'twilio_auth_token' => env('TWILIO_AUTH_TOKEN'),
        'twilio_whatsapp_from' => env('TWILIO_WHATSAPP_FROM'),
        'twilio_messaging_service_sid' => env('TWILIO_MESSAGING_SERVICE_SID'),
        'twilio_status_callback_url' => env('TWILIO_STATUS_CALLBACK_URL'),
        'twilio_webhook_url' => env('TWILIO_WEBHOOK_URL'),
        'twilio_validate_webhook' => env('TWILIO_VALIDATE_WEBHOOK', false),
        'twilio_content_templates' => json_decode(env('TWILIO_CONTENT_TEMPLATE_MAP', '[]'), true) ?: [],
    ],

    'onux' => [
        'whatsapp_alert_numbers' => env('ONUX_WHATSAPP_ALERT_NUMBERS', ''),
        'whatsapp_alert_group_ids' => env('ONUX_WHATSAPP_ALERT_GROUP_IDS', ''),
        'whatsapp_allowed_numbers' => env('ONUX_WHATSAPP_ALLOWED_NUMBERS', ''),
        'whatsapp_allowed_group_ids' => env('ONUX_WHATSAPP_ALLOWED_GROUP_IDS', ''),
        'whatsapp_allow_sandbox_participants' => env('ONUX_WHATSAPP_ALLOW_SANDBOX_PARTICIPANTS', false),
    ],

];
