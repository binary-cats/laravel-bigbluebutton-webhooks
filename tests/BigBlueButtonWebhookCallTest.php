<?php

namespace Tests;

use BinaryCats\BigBlueButtonWebhooks\ProcessBigBlueButtonWebhookJob;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Spatie\WebhookClient\Models\WebhookCall;

class BigBlueButtonWebhookCallTest extends TestCase
{
    /** @var \BinaryCats\BigBlueButtonWebhooks\ProcessBigBlueButtonWebhookJob */
    public $processBigBlueButtonWebhookJob;

    /** @var \Spatie\WebhookClient\Models\WebhookCall */
    public $webhookCall;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        config(['bigbluebutton-webhooks.jobs' => ['my_type' => DummyJob::class]]);

        $model = config('bigbluebutton-webhooks.model', WebhookCall::class);

        $this->webhookCall = $model::create([
            'name' => 'bigbluebutton',
            'payload' => [
                'event' => [
                    [
                        'data' => [
                            'type' => 'event',
                            'id' => 'my.type',
                            'attributes' => [
                            ],
                            'event' => [
                                'ts' => 1591652302962,
                            ],
                        ],
                    ],
                ],
                'timestamp' => '1591652302965',
                'domain' => 'example.com',
            ],
            'url' => '/webhooks/bigbluebutton',
        ]);

        $this->processBigblueButtonwEbhookJob = new ProcessBigBlueButtonWebhookJob($this->webhookCall);
    }

    #[Test]
    public function it_will_fire_off_the_configured_job(): void
    {
        $this->processBigblueButtonwEbhookJob->handle();

        $this->assertEquals($this->webhookCall->id, cache('dummyjob')->id);
    }

    #[Test]
    public function it_will_not_dispatch_a_job_for_another_type(): void
    {
        config(['bigbluebutton-webhooks.jobs' => ['another_type' => DummyJob::class]]);

        $this->processBigblueButtonwEbhookJob->handle();

        $this->assertNull(cache('dummyjob'));
    }

    #[Test]
    public function it_will_not_dispatch_jobs_when_no_jobs_are_configured(): void
    {
        config(['bigbluebutton-webhooks.jobs' => []]);

        $this->processBigblueButtonwEbhookJob->handle();

        $this->assertNull(cache('dummyjob'));
    }

    #[Test]
    public function it_will_dispatch_events_even_when_no_corresponding_job_is_configured(): void
    {
        config(['bigbluebutton-webhooks.jobs' => ['another_type' => DummyJob::class]]);

        $this->processBigblueButtonwEbhookJob->handle();

        $webhookCall = $this->webhookCall;

        Event::assertDispatched("bigbluebutton-webhooks::{$webhookCall->payload['event'][0]['data']['id']}", function ($event, $eventPayload) use ($webhookCall) {
            $this->assertInstanceOf(WebhookCall::class, $eventPayload);
            $this->assertEquals($webhookCall->id, $eventPayload->id);

            return true;
        });

        $this->assertNull(cache('dummyjob'));
    }
}
