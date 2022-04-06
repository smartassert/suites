<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Entity\Suite;
use App\Model\EntityId;
use App\Repository\SuiteRepository;
use App\Request\SuiteRequest;
use webignition\ObjectReflector\ObjectReflector;

abstract class AbstractUpdateSuiteTest extends AbstractApplicationTest
{
    use CreateUpdateBadRequestDataProviderTrait;

    private SuiteRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $repository = self::getContainer()->get(SuiteRepository::class);
        \assert($repository instanceof SuiteRepository);
        $this->repository = $repository;
    }

    /**
     * @dataProvider updateBadMethodDataProvider
     */
    public function testUpdateBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeUpdateRequest(
            $this->authenticationConfiguration->validToken,
            EntityId::create(),
            [],
            $method
        );

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
        ];
    }

    public function testUpdateSuiteNotFound(): void
    {
        $response = $this->applicationClient->makeUpdateRequest(
            $this->authenticationConfiguration->validToken,
            EntityId::create(),
            []
        );

        self::assertSame(404, $response->getStatusCode());
    }

    /**
     * @dataProvider createUpdateBadRequestDataProvider
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

        $response = $this->applicationClient->makeUpdateRequest(
            $this->authenticationConfiguration->validToken,
            $suiteId,
            $payload
        );

        $this->responseAsserter->assertBadRequestResponse($response, $expectedResponseData);
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

        $createResponse = $this->applicationClient->makeCreateRequest(
            $this->authenticationConfiguration->validToken,
            $createPayload
        );

        self::assertSame(200, $createResponse->getStatusCode());
        self::assertSame(1, $this->repository->count([]));

        $createResponseData = json_decode($createResponse->getBody()->getContents(), true);
        self::assertIsArray($createResponseData);

        $updateResponse = $this->applicationClient->makeUpdateRequest(
            $this->authenticationConfiguration->validToken,
            $createResponseData['id'],
            $updatePayload
        );

        $this->responseAsserter->assertSerializedSuiteResponse($updateResponse, $expectedResponseData);
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
