<?php

namespace Tests;

use BinaryCats\BigBlueButtonWebhooks\BigBlueButtonWebhooksServiceProvider;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    /**
     * Set up the environment.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        config()->set('bigbluebutton-webhooks.signing_secret', 'test_signing_secret');
    }

    protected function setUpDatabase(): void
    {
        $migration = include __DIR__.'/../vendor/spatie/laravel-webhook-client/database/migrations/create_webhook_calls_table.php.stub';

        $migration->up();
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return string[]
     */
    protected function getPackageProviders($app): array
    {
        return [
            BigBlueButtonWebhooksServiceProvider::class,
        ];
    }

    protected function disableExceptionHandling(): void
    {
        $this->app->instance(ExceptionHandler::class, new class () extends Handler {
            public function __construct()
            {
            }

            public function report(Exception $e)
            {
            }

            public function render($request, Exception $exception)
            {
                throw $exception;
            }
        });
    }

    /**
     * Determine the signature for a given payload and config key.
     */
    protected function determineBigBlueButtonSignature(array $payload = [], ?string $configKey = null): string
    {
        $secret = ($configKey) ?
            config("bigbluebutton-webhooks.signing_secret_{$configKey}") :
            config('bigbluebutton-webhooks.signing_secret');

        return $secret;
    }
}
