<?php

namespace App\Services;

class AdvisoryResult
{
    public function __construct(
        public readonly string $topic,
        public readonly string $summary,
        /** @var array<int, array{label:string, value:mixed, reason:string}> */
        public readonly array $items = [],
        public readonly string $method = 'deterministic',
        public readonly float $confidence = 1.0,
    ) {}

    public function toArray(): array
    {
        return [
            'topic' => $this->topic, 'summary' => $this->summary,
            'items' => $this->items, 'method' => $this->method, 'confidence' => $this->confidence,
        ];
    }
}
