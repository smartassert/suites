<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Model\EntityId;
use App\Tests\Services\AuthenticationConfiguration;

abstract class AbstractUnauthorizedUserTest extends AbstractApplicationTest
{
    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testCreateSuiteForUnauthorizedUser(callable $tokenCreator): void
    {
        $response = $this->applicationClient->makeCreateRequest(
            $tokenCreator(self::$authenticationConfiguration),
            []
        );

        $this->responseAsserter->assertUnauthorizedResponse($response);
    }

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testUpdateSuiteForUnauthorizedUser(callable $tokenCreator): void
    {
        $response = $this->applicationClient->makeUpdateRequest(
            $tokenCreator(self::$authenticationConfiguration),
            EntityId::create(),
            []
        );

        $this->responseAsserter->assertUnauthorizedResponse($response);
    }

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testDeleteSuiteForUnauthorizedUser(callable $tokenCreator): void
    {
        $response = $this->applicationClient->makeDeleteRequest(
            $tokenCreator(self::$authenticationConfiguration),
            EntityId::create()
        );

        $this->responseAsserter->assertUnauthorizedResponse($response);
    }

    /**
     * @return array<mixed>
     */
    public function unauthorizedUserDataProvider(): array
    {
        return [
            'no token' => [
                'tokenCreator' => function () {
                    return null;
                },
            ],
            'empty token' => [
                'tokenCreator' => function () {
                    return '';
                },
            ],
            'non-empty invalid token' => [
                'tokenCreator' => function (AuthenticationConfiguration $authenticationConfiguration) {
                    return $authenticationConfiguration->getInvalidApiToken();
                },
            ],
        ];
    }
}
