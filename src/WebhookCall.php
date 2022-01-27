<?php

namespace BinaryCats\BigBlueButtonWebhooks;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Spatie\WebhookClient\Models\WebhookCall as Model;
use Spatie\WebhookClient\WebhookConfig;

class WebhookCall extends Model
{
    /**
     * @param  \Spatie\WebhookClient\WebhookConfig  $config
     * @param  \Illuminate\Http\Request  $request
     * @return \Spatie\WebhookClient\Models\WebhookCall
     */
    public static function storeWebhook(WebhookConfig $config, Request $request): Model
    {
        // bigblubutton payload is build in expectation of multiple events
        $payload = $request->input();
        // transform event
        if ($event = Arr::get($payload, 'event', null) and is_string($event)) {
            $payload['event'] = json_decode($event, true);
        }
        // take the headers form the top
        $headers = self::headersToStore($config, $request);
        // parse and return
        return self::create([
            'name' => $config->name,
            'url' => $request->fullUrl(),
            'headers' => $headers,
            'payload' => $payload,
        ]);
    }
}
