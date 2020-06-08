<?php

namespace BinaryCats\BigBlueButtonWebhooks\Tests;

class PayloadDefinition
{
    /**
     * Given the compexity of the payload, let's put is all into the same method.
     */
    public static function getPayloadDefinition(): array
    {
        return [
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
        ];
    }
}
