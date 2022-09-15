<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Model\EntityId;
use App\Repository\SuiteRepository;
use App\Request\SuiteRequest;
use App\Tests\DataProvider\UnauthorisedUserDataProviderTrait;

abstract class AbstractDeleteSuiteTest extends AbstractApplicationTest
{
    use UnauthorisedUserDataProviderTrait;

    private SuiteRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $repository = self::getContainer()->get(SuiteRepository::class);
        \assert($repository instanceof SuiteRepository);
        $this->repository = $repository;
    }

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testDeleteForUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeDeleteRequest($token, EntityId::create());

        $this->responseAsserter->assertUnauthorizedResponse($response);
    }

    /**
     * @dataProvider deleteBadMethodDataProvider
     */
    public function testDeleteBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeDeleteRequest(
            self::$authenticationConfiguration->getValidApiToken(),
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
            'POST' => [
                'method' => 'POST',
            ],
        ];
    }

    public function testDeleteSuiteNotFound(): void
    {
        $response = $this->applicationClient->makeDeleteRequest(
            self::$authenticationConfiguration->getValidApiToken(),
            EntityId::create()
        );

        self::assertSame(200, $response->getStatusCode());
    }

    public function testDeleteSuiteIsFound(): void
    {
        self::assertSame(0, $this->repository->count([]));

        $createResponse = $this->applicationClient->makeCreateRequest(
            self::$authenticationConfiguration->getValidApiToken(),
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
            self::$authenticationConfiguration->getValidApiToken(),
            $createResponseData['id']
        );

        self::assertSame(200, $deleteResponse->getStatusCode());
        self::assertSame('', $deleteResponse->getBody()->getContents());
    }
}
