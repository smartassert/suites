<?php

namespace App\Request;

class CreateRequest
{
    public const KEY_SOURCE_ID = 'source_id';
    public const KEY_LABEL = 'label';
    public const KEY_TESTS = 'tests';

    /**
     * @param array<int, string> $tests
     */
    public function __construct(
        public readonly ?string $sourceId,
        public readonly ?string $label,
        public readonly ?array $tests,
    ) {
    }
}
