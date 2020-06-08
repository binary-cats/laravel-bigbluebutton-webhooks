# Handle BigBlueButton Webhooks in a Laravel application

![https://github.com/binary-cats/laravel-bigbluebutton-webhooks/actions](https://github.com/binary-cats/laravel-bigbluebutton-webhooks/workflows/Laravel/badge.svg)
![https://github.styleci.io/repos/270848719](https://github.styleci.io/repos/270848719/shield)
![https://scrutinizer-ci.com/g/binary-cats/laravel-bigbluebutton-webhooks/](https://scrutinizer-ci.com/g/binary-cats/laravel-bigbluebutton-webhooks/badges/quality-score.png?b=master)

[BigBlueButton](https://bigbluebutton.org/) can notify your application of mail events using webhooks. This package can help you handle those webhooks. Out of the box it will verify the BigBlueButton signature of all incoming requests. It appears that the signature validation is below simplie right now and does not match the documentation. All valid calls will be logged to the database. You can easily define jobs or events that should be dispatched when specific events hit your app.

This package will not handle what should be done after the webhook request has been validated and the right job or event is called. You should still code up any work (eg. regarding payments) yourself.

Before using this package we highly recommend reading [the entire documentation on webhooks over at BigBlueButton](http://docs.bigbluebutton.org/).

This package is an almost line-to-line adapted copy of absolutely amazing [spatie/laravel-stripe-webhooks](https://github.com/spatie/laravel-stripe-webhooks)

## Installation

You can install the package via composer:

```bash
composer require binary-cats/laravel-bigbluebutton-webhooks
```

The service provider will automatically register itself.

You must publish the config file with:
```bash
php artisan vendor:publish --provider="BinaryCats\BigBlueButtonWebhooks\BigBlueButtonWebhooksServiceProvider" --tag="config"
```

This is the contents of the config file that will be published at `config/bigbluebutton-webhooks.php`:

```php
return [

    /*
     * BigBlueButton will sign each webhook using a shared secret.
     */
    'signing_secret' => env('BBB_SECRET'),

    /*
     * You can define the job that should be run when a certain webhook hits your application
     * here. The key is the name of the BigBlueButton event type with the `.` replaced by a `_`.
     */
    'jobs' => [
        'meeting-created' => \BinaryCats\BigBlueButtonWebhooks\Jobs\MeetingCreatedJob::class,
    ],

    /*
     * The classname of the model to be used. The class should equal or extend
     * Spatie\WebhookClient\Models\WebhookCall
     */
    'model' => \BinaryCats\BigBlueButtonWebhooks\WebhookCall::class,

    /*
     * The classname of the model to be used. The class should equal or extend
     * Spatie\WebhookClient\ProcessWebhookJob
     */
    'process_webhook_job' => \BinaryCats\BigBlueButtonWebhooks\ProcessBigBlueButtonWebhookJob::class,
];
```

In the `signing_secret` key of the config file you should add a valid shared secret of the BigBlueButton server.

**You can skip migrating is you have already installed `Spatie\WebhookClient`**

Next, you must publish the migration with:
```bash
php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="migrations"
```

After migration has been published you can create the `webhook_calls` table by running the migrations:

```bash
php artisan migrate
```

### Routing
Finally, take care of the routing: You must configure at what url BigBlueButton webhooks should hit your app. In the routes file of your app you must pass that route to `Route::bigbluebuttonWebhooks()`:

I like to group functionality by domain, so I would suggest `webhooks/bigbluebutton` (especially if you plan to have more webhooks), but it is really up to you.

```php
# routes\web.php
Route::bigbluebuttonWebhooks('webhooks/bigbluebutton');
```

Behind the scenes this will register a `POST` route to a controller provided by this package. Because BigBlueButton has no way of getting a csrf-token, you must add that route to the `except` array of the `VerifyCsrfToken` middleware:

```php
protected $except = [
    'webhooks/bigbluebutton',
];
```

## Usage

BigBlueButton will send out webhooks for several event types. I tried to locate the comprehensive list of all BigBLueButton events, however in vain. If you have it, please let me know

BigBlueButton will sign all requests hitting the webhook url of your app. This package will automatically verify if the signature is valid. If it is not, the request was probably not sent by BigBlueButton.

Unless something goes terribly wrong, this package will always respond with a `200` to webhook requests. Sending a `200` will prevent BigBlueButton from resending the same event over and over again. All webhook requests with a valid signature will be logged in the `webhook_calls` table. The table has a `payload` column where the entire payload of the incoming webhook is saved.

If the signature is not valid, the request will NOT be logged in the `webhook_calls` table but a `BinaryCats\BigBlueButtonWebhooks\Exceptions\WebhookFailed` exception will be thrown.
If something goes wrong during the webhook request the thrown exception will be saved in the `exception` column. In that case the controller will send a `500` instead of `200`.

**N.B.: According to the docs:**

> Hooks are only removed if a call to /hooks/destroy is made or if the callbacks for the hook fail too many times (~12) for a long period of time (~5min).

There are two ways this package enables you to handle webhook requests: you can opt to queue a job or listen to the events the package will fire.

### Handling webhook requests using jobs
If you want to do something when a specific event type comes in you can define a job that does the work. Here's an example of such a job:

```php
<?php

namespace App\Jobs\BigBlueButtonWebhooks;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\WebhookClient\Models\WebhookCall;

class HandleDelivered implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /** @var \Spatie\WebhookClient\Models\WebhookCall */
    public $webhookCall;

    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }

    public function handle()
    {
        // do your work here

        // you can access the payload of the webhook call with `$this->webhookCall->payload`
    }
}
```

Spatie highly recommends that you make this job queueable, because this will minimize the response time of the webhook requests. This allows you to handle more BigBlueButton webhook requests and avoid timeouts.

Take a second to review `BinaryCats\BigBlueButtonWebhooks\Tests\PayloadDefinition::getPayloadDefinition()` to see how the payload is structured.

After having created your job you must register it at the `jobs` array in the `bigbluebutton-webhooks.php` config file. The key should be the name of BigBlueButton event type where but with the `.` replaced by `_`. The value should be the fully qualified classname.

```php
// config/bigbluebutton-webhooks.php

'jobs' => [
    'meeting-created' => \App\Jobs\BigBlueButtonWebhooks\MeetingCreatedJob::class,
],
```

### Handling webhook requests using events

Instead of queueing jobs to perform some work when a webhook request comes in, you can opt to listen to the events this package will fire. Whenever a valid request hits your app, the package will fire a `bigbluebutton-webhooks::<name-of-the-event>` event.

The payload of the events will be the instance of `WebhookCall` that was created for the incoming request.

Let's take a look at how you can listen for such an event. In the `EventServiceProvider` you can register listeners.

```php
/**
 * The event listener mappings for the application.
 *
 * @var array
 */
protected $listen = [
    'bigbluebutton-webhooks::meeting-created' => [
        App\Listeners\MeetingCreatedListener::class,
    ],
];
```

Here's an example of such a listener:

```php
<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\WebhookClient\Models\WebhookCall;

class MeetingCreatedListener implements ShouldQueue
{
    public function handle(WebhookCall $webhookCall)
    {
        // do your work here

        // you can access the payload of the webhook call with `$webhookCall->payload`
    }
}
```

Spatie highly recommends that you make the event listener queueable, as this will minimize the response time of the webhook requests. This allows you to handle more BigBlueButton webhook requests and avoid timeouts.

The above example is only one way to handle events in Laravel. To learn the other options, read [the Laravel documentation on handling events](https://laravel.com/docs/7.x/events).

## Advanced usage

### Retry handling a webhook

All incoming webhook requests are written to the database. This is incredibly valuable when something goes wrong while handling a webhook call. You can easily retry processing the webhook call, after you've investigated and fixed the cause of failure, like this:

```php
use Spatie\WebhookClient\Models\WebhookCall;
use BinaryCats\BigBlueButtonWebhooks\ProcessBigBlueButtonWebhookJob;

dispatch(new ProcessBigBlueButtonWebhookJob(WebhookCall::find($id)));
```

### Performing custom logic

You can add some custom logic that should be executed before and/or after the scheduling of the queued job by using your own job class. You can do this by specifying your own job class in the `process_webhook_job` key of the `bigbluebutton-webhooks` config file. The class should extend `BinaryCats\BigBlueButtonWebhooks\ProcessBigBlueButtonWebhookJob`.

Here's an example:

```php
use BinaryCats\BigBlueButtonWebhooks\ProcessBigBlueButtonWebhookJob;

class MyCustomBigBlueButtonWebhookJob extends ProcessBigBlueButtonWebhookJob
{
    public function handle()
    {
        // do some custom stuff beforehand

        parent::handle();

        // do some custom stuff afterwards
    }
}
```
### Handling multiple signing secrets

When needed might want to the package to handle multiple endpoints and secrets. Here's how to configurate that behaviour.

If you are using the `Route::bigbluebuttonWebhooks` macro, you can append the `configKey` as follows:

```php
Route::bigbluebuttonWebhooks('webhooks/bigbluebutton/{configKey}');
```

Alternatively, if you are manually defining the route, you can add `configKey` like so:

```php
Route::post('webhooks/bigbluebutton/{configKey}', 'BinaryCats\BigBlueButtonWebhooks\BigBlueButtonWebhooksController');
```

If this route parameter is present the verify middleware will look for the secret using a different config key, by appending the given the parameter value to the default config key. E.g. If BigBlueButton posts to `webhooks/bigbluebutton/my-named-secret` you'd add a new config named `signing_secret_my-named-secret`.

Example config might look like:

```php
// secret for when BigBlueButton posts to webhooks/bigbluebutton/account
'signing_secret_account' => 'whsec_abc',
// secret for when BigBlueButton posts to webhooks/bigbluebutton/my-named-secret
'signing_secret_my-named-secret' => 'whsec_123',
```

### About BigBlueButton

[BigBlueButton](https://www.bigbluebutton.org/) is an open source web conferencing system for online learning.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information about what has changed recently.

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email cyrill.kalita@gmail.com instead of using issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

## Credits

- [Cyrill Kalita](https://github.com/binary-cats)
- [All Contributors](../../contributors)

Big shout-out to [Spatie](https://spatie.be/) for their work, which is a huge inspiration.

## Support us

Binary Cats is a webdesign agency based in Illinois, US.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
