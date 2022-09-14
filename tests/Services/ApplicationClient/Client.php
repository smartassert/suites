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
    public function makeCreateRequest(
        ?string $authenticationToken,
        array $payload,
        string $method = 'POST'
    ): ResponseInterface {
        return $this->client->makeRequest(
            $method,
            $this->router->generate('create'),
            array_merge(
                $this->createAuthorizationHeader($authenticationToken),
                [
                    'content-type' => 'application/x-www-form-urlencoded',
                ]
            ),
            http_build_query($payload)
        );
    }

    public function makeGetRequest(
        ?string $authenticationToken,
        string $suiteId,
        string $method = 'GET'
    ): ResponseInterface {
        return $this->client->makeRequest(
            $method,
            $this->router->generate('get', ['suiteId' => $suiteId]),
            $this->createAuthorizationHeader($authenticationToken)
        );
    }

    /**
     * @param array<mixed> $payload
     */
    public function makeUpdateRequest(
        ?string $authenticationToken,
        string $suiteId,
        array $payload,
        string $method = 'PUT'
    ): ResponseInterface {
        return $this->client->makeRequest(
            $method,
            $this->router->generate('update', ['suiteId' => $suiteId]),
            array_merge(
                $this->createAuthorizationHeader($authenticationToken),
                [
                    'content-type' => 'application/x-www-form-urlencoded',
                ]
            ),
            http_build_query($payload)
        );
    }

    public function makeDeleteRequest(
        ?string $authenticationToken,
        string $suiteId,
        string $method = 'DELETE'
    ): ResponseInterface {
        return $this->client->makeRequest(
            $method,
            $this->router->generate('delete', ['suiteId' => $suiteId]),
            $this->createAuthorizationHeader($authenticationToken)
        );
    }

    public function makeHealthCheckRequest(string $method = 'GET'): ResponseInterface
    {
        return $this->client->makeRequest($method, $this->router->generate('health-check'));
    }

    public function makeStatusRequest(): ResponseInterface
    {
        return $this->client->makeRequest('GET', $this->router->generate('status'));
    }

    /**
     * @return array<string, string>
     */
    private function createAuthorizationHeader(?string $authenticationToken): array
    {
        $headers = [];
        if (is_string($authenticationToken)) {
            $headers = [
                'authorization' => 'Bearer ' . $authenticationToken,
            ];
        }

        return $headers;
    }
}
