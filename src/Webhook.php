<?php

namespace BinaryCats\BigBlueButtonWebhooks;

use Illuminate\Http\Request;

class Webhook
{
    /**
     * Validate and raise an appropriate event.
     *
     * @param  array $payload
     * @param  string $signature
     * @param  string $secret
     * @return BinaryCats\BigBlueButtonWebhooks\Event
     */
    public static function constructEvent(array $payload, string $signature, string $secret): Event
    {
        // verify we are good, else throw an expection
        WebhookSignature::make($signature, $secret)->verify();
        // Make an event
        return Event::constructFrom($payload);
    }
}
