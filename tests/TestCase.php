<?php

namespace BinaryCats\BigBlueButtonWebhooks\Tests;

use BinaryCats\BigBlueButtonWebhooks\BigBlueButtonWebhooksServiceProvider;
use CreateWebhookCallsTable;
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
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        config(['bigbluebutton-webhooks.signing_secret' => 'test_signing_secret']);
    }

    protected function setUpDatabase()
    {
        include_once __DIR__.'/../vendor/spatie/laravel-webhook-client/database/migrations/create_webhook_calls_table.php.stub';

        (new CreateWebhookCallsTable())->up();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            BigBlueButtonWebhooksServiceProvider::class,
        ];
    }

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

    protected function determineBigBlueButtonSignature(array $payload, string $configKey = null): string
    {
        $secret = ($configKey) ?
            config("bigbluebutton-webhooks.signing_secret_{$configKey}") :
            config('bigbluebutton-webhooks.signing_secret');

        return $secret;
    }
}
