<?php

namespace Tests;

class PayloadDefinition
{
    /**
     * @return array
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
