<?php

declare(strict_types=1);

namespace App\Tests\Services\ApplicationClient;

use Psr\Http\Message\ResponseInterface;
use SmartAssert\SymfonyTestClient\ClientInterface;
use Symfony\Component\Routing\RouterInterface;

class Client
{
    public function __construct(
        private ClientInterface $client,
        private RouterInterface $router,
    ) {
    }

    /**
     * @param array<mixed> $payload
     */
    public function makeCreateRequest(array $payload, string $method = 'POST'): ResponseInterface
    {
        return $this->client->makeRequest(
            $method,
            $this->router->generate('create'),
            [
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query($payload)
        );
    }

    /**
     * @param array<mixed> $payload
     */
    public function makeUpdateRequest(string $suiteId, array $payload, string $method = 'PUT'): ResponseInterface
    {
        return $this->client->makeRequest(
            $method,
            $this->router->generate('update', ['suiteId' => $suiteId]),
            [
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query($payload)
        );
    }

    public function makeDeleteRequest(string $suiteId, string $method = 'DELETE'): ResponseInterface
    {
        return $this->client->makeRequest($method, $this->router->generate('delete', ['suiteId' => $suiteId]));
    }
}
