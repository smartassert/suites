<?php

declare(strict_types=1);

namespace App\Tests\Services\Asserter;

use App\Entity\Suite;
use App\Repository\SuiteRepository;
use PHPUnit\Framework\TestCase;
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
        TestCase::assertSame(400, $response->getStatusCode());
        TestCase::assertSame('application/json', $response->getHeaderLine('content-type'));

        $responseData = json_decode($response->getBody()->getContents(), true);

        TestCase::assertSame($expectedResponseData, $responseData);
    }

    /**
     * @param array<mixed> $expectedResponseData
     */
    public function assertSerializedSuiteResponse(ResponseInterface $response, array $expectedResponseData): void
    {
        TestCase::assertSame(200, $response->getStatusCode());
        TestCase::assertSame('application/json', $response->getHeaderLine('content-type'));

        TestCase::assertSame(1, $this->suiteRepository->count([]));
        $suite = $this->suiteRepository->findAll()[0];
        TestCase::assertInstanceOf(Suite::class, $suite);

        $responseData = json_decode($response->getBody()->getContents(), true);
        $expectedResponseData['id'] = ObjectReflector::getProperty($suite, 'id');
        TestCase::assertSame($expectedResponseData, $responseData);

        TestCase::assertSame($expectedResponseData['source_id'], ObjectReflector::getProperty($suite, 'sourceId'));
        TestCase::assertSame($expectedResponseData['label'], ObjectReflector::getProperty($suite, 'label'));
        TestCase::assertSame($expectedResponseData['tests'] ?? null, ObjectReflector::getProperty($suite, 'tests'));
    }
}
