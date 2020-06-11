<?php

namespace BinaryCats\BigBlueButtonWebhooks;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Spatie\WebhookClient\Models\WebhookCall as Model;
use Spatie\WebhookClient\WebhookConfig;

class WebhookCall extends Model
{
    public static function storeWebhook(WebhookConfig $config, Request $request): Model
    {
        // payload is not proper JSON, rather is it split between three blocks
        $payload = $request->input();
        // transform event
        if ($event = Arr::get($payload, 'event', null) and is_string($event)) {
            $payload['event'] = json_decode($event, true);
        }
        // create
        return self::create([
            'name' => $config->name,
            'payload' => $payload,
        ]);
    }
}
