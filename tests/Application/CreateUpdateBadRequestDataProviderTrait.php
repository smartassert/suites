<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Request\SuiteRequest;
use Symfony\Component\Uid\Ulid;

trait CreateUpdateBadRequestDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public function createUpdateBadRequestDataProvider(): array
    {
        $validSourceId = Ulid::generate();
        $validLabel = 'valid label';
        $validTests = [
            'Test/test1.yaml',
            'Test/test2.yaml',
        ];

        return [
            'source_id missing (not present)' => [
                'payload' => [
                    SuiteRequest::KEY_LABEL => $validLabel,
                    SuiteRequest::KEY_TESTS => $validTests,
                ],
                'expectedResponseData' => [
                    'error_state' => 'source_id/missing',
                ],
            ],
            'source_id missing (empty)' => [
                'payload' => [
                    SuiteRequest::KEY_SOURCE_ID => '',
                    SuiteRequest::KEY_LABEL => $validLabel,
                    SuiteRequest::KEY_TESTS => $validTests,
                ],
                'expectedResponseData' => [
                    'error_state' => 'source_id/missing',
                ],
            ],
            'source_id invalid' => [
                'payload' => [
                    SuiteRequest::KEY_SOURCE_ID => 'not a ULID',
                    SuiteRequest::KEY_LABEL => $validLabel,
                    SuiteRequest::KEY_TESTS => $validTests,
                ],
                'expectedResponseData' => [
                    'error_state' => 'source_id/invalid',
                ],
            ],
            'label missing (not present)' => [
                'payload' => [
                    SuiteRequest::KEY_SOURCE_ID => $validSourceId,
                    SuiteRequest::KEY_TESTS => $validTests,
                ],
                'expectedResponseData' => [
                    'error_state' => 'label/missing',
                ],
            ],
            'label missing (empty)' => [
                'payload' => [
                    SuiteRequest::KEY_SOURCE_ID => $validSourceId,
                    SuiteRequest::KEY_LABEL => '',
                    SuiteRequest::KEY_TESTS => $validTests,
                ],
                'expectedResponseData' => [
                    'error_state' => 'label/missing',
                ],
            ],
            'test paths invalid' => [
                'payload' => [
                    SuiteRequest::KEY_SOURCE_ID => $validSourceId,
                    SuiteRequest::KEY_LABEL => $validLabel,
                    SuiteRequest::KEY_TESTS => [
                        'Valid/path.yaml',
                        'Invalid/path.txt',
                        'Invalid/path.js',
                        'Valid/path.yml',
                    ],
                ],
                'expectedResponseData' => [
                    'error_state' => 'tests/invalid',
                    'payload' => [
                        'invalid_paths' => [
                            'Invalid/path.txt',
                            'Invalid/path.js',
                        ],
                    ],
                ],
            ],
        ];
    }
}
