<?php

namespace App\Request;

class SuiteRequest
{
    public const KEY_ID = 'id';
    public const KEY_SOURCE_ID = 'source_id';
    public const KEY_LABEL = 'label';
    public const KEY_TESTS = 'tests';

    /**
     * @param array<int, string> $tests
     */
    public function __construct(
        public readonly ?string $id,
        public readonly string $userId,
        public readonly ?string $sourceId,
        public readonly ?string $label,
        public readonly ?array $tests,
    ) {
    }
}
