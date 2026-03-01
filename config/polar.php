<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Polar Access Token
    |--------------------------------------------------------------------------
    |
    | The Polar access token is used to authenticate with the Polar API.
    | You can find your access token in the Polar dashboard > Settings
    | under the "Developers" section.
    |
    */
    'access_token' => env('POLAR_ACCESS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Polar Server
    |--------------------------------------------------------------------------
    |
    | The Polar server environment to use for API requests.
    | Available options: "production" or "sandbox"
    |
    | - production: https://api.polar.sh (Production environment)
    | - sandbox: https://sandbox-api.polar.sh (Sandbox environment)
    |
    */
    'server' => env('POLAR_SERVER', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Polar Webhook Secret
    |--------------------------------------------------------------------------
    |
    | The Polar webhook secret is used to verify that the webhook requests
    | are coming from Polar. You can find your webhook secret in the Polar
    | dashboard > Settings > Webhooks on each registered webhook.
    |
    | We (the developers) recommend using a single webhook for all your
    | integrations. This way you can use the same secret for all your
    | integrations and you don't have to manage multiple webhooks.
    |
    */
    'webhook_secret' => env('POLAR_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Polar Url Path
    |--------------------------------------------------------------------------
    |
    | This is the base URI where routes from Polar will be served
    | from. The URL built into Polar is used by default; however,
    | you can modify this path as you see fit for your application.
    |
    */
    'path' => env('POLAR_PATH', 'polar'),

    /*
    |--------------------------------------------------------------------------
    | Middleware Redirect URL
    |--------------------------------------------------------------------------
    |
    | When a user fails the polar.subscribed middleware check and the request
    | is not expecting JSON, they will be redirected to this URL. If null,
    | a 403 response is returned instead.
    |
    */
    'middleware_redirect_url' => null,

    /*
    |--------------------------------------------------------------------------
    | Polar Organization ID
    |--------------------------------------------------------------------------
    |
    | Your Polar organization ID. This is used as the default for billable
    | methods like validateLicenseKey(), activateLicenseKey(), etc. so you
    | don't have to pass it every time. You can find your organization ID
    | in the Polar dashboard under Settings.
    |
    */
    'organization_id' => env('POLAR_ORGANIZATION_ID'),

    /*
    |--------------------------------------------------------------------------
    | Custom Webhook Handlers
    |--------------------------------------------------------------------------
    |
    | Register custom webhook handlers for specific event types. Each handler
    | must implement the Climactic\LaravelPolar\Contracts\WebhookHandler
    | interface. Custom handlers override the built-in handling for that event.
    |
    */
    'webhook_handlers' => [
        // 'subscription.created' => App\Webhooks\CustomHandler::class,
    ],
];
