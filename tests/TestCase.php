<?php

namespace Tests;

use BinaryCats\BigBlueButtonWebhooks\BigBlueButtonWebhooksServiceProvider;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    /**
     * Set up the environment.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        config()->set('bigbluebutton-webhooks.signing_secret', 'test_signing_secret');
    }

    /**
     * @return void
     */
    protected function setUpDatabase()
    {
        $migration = include __DIR__.'/../vendor/spatie/laravel-webhook-client/database/migrations/create_webhook_calls_table.php.stub';

        $migration->up();
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return string[]
     */
    protected function getPackageProviders($app)
    {
        return [
            BigBlueButtonWebhooksServiceProvider::class,
        ];
    }

    /**
     * @return void
     */
    protected function disableExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, new class extends Handler
        {
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
     * @param  array  $payload
     * @param  string|null  $configKey
     * @return string
     */
    protected function determineBigBlueButtonSignature(array $payload, string $configKey = null): string
    {
        $secret = ($configKey) ?
            config("bigbluebutton-webhooks.signing_secret_{$configKey}") :
            config('bigbluebutton-webhooks.signing_secret');

        return $secret;
    }
}
