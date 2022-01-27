<?php

namespace BinaryCats\BigBlueButtonWebhooks;

use BinaryCats\BigBlueButtonWebhooks\Contracts\WebhookEvent;

final class Event implements WebhookEvent
{
    /**
     * Attributes from the event.
     *
     * @var string[]
     */
    public $attributes = [];

    /**
     * Create new Event.
     *
     * @param string[] $attributes
     */
    public function __construct($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @param mixed[] $data
     * @return static
     */
    public static function constructFrom(array $data): self
    {
        return new static($data);
    }
}
