<?php

declare(strict_types=1);

namespace App\Tests\Functional\ArgumentResolver;

use App\ArgumentResolver\SuiteRequestResolver;
use App\Request\SuiteRequest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\TraceableValueResolver;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use webignition\ObjectReflector\ObjectReflector;

class SuiteRequestResolverTest extends WebTestCase
{
    private SuiteRequestResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $resolver = self::getContainer()->get(SuiteRequestResolver::class);
        \assert($resolver instanceof TraceableValueResolver);

        $resolver = ObjectReflector::getProperty($resolver, 'inner');
        \assert($resolver instanceof SuiteRequestResolver);

        $this->resolver = $resolver;
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(ArgumentMetadata $argumentMetadata, bool $expected): void
    {
        self::assertSame($expected, $this->resolver->supports(new Request(), $argumentMetadata));
    }

    /**
     * @return array<mixed>
     */
    public function supportsDataProvider(): array
    {
        return [
            'supports' => [
                'argumentMetadata' => (function () {
                    $argumentMetadata = \Mockery::mock(ArgumentMetadata::class);
                    $argumentMetadata
                        ->shouldReceive('getType')
                        ->andReturn(SuiteRequest::class)
                    ;

                    return $argumentMetadata;
                })(),
                'expected' => true,
            ],
            'does not support' => [
                'argumentMetadata' => (function () {
                    $argumentMetadata = \Mockery::mock(ArgumentMetadata::class);
                    $argumentMetadata
                        ->shouldReceive('getType')
                        ->andReturn(ArgumentMetadata::class)
                    ;

                    return $argumentMetadata;
                })(),
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider resolveDataProvider
     */
    public function testResolve(Request $request, SuiteRequest $expected): void
    {
        $argumentMetadata = \Mockery::mock(ArgumentMetadata::class);
        $argumentMetadata
            ->shouldReceive('getType')
            ->andReturn(SuiteRequest::class)
        ;

        $generator = $this->resolver->resolve($request, $argumentMetadata);
        $items = iterator_to_array($generator);

        self::assertCount(1, $items);
        self::assertEquals($expected, $items[0]);
    }

    /**
     * @return array<mixed>
     */
    public function resolveDataProvider(): array
    {
        return [
            'empty' => [
                'request' => new Request(),
                'expected' => new SuiteRequest('', '', null),
            ],
            'all values present but empty' => [
                'request' => new Request([], [
                    SuiteRequest::KEY_SOURCE_ID => '',
                    SuiteRequest::KEY_LABEL => '',
                    SuiteRequest::KEY_TESTS => [],
                ]),
                'expected' => new SuiteRequest('', '', null),
            ],
            'source_id and label are ignored if not strings' => [
                'request' => new Request([], [
                    SuiteRequest::KEY_SOURCE_ID => 100,
                    SuiteRequest::KEY_LABEL => 200,
                    SuiteRequest::KEY_TESTS => [],
                ]),
                'expected' => new SuiteRequest('', '', null),
            ],
            'source_id and label are trimmed' => [
                'request' => new Request([], [
                    SuiteRequest::KEY_SOURCE_ID => '  trimmed  ',
                    SuiteRequest::KEY_LABEL => ' also trimmed      ',
                    SuiteRequest::KEY_TESTS => [],
                ]),
                'expected' => new SuiteRequest('trimmed', 'also trimmed', null),
            ],
            'tests items are ignored if not strings' => [
                'request' => new Request([], [
                    SuiteRequest::KEY_SOURCE_ID => 'source_id',
                    SuiteRequest::KEY_LABEL => 'label',
                    SuiteRequest::KEY_TESTS => [
                        100,
                        'Tests/test1.yaml',
                        true,
                        null,
                        'Tests/test2.yaml',
                        3.14,
                    ],
                ]),
                'expected' => new SuiteRequest('source_id', 'label', ['Tests/test1.yaml', 'Tests/test2.yaml']),
            ],
        ];
    }
}
