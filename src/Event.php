<?php

namespace BinaryCats\BigBlueButtonWebhooks;

use BinaryCats\BigBlueButtonWebhooks\Contracts\WebhookEvent;

class Event implements WebhookEvent
{
    /**
     * Attributes from the event.
     *
     * @var array
     */
    public $attributes = [];

    /**
     * Create new Event.
     *
     * @param array $attributes
     */
    public function __construct($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Construct the event.
     *
     * @return Event
     */
    public static function constructFrom($data): self
    {
        return new static($data);
    }
}
