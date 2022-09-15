<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Entity\Suite;
use App\Model\EntityId;
use App\Repository\SuiteRepository;
use App\Tests\Services\ApplicationClient\Client;
use App\Tests\Services\ApplicationClient\ClientFactory;
use App\Tests\Services\Asserter\ResponseAsserter;
use App\Tests\Services\AuthenticationConfiguration;
use App\Tests\Services\EntityRemover;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\SymfonyTestClient\ClientInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use webignition\ObjectReflector\ObjectReflector;

abstract class AbstractApplicationTest extends WebTestCase
{
    protected static KernelBrowser $kernelBrowser;
    protected Client $applicationClient;
    protected ResponseAsserter $responseAsserter;
    protected static AuthenticationConfiguration $authenticationConfiguration;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$kernelBrowser = self::createClient();

        $authenticationConfiguration = self::getContainer()->get(AuthenticationConfiguration::class);
        \assert($authenticationConfiguration instanceof AuthenticationConfiguration);
        self::$authenticationConfiguration = $authenticationConfiguration;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $factory = self::getContainer()->get(ClientFactory::class);
        \assert($factory instanceof ClientFactory);

        $this->applicationClient = $factory->create($this->getClientAdapter());

        $entityRemover = self::getContainer()->get(EntityRemover::class);
        if ($entityRemover instanceof EntityRemover) {
            $entityRemover->removeAll();
        }

        $responseAsserter = self::getContainer()->get(ResponseAsserter::class);
        \assert($responseAsserter instanceof ResponseAsserter);
        $this->responseAsserter = $responseAsserter;
    }

    abstract protected function getClientAdapter(): ClientInterface;

    /**
     * @param callable(string $apiToken, string $suiteId): ResponseInterface $responseCreator
     */
    protected function doInvalidUserTest(callable $responseCreator): void
    {
        $suite = $this->createSuite(EntityId::create(), EntityId::create(), 'label');

        $suiteId = ObjectReflector::getProperty($suite, 'id');
        $suiteId = is_string($suiteId) ? $suiteId : '';

        $response = $responseCreator(self::$authenticationConfiguration->getValidApiToken(), $suiteId);

        self::assertSame(403, $response->getStatusCode());
    }

    protected function doUnauthorizedUserTest(callable $responseCreator): void
    {
        $response = $responseCreator();

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('', $response->getBody()->getContents());
    }

    protected function createSuite(string $userId, string $sourceId, string $label = 'label'): Suite
    {
        $suite = new Suite($userId, $sourceId, $label);

        $repository = self::getContainer()->get(SuiteRepository::class);
        \assert($repository instanceof SuiteRepository);

        $repository->add($suite);

        return $suite;
    }
}
