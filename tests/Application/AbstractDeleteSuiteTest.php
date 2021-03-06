<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Model\EntityId;
use App\Repository\SuiteRepository;
use App\Request\SuiteRequest;

abstract class AbstractDeleteSuiteTest extends AbstractApplicationTest
{
    private SuiteRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $repository = self::getContainer()->get(SuiteRepository::class);
        \assert($repository instanceof SuiteRepository);
        $this->repository = $repository;
    }

    /**
     * @dataProvider deleteBadMethodDataProvider
     */
    public function testDeleteBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeDeleteRequest(
            $this->authenticationConfiguration->validToken,
            EntityId::create(),
            $method
        );

        self::assertSame(405, $response->getStatusCode());
        self::assertSame('', $response->getBody()->getContents());
    }

    /**
     * @return array<mixed>
     */
    public function deleteBadMethodDataProvider(): array
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

    public function testDeleteSuiteNotFound(): void
    {
        $response = $this->applicationClient->makeDeleteRequest(
            $this->authenticationConfiguration->validToken,
            EntityId::create()
        );

        self::assertSame(200, $response->getStatusCode());
    }

    public function testDeleteSuiteIsFound(): void
    {
        self::assertSame(0, $this->repository->count([]));

        $createResponse = $this->applicationClient->makeCreateRequest(
            $this->authenticationConfiguration->validToken,
            [
                SuiteRequest::KEY_SOURCE_ID => EntityId::create(),
                SuiteRequest::KEY_LABEL => 'label',
            ]
        );

        self::assertSame(200, $createResponse->getStatusCode());
        self::assertSame(1, $this->repository->count([]));

        $createResponseData = json_decode($createResponse->getBody()->getContents(), true);
        self::assertIsArray($createResponseData);

        $deleteResponse = $this->applicationClient->makeDeleteRequest(
            $this->authenticationConfiguration->validToken,
            $createResponseData['id']
        );

        self::assertSame(200, $deleteResponse->getStatusCode());
        self::assertSame('', $deleteResponse->getBody()->getContents());
    }
}
