<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter;

use App\Entity\Suite;
use App\Repository\SuiteRepository;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use webignition\ObjectReflector\ObjectReflector;

class ResponseAsserter
{
    public function __construct(
        private SuiteRepository $suiteRepository,
    ) {
    }

    /**
     * @param array<mixed> $expectedResponseData
     */
    public function assertBadRequestResponse(ResponseInterface $response, array $expectedResponseData): void
    {
        Assert::assertSame(400, $response->getStatusCode());
        Assert::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);

        Assert::assertSame($expectedResponseData, $responseData);
    }

    /**
     * @param array<mixed> $expectedResponseData
     */
    public function assertSerializedSuiteResponse(ResponseInterface $response, array $expectedResponseData): void
    {
        Assert::assertSame(200, $response->getStatusCode());
        Assert::assertSame('application/json', $response->getHeaderLine('content-type'));

        Assert::assertSame(1, $this->suiteRepository->count([]));
        $suite = $this->suiteRepository->findAll()[0];
        Assert::assertInstanceOf(Suite::class, $suite);

        $responseData = json_decode($response->getBody()->getContents(), true);
        $expectedResponseData['id'] = ObjectReflector::getProperty($suite, 'id');
        Assert::assertSame($expectedResponseData, $responseData);

        Assert::assertSame($expectedResponseData['source_id'], ObjectReflector::getProperty($suite, 'sourceId'));
        Assert::assertSame($expectedResponseData['label'], ObjectReflector::getProperty($suite, 'label'));
        Assert::assertSame($expectedResponseData['tests'] ?? null, ObjectReflector::getProperty($suite, 'tests'));
    }

    public function assertUnauthorizedResponse(ResponseInterface $response): void
    {
        Assert::assertSame(401, $response->getStatusCode());
        $response->getBody()->rewind();
        Assert::assertSame('', $response->getBody()->getContents());
    }

    public function assertForbiddenResponse(ResponseInterface $response): void
    {
        Assert::assertSame(403, $response->getStatusCode());
    }
}
