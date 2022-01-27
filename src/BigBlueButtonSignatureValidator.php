<?php

namespace BinaryCats\BigBlueButtonWebhooks;

use Exception;
use Illuminate\Http\Request;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class BigBlueButtonSignatureValidator implements SignatureValidator
{
    /**
     * True if the signature has been valiates.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Spatie\WebhookClient\WebhookConfig  $config
     * @return bool
     */
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        // idenfity signature
        $signature = $request->bearerToken();
        // "pretend" to fetch secret
        $secret = $config->signingSecret;
        // For the webhooks with a signature
        try {
            Webhook::constructEvent($request->all(), $signature, $secret);
        } catch (Exception $exception) {
            report($exception);

            return false;
        }
        // default
        return true;
    }
}
