<?php

namespace BinaryCats\BigBlueButtonWebhooks;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookProcessor;
use Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile;

class BigBlueButtonWebhooksController
{
    /**
     * Invoke controller method.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  string|null $configKey
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, string $configKey = null)
    {
        $webhookConfig = new WebhookConfig([
            'name' => 'bigbluebutton',
            'signing_secret' => ($configKey) ?
                config('bigbluebutton-webhooks.signing_secret_'.$configKey) :
                config('bigbluebutton-webhooks.signing_secret'),
            'signature_header_name' => null,
            'signature_validator' => BigBlueButtonSignatureValidator::class,
            'webhook_profile' => ProcessEverythingWebhookProfile::class,
            'webhook_model' => config('bigbluebutton-webhooks.model'),
            'process_webhook_job' => config('bigbluebutton-webhooks.process_webhook_job'),
        ]);

        (new WebhookProcessor($request, $webhookConfig))->process();

        return response()->json(['message' => 'ok']);
    }
}
