<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Tests\Services\ApplicationClient\Client;
use App\Tests\Services\ApplicationClient\ClientFactory;
use App\Tests\Services\Asserter\ResponseAsserter;
use App\Tests\Services\EntityRemover;
use SmartAssert\SymfonyTestClient\ClientInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractApplicationTest extends WebTestCase
{
    protected KernelBrowser $kernelBrowser;
    protected Client $applicationClient;
    protected ResponseAsserter $responseAsserter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kernelBrowser = self::createClient();

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
}
