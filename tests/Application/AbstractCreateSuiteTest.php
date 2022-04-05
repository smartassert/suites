<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Entity\Suite;
use App\Repository\SuiteRepository;
use App\Request\SuiteRequest;
use Symfony\Component\Uid\Ulid;
use webignition\ObjectReflector\ObjectReflector;

abstract class AbstractCreateSuiteTest extends AbstractApplicationTest
{
    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testCreateBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeCreateRequest([], $method);

        self::assertSame(405, $response->getStatusCode());
        self::assertSame('', $response->getBody()->getContents());
    }

    /**
     * @return array<mixed>
     */
    public function createBadMethodDataProvider(): array
    {
        return [
            'GET' => [
                'method' => 'GET',
            ],
            'HEAD' => [
                'method' => 'HEAD',
            ],
            'PUT' => [
                'method' => 'PUT',
            ],
            'DELETE' => [
                'method' => 'DELETE',
            ],
        ];
    }

    /**
     * @dataProvider createBadRequestDataProvider
     *
     * @param array<string, string> $payload
     * @param array<string, string> $expectedResponseData
     */
    public function testCreateBadRequest(array $payload, array $expectedResponseData): void
    {
        $response = $this->applicationClient->makeCreateRequest($payload);

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);

        self::assertSame($expectedResponseData, $responseData);
    }

    /**
     * @return array<mixed>
     */
    public function createBadRequestDataProvider(): array
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

    /**
     * @dataProvider createSuccessDataProvider
     *
     * @param array<string, string> $payload
     * @param array<mixed>          $expectedResponseData
     */
    public function testCreateSuccess(array $payload, array $expectedResponseData): void
    {
        $suiteRepository = self::getContainer()->get(SuiteRepository::class);
        \assert($suiteRepository instanceof SuiteRepository);

        self::assertSame(0, $suiteRepository->count([]));

        $response = $this->applicationClient->makeCreateRequest($payload);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);

        self::assertSame(1, $suiteRepository->count([]));

        $suite = $suiteRepository->findAll()[0];
        self::assertInstanceOf(Suite::class, $suite);

        $expectedResponseData['id'] = ObjectReflector::getProperty($suite, 'id');
        self::assertSame($expectedResponseData, $responseData);

        self::assertSame($expectedResponseData['source_id'], ObjectReflector::getProperty($suite, 'sourceId'));
        self::assertSame($expectedResponseData['label'], ObjectReflector::getProperty($suite, 'label'));
        self::assertSame($expectedResponseData['tests'] ?? null, ObjectReflector::getProperty($suite, 'tests'));
    }

    /**
     * @return array<mixed>
     */
    public function createSuccessDataProvider(): array
    {
        $validSourceId = Ulid::generate();

        return [
            'no tests (not present)' => [
                'payload' => [
                    SuiteRequest::KEY_SOURCE_ID => $validSourceId,
                    SuiteRequest::KEY_LABEL => 'non-empty value',
                ],
                'expectedResponseData' => [
                    'id' => '#as-generated',
                    'source_id' => $validSourceId,
                    'label' => 'non-empty value',
                ],
            ],
            'no tests (not empty)' => [
                'payload' => [
                    SuiteRequest::KEY_SOURCE_ID => $validSourceId,
                    SuiteRequest::KEY_LABEL => 'non-empty value',
                    SuiteRequest::KEY_TESTS => [],
                ],
                'expectedResponseData' => [
                    'id' => '#as-generated',
                    'source_id' => $validSourceId,
                    'label' => 'non-empty value',
                ],
            ],
            'has tests' => [
                'payload' => [
                    SuiteRequest::KEY_SOURCE_ID => $validSourceId,
                    SuiteRequest::KEY_LABEL => 'non-empty value',
                    SuiteRequest::KEY_TESTS => [
                        'Test/test1.yaml',
                        'Test/test2.yml',
                    ],
                ],
                'expectedResponseData' => [
                    'id' => '#as-generated',
                    'source_id' => $validSourceId,
                    'label' => 'non-empty value',
                    'tests' => [
                        'Test/test1.yaml',
                        'Test/test2.yml',
                    ],
                ],
            ],
        ];
    }
}
