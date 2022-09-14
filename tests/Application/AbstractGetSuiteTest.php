<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Model\EntityId;
use App\Request\SuiteRequest;
use Symfony\Component\Uid\Ulid;

abstract class AbstractGetSuiteTest extends AbstractApplicationTest
{
    /**
     * @var array<mixed>
     */
    private array $suiteData;
    private string $suiteId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->suiteData = $this->createSuite();
        $suiteId = $this->suiteData['id'] ?? null;
        \assert(is_string($suiteId));
        $this->suiteId = $suiteId;
    }

    /**
     * @dataProvider getBadMethodDataProvider
     */
    public function testGetBadMethod(string $method): void
    {
        $response = $this->applicationClient->makeGetRequest(
            self::$authenticationConfiguration->getValidApiToken(),
            $this->suiteId,
            $method
        );

        self::assertSame(405, $response->getStatusCode());
        self::assertSame('', $response->getBody()->getContents());
    }

    /**
     * @return array<mixed>
     */
    public function getBadMethodDataProvider(): array
    {
        return [
            'POST' => [
                'method' => 'POST',
            ],
        ];
    }

    public function testGetSuiteNotFound(): void
    {
        $response = $this->applicationClient->makeGetRequest(
            self::$authenticationConfiguration->getValidApiToken(),
            EntityId::create()
        );

        self::assertSame(404, $response->getStatusCode());
    }

    public function testGetSuccess(): void
    {
        $response = $this->applicationClient->makeGetRequest(
            self::$authenticationConfiguration->getValidApiToken(),
            $this->suiteId
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        self::assertIsArray($responseData);

        self::assertSame($this->suiteData, $responseData);
    }

    /**
     * @return array<mixed>
     */
    private function createSuite(): array
    {
        $response = $this->applicationClient->makeCreateRequest(
            self::$authenticationConfiguration->getValidApiToken(),
            [
                SuiteRequest::KEY_SOURCE_ID => (string) new Ulid(),
                SuiteRequest::KEY_LABEL => 'non-empty value',
            ]
        );

        \assert(200 === $response->getStatusCode());
        \assert('application/json' === $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);
        \assert(is_array($responseData));

        return $responseData;
    }
}
