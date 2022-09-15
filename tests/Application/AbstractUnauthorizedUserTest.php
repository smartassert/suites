<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Model\EntityId;
use App\Tests\DataProvider\UnauthorisedUserDataProviderTrait;

abstract class AbstractUnauthorizedUserTest extends AbstractApplicationTest
{
    use UnauthorisedUserDataProviderTrait;

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testUpdateSuiteForUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeUpdateRequest($token, EntityId::create(), []);

        $this->responseAsserter->assertUnauthorizedResponse($response);
    }

    /**
     * @dataProvider unauthorizedUserDataProvider
     */
    public function testDeleteSuiteForUnauthorizedUser(?string $token): void
    {
        $response = $this->applicationClient->makeDeleteRequest($token, EntityId::create());

        $this->responseAsserter->assertUnauthorizedResponse($response);
    }
}
