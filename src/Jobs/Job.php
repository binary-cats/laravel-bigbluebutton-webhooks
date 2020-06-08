<?php

namespace BinaryCats\BigBlueButtonWebhooks\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Spatie\WebhookClient\Models\WebhookCall;

abstract class Job
{
    use Dispatchable, SerializesModels;

    /**
     * Bind the implementation.
     *
     * @var Spatie\WebhookClient\Models\WebhookCall
     */
    protected $webhookCall;

    /**
     * Location of the root.
     *
     * @var string
     */
    protected $root = 'event.0.data';

    /**
     * Create new Job.
     *
     * @param Spatie\WebhookClient\Models\WebhookCall $webhookCall
     */
    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }

    /**
     * Fetch Payload.
     *
     * @return array
     */
    protected function payload(): array
    {
        return $this->webhookCall->payload;
    }

    /**
     * Get the value from the payload's event data.
     *
     * @param  string $key
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->payload(), "{$this->root}.{$key}", $default);
    }
}
