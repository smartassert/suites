<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Suite;
use App\Model\EntityId;
use PHPUnit\Framework\TestCase;
use webignition\ObjectReflector\ObjectReflector;

class SuiteTest extends TestCase
{
    /**
     * @dataProvider jsonSerializeDataProvider
     *
     * @param array<mixed> $expected
     */
    public function testJsonSerialize(Suite $suite, array $expected): void
    {
        self::assertSame($expected, $suite->jsonSerialize());
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerializeDataProvider(): array
    {
        $userIdNoTests = EntityId::create();
        $sourceIdNoTests = EntityId::create();
        $labelNoTests = 'suite no tests';
        $userIdHasTests = EntityId::create();
        $sourceIdHasTests = EntityId::create();
        $labelHasTests = 'suite has tests';
        $tests = [
            'Test/test1.yaml',
            'Test/test2.yaml',
        ];

        $suiteNoTests = new Suite($userIdNoTests, $sourceIdNoTests, $labelNoTests);
        $suiteHasTests = new Suite($userIdHasTests, $sourceIdHasTests, $labelHasTests, $tests);

        return [
            'no tests' => [
                'suite' => $suiteNoTests,
                'expected' => [
                    'id' => ObjectReflector::getProperty($suiteNoTests, 'id'),
                    'source_id' => $sourceIdNoTests,
                    'label' => $labelNoTests,
                    'tests' => [],
                ],
            ],
            'has tests' => [
                'suite' => $suiteHasTests,
                'expected' => [
                    'id' => ObjectReflector::getProperty($suiteHasTests, 'id'),
                    'source_id' => $sourceIdHasTests,
                    'label' => $labelHasTests,
                    'tests' => $tests,
                ],
            ],
        ];
    }
}
