<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Entity\Suite;
use App\Model\EntityId;
use App\Repository\SuiteRepository;
use App\Request\SuiteRequest;
use Symfony\Component\Uid\Ulid;
use webignition\ObjectReflector\ObjectReflector;

abstract class AbstractUpdateSuiteTest extends AbstractApplicationTest
{
//    private Suite $suite;
    private SuiteRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

//        $suite = new Suite(EntityId::create(), EntityId::create(), 'label');

        $repository = self::getContainer()->get(SuiteRepository::class);
        \assert($repository instanceof SuiteRepository);
        $this->repository = $repository;
//        $this->repository->add($this->suite);
//
//        self::assertSame($this->suite, $this->repository->find(ObjectReflector::getProperty($this->suite, 'id')));
    }

    /**
     * @dataProvider updateBadMethodDataProvider
     */
    public function testUpdateBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeUpdateRequest(EntityId::create(), [], $method);

        self::assertSame(405, $response->getStatusCode());
        self::assertSame('', $response->getBody()->getContents());
    }

    /**
     * @return array<mixed>
     */
    public function updateBadMethodDataProvider(): array
    {
        return [
            'GET' => [
                'method' => 'GET',
            ],
            'HEAD' => [
                'method' => 'HEAD',
            ],
            'POST' => [
                'method' => 'POST',
            ],
            'DELETE' => [
                'method' => 'DELETE',
            ],
        ];
    }

    public function testUpdateSuiteNotFound(): void
    {
        $response = $this->applicationClient->makeUpdateRequest(EntityId::create(), []);

        self::assertSame(404, $response->getStatusCode());
    }

    /**
     * @dataProvider createBadRequestDataProvider
     *
     * @param array<string, string> $payload
     * @param array<string, string> $expectedResponseData
     */
    public function testUpdateBadRequest(array $payload, array $expectedResponseData): void
    {
        $suite = new Suite(EntityId::create(), EntityId::create(), 'label');
        $this->repository->add($suite);

        $suiteId = ObjectReflector::getProperty($suite, 'id');
        $suiteId = is_string($suiteId) ? $suiteId : '';

        $response = $this->applicationClient->makeUpdateRequest($suiteId, $payload);

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
     * @dataProvider updateSuccessDataProvider
     *
     * @param array<string, string> $createPayload
     * @param array<string, string> $updatePayload
     * @param array<mixed>          $expectedResponseData
     */
    public function testUpdateSuccess(array $createPayload, array $updatePayload, array $expectedResponseData): void
    {
        self::assertSame(0, $this->repository->count([]));

        $createResponse = $this->applicationClient->makeCreateRequest($createPayload);
        self::assertSame(200, $createResponse->getStatusCode());
        self::assertSame(1, $this->repository->count([]));

        $createResponseData = json_decode($createResponse->getBody()->getContents(), true);
        self::assertIsArray($createResponseData);

        $updateResponse = $this->applicationClient->makeUpdateRequest($createResponseData['id'], $updatePayload);
        self::assertSame(200, $updateResponse->getStatusCode());
        self::assertSame('application/json', $updateResponse->getHeaderLine('content-type'));

        $updateResponseData = json_decode($updateResponse->getBody()->getContents(), true);
//        var_dump($expectedResponseData, $updateResponseData);
//        exit();

        self::assertSame(1, $this->repository->count([]));

        $suite = $this->repository->findAll()[0];
        self::assertInstanceOf(Suite::class, $suite);
//        var_dump($suite);
//        exit();

        $expectedResponseData['id'] = ObjectReflector::getProperty($suite, 'id');
        self::assertSame($expectedResponseData, $updateResponseData);

        self::assertSame($expectedResponseData['source_id'], ObjectReflector::getProperty($suite, 'sourceId'));
        self::assertSame($expectedResponseData['label'], ObjectReflector::getProperty($suite, 'label'));
        self::assertSame(($expectedResponseData['tests'] ?? null), ObjectReflector::getProperty($suite, 'tests'));

//        $suiteRepository = self::getContainer()->get(SuiteRepository::class);
//        \assert($suiteRepository instanceof SuiteRepository);
//
//        self::assertSame(0, $suiteRepository->count([]));
//
//        $response = $this->applicationClient->makeUpdateRequest(
//            ObjectReflector::getProperty($this->suite, 'id'),
//            $payload
//        );
//
//        self::assertSame(200, $response->getStatusCode());
//        self::assertSame(1, $suiteRepository->count([]));
//
//        $suite = $suiteRepository->findAll()[0];
//        self::assertInstanceOf(Suite::class, $suite);
//
//        $expectedResponseData['id'] = ObjectReflector::getProperty($suite, 'id');
//        self::assertSame($expectedResponseData, $responseData);
//
//        self::assertSame($expectedResponseData['source_id'], ObjectReflector::getProperty($suite, 'sourceId'));
//        self::assertSame($expectedResponseData['label'], ObjectReflector::getProperty($suite, 'label'));
//        self::assertSame($expectedResponseData['tests'] ?? null, ObjectReflector::getProperty($suite, 'tests'));
    }

    /**
     * @return array<mixed>
     */
    public function updateSuccessDataProvider(): array
    {
        $originalSourceId = EntityId::create();
        $updatedSourceId = EntityId::create();
        $originalLabel = 'original label';
        $updatedLabel = 'updated label';
        $originalTests = ['Test/test1.yaml', 'Test/test2.yml'];
        $updatedTests = ['Test/test3.yaml', 'Test/test4.yml'];

        return [
            'create with no tests (empty), update has no changes' => [
                'createPayload' => [
                    SuiteRequest::KEY_SOURCE_ID => $originalSourceId,
                    SuiteRequest::KEY_LABEL => $originalLabel,
                ],
                'updatePayload' => [
                    SuiteRequest::KEY_SOURCE_ID => $originalSourceId,
                    SuiteRequest::KEY_LABEL => $originalLabel,
                ],
                'expectedResponseData' => [
                    'id' => '#as-generated',
                    'source_id' => $originalSourceId,
                    'label' => $originalLabel,
                    'tests' => [],
                ],
            ],
            'create with no tests (not empty), update has no changes' => [
                'createPayload' => [
                    SuiteRequest::KEY_SOURCE_ID => $originalSourceId,
                    SuiteRequest::KEY_LABEL => $originalLabel,
                    SuiteRequest::KEY_TESTS => [],
                ],
                'updatePayload' => [
                    SuiteRequest::KEY_SOURCE_ID => $originalSourceId,
                    SuiteRequest::KEY_LABEL => $originalLabel,
                    SuiteRequest::KEY_TESTS => [],
                ],
                'expectedResponseData' => [
                    'id' => '#as-generated',
                    'source_id' => $originalSourceId,
                    'label' => $originalLabel,
                    'tests' => [],
                ],
            ],
            'create with tests, remove tests' => [
                'createPayload' => [
                    SuiteRequest::KEY_SOURCE_ID => $originalSourceId,
                    SuiteRequest::KEY_LABEL => $originalLabel,
                    SuiteRequest::KEY_TESTS => $originalTests,
                ],
                'updatePayload' => [
                    SuiteRequest::KEY_SOURCE_ID => $originalSourceId,
                    SuiteRequest::KEY_LABEL => $originalLabel,
                    SuiteRequest::KEY_TESTS => [],
                ],
                'expectedResponseData' => [
                    'id' => '#as-generated',
                    'source_id' => $originalSourceId,
                    'label' => $originalLabel,
                    'tests' => [],
                ],
            ],
            'update all properties' => [
                'createPayload' => [
                    SuiteRequest::KEY_SOURCE_ID => $originalSourceId,
                    SuiteRequest::KEY_LABEL => $originalLabel,
                    SuiteRequest::KEY_TESTS => $originalTests,
                ],
                'updatePayload' => [
                    SuiteRequest::KEY_SOURCE_ID => $updatedSourceId,
                    SuiteRequest::KEY_LABEL => $updatedLabel,
                    SuiteRequest::KEY_TESTS => $updatedTests,
                ],
                'expectedResponseData' => [
                    'id' => '#as-generated',
                    'source_id' => $updatedSourceId,
                    'label' => $updatedLabel,
                    'tests' => $updatedTests,
                ],
            ],
        ];
    }
}
