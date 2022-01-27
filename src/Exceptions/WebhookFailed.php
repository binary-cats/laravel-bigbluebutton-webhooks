<?php

namespace BinaryCats\BigBlueButtonWebhooks\Exceptions;

use Exception;
use Spatie\WebhookClient\Models\WebhookCall;

final class WebhookFailed extends Exception
{
    /**
     * @return static
     */
    public static function signingSecretNotSet(): self
    {
        return new static('The webhook signing secret is not set. Make sure that the `signing_secret` config key is set to the correct value.');
    }

    /**
     * @param  string  $jobClass
     * @param  \Spatie\WebhookClient\Models\WebhookCall  $webhookCall
     * @return static
     */
    public static function jobClassDoesNotExist(string $jobClass, WebhookCall $webhookCall): self
    {
        return new static("Could not process webhook id `{$webhookCall->id}` of type `{$webhookCall->name} because the configured jobclass `$jobClass` does not exist.");
    }

    /**
     * @param  \Spatie\WebhookClient\Models\WebhookCall  $webhookCall
     * @return static
     */
    public static function missingType(WebhookCall $webhookCall): self
    {
        return new static("Webhook call id `{$webhookCall->id}` did not contain a type. Valid BigBlueButton webhook calls should always contain a type.");
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function render($request)
    {
        return response(['error' => $this->getMessage()], 400);
    }
}
