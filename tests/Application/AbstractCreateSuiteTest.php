<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Entity\Suite;
use App\Repository\SuiteRepository;
use App\Request\SuiteRequest;
use App\Tests\DataProvider\UnauthorizedUserDataProviderTrait;
use Symfony\Component\Uid\Ulid;

abstract class AbstractCreateSuiteTest extends AbstractApplicationTest
{
    use CreateUpdateBadRequestDataProviderTrait;
    use UnauthorizedUserDataProviderTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testCreateForUnauthorizedUser(?string $token): void
    {
        $this->doUnauthorizedUserTest(function () use ($token) {
            return $this->applicationClient->makeCreateRequest($token, []);
        });
    }

    /**
     * @dataProvider createBadMethodDataProvider
     */
    public function testCreateBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeCreateRequest(
            self::$authenticationConfiguration->getValidApiToken(),
            [],
            $method
        );

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
     * @dataProvider createUpdateBadRequestDataProvider
     *
     * @param array<string, string> $payload
     * @param array<string, string> $expectedResponseData
     */
    public function testCreateBadRequest(array $payload, array $expectedResponseData): void
    {
        $response = $this->applicationClient->makeCreateRequest(
            self::$authenticationConfiguration->getValidApiToken(),
            $payload
        );

        $this->responseAsserter->assertBadRequestResponse($response, $expectedResponseData);
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

        $response = $this->applicationClient->makeCreateRequest(
            self::$authenticationConfiguration->getValidApiToken(),
            $payload
        );

        self::assertSame(1, $suiteRepository->count([]));

        $suite = $suiteRepository->findAll()[0];
        self::assertInstanceOf(Suite::class, $suite);
        self::assertSame(self::$authenticationConfiguration->getUser()->id, $suite->getUserId());

        $this->responseAsserter->assertSerializedSuiteResponse($response, $expectedResponseData);
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
                    'tests' => [],
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
                    'tests' => [],
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
