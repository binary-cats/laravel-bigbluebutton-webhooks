<?php

namespace BinaryCats\BigBlueButtonWebhooks;

use BinaryCats\BigBlueButtonWebhooks\Exceptions\WebhookFailed;
use Illuminate\Support\Arr;
use Spatie\WebhookClient\ProcessWebhookJob;

class ProcessBigBlueButtonWebhookJob extends ProcessWebhookJob
{
    /**
     * Name of the payload key to contain the type of event.
     *
     * @var string
     */
    protected $key = 'event.0.data.id';

    /**
     * Handle the process.
     *
     * @return void
     */
    public function handle()
    {
        $type = Arr::get($this->webhookCall, "payload.{$this->key}");

        if (! $type) {
            throw WebhookFailed::missingType($this->webhookCall);
        }

        event("bigbluebutton-webhooks::{$type}", $this->webhookCall);

        $jobClass = $this->determineJobClass($type);

        if ($jobClass === '') {
            return;
        }

        if (! class_exists($jobClass)) {
            throw WebhookFailed::jobClassDoesNotExist($jobClass, $this->webhookCall);
        }

        dispatch(new $jobClass($this->webhookCall));
    }

    protected function determineJobClass(string $eventType): string
    {
        $jobConfigKey = str_replace('.', '_', $eventType);

        return config("bigbluebutton-webhooks.jobs.{$jobConfigKey}", '');
    }
}
