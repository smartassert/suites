<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Model\EntityId;
use App\Tests\DataProvider\UnauthorizedUserDataProviderTrait;

abstract class AbstractListSuitesTest extends AbstractApplicationTest
{
    use UnauthorizedUserDataProviderTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testListSuitesForUnauthorizedUser(?string $token): void
    {
        $this->doUnauthorizedUserTest(function () use ($token) {
            return $this->applicationClient->makeListRequest($token);
        });
    }

    /**
     * @dataProvider listBadMethodDataProvider
     */
    public function testListBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeListRequest(
            self::$authenticationConfiguration->getValidApiToken(),
            $method
        );

        self::assertSame(405, $response->getStatusCode());
        self::assertSame('', $response->getBody()->getContents());
    }

    /**
     * @return array<mixed>
     */
    public function listBadMethodDataProvider(): array
    {
        return [
            'POST' => [
                'method' => 'POST',
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
     * @dataProvider listSuccessDataProvider
     *
     * @param array<mixed> $expectedSuiteLabels
     */
    public function testListSuccess(callable $suiteCollectionCreator, array $expectedSuiteLabels): void
    {
        $suiteCollectionCreator(self::$authenticationConfiguration->getUser()->id);

        $response = $this->applicationClient->makeListRequest(self::$authenticationConfiguration->getValidApiToken());

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        self::assertIsArray($responseData);

        self::assertCount(count($expectedSuiteLabels), $responseData);

        foreach ($responseData as $suiteIndex => $suiteData) {
            self::assertIsArray($suiteData);
            self::assertSame($expectedSuiteLabels[$suiteIndex], $suiteData['label']);
        }
    }

    /**
     * @return array<mixed>
     */
    public function listSuccessDataProvider(): array
    {
        return [
            'no suites' => [
                'suiteCollectionCreator' => function () {
                },
                'expectedSuiteLabels' => [],
            ],
            'no matching suites' => [
                'suiteCollectionCreator' => function () {
                    $this->createSuite(EntityId::create(), EntityId::create(), 'label 1');
                    $this->createSuite(EntityId::create(), EntityId::create(), 'label 2');
                    $this->createSuite(EntityId::create(), EntityId::create(), 'label 3');
                },
                'expectedSuiteLabels' => [],
            ],
            'some matching suites' => [
                'suiteCollectionCreator' => function (string $userId) {
                    $this->createSuite($userId, EntityId::create(), 'label 1');
                    $this->createSuite(EntityId::create(), EntityId::create(), 'label 2');
                    $this->createSuite($userId, EntityId::create(), 'label 3');
                },
                'expectedSuiteLabels' => [
                    'label 1',
                    'label 3',
                ],
            ],
        ];
    }
}
