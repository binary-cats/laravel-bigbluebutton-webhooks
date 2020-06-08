<?php

return [

    /*
     * BigBlueButton will sign each webhook using a shared secret.
     */
    'signing_secret' => env('BBB_SECRET'),

    /*
     * You can define the job that should be run when a certain webhook hits your application
     * here. The key is the name of the BigBlueButton event type with the `.` replaced by a `_`.
     */
    'jobs' => [
        // 'meeting-created' => \BinaryCats\BigBlueButtonWebhooks\Jobs\MeetingCreatedJob::class,
    ],

    /*
     * The classname of the model to be used. The class should equal or extend
     * Spatie\WebhookClient\Models\WebhookCall
     */
    'model' => \BinaryCats\BigBlueButtonWebhooks\WebhookCall::class,

    /*
     * The classname of the model to be used. The class should equal or extend
     * Spatie\WebhookClient\ProcessWebhookJob
     */
    'process_webhook_job' => \BinaryCats\BigBlueButtonWebhooks\ProcessBigBlueButtonWebhookJob::class,
];
