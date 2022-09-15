<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Entity\Suite;
use App\Model\EntityId;
use App\Repository\SuiteRepository;
use webignition\ObjectReflector\ObjectReflector;

abstract class AbstractInvalidSuiteUserTest extends AbstractApplicationTest
{
    public const FILENAME = 'filename.yaml';

    private string $suiteId;

    protected function setUp(): void
    {
        parent::setUp();

        $suite = new Suite(EntityId::create(), EntityId::create(), 'label');

        $repository = self::getContainer()->get(SuiteRepository::class);
        \assert($repository instanceof SuiteRepository);

        $repository->add($suite);

        $suiteId = ObjectReflector::getProperty($suite, 'id');
        $this->suiteId = is_string($suiteId) ? $suiteId : '';
    }

    public function testUpdateSuiteForInvalidUser(): void
    {
        $response = $this->applicationClient->makeUpdateRequest(
            self::$authenticationConfiguration->getValidApiToken(),
            $this->suiteId,
            []
        );

        $this->responseAsserter->assertForbiddenResponse($response);
    }
}
