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
     * @var \Spatie\WebhookClient\Models\WebhookCall
     */
    protected WebhookCall $webhookCall;

    /**
     * Location of the root.
     *
     * @var string
     */
    protected string $root = 'event.0.data';

    /**
     * Create new Job.
     *
     * @param  \Spatie\WebhookClient\Models\WebhookCall  $webhookCall
     */
    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }

    /**
     * Fetch Payload.
     *
     * @return mixed[]
     */
    protected function payload(): array
    {
        return $this->webhookCall->payload;
    }

    /**
     * @param  string  $key
     * @param  mixed  $default
     * @return array|\ArrayAccess|mixed
     */
    public function get(string $key, $default = null)
    {
        return Arr::get($this->payload(), "{$this->root}.{$key}", $default);
    }
}
