<?php

namespace App\Request;

class SuiteRequest
{
    /**
     * @param array<int, string> $tests
     */
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly string $sourceId,
        public readonly string $label,
        public readonly array $tests,
    ) {
    }
}
