<?php

declare(strict_types=1);

namespace App\Tests\Functional\ArgumentResolver;

use App\ArgumentResolver\CreateRequestResolver;
use App\Request\CreateRequest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class CreateRequestResolverTest extends WebTestCase
{
    private ArgumentValueResolverInterface $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $resolver = self::getContainer()->get(CreateRequestResolver::class);
        \assert($resolver instanceof ArgumentValueResolverInterface);

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
                        ->andReturn(CreateRequest::class)
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
    public function testResolve(Request $request, CreateRequest $expected): void
    {
        $argumentMetadata = \Mockery::mock(ArgumentMetadata::class);
        $argumentMetadata
            ->shouldReceive('getType')
            ->andReturn(CreateRequest::class)
        ;

        $generator = $this->resolver->resolve($request, $argumentMetadata);

        $items = [];
        if ($generator instanceof \Traversable) {
            $items = iterator_to_array($generator);
        }

        self::assertIsArray($items);
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
                'expected' => new CreateRequest('', '', null),
            ],
            'all values present but empty' => [
                'request' => new Request([], [
                    CreateRequest::KEY_SOURCE_ID => '',
                    CreateRequest::KEY_LABEL => '',
                    CreateRequest::KEY_TESTS => [],
                ]),
                'expected' => new CreateRequest('', '', null),
            ],
            'source_id and label are ignored if not strings' => [
                'request' => new Request([], [
                    CreateRequest::KEY_SOURCE_ID => 100,
                    CreateRequest::KEY_LABEL => 200,
                    CreateRequest::KEY_TESTS => [],
                ]),
                'expected' => new CreateRequest('', '', null),
            ],
            'source_id and label are trimmed' => [
                'request' => new Request([], [
                    CreateRequest::KEY_SOURCE_ID => '  trimmed  ',
                    CreateRequest::KEY_LABEL => ' also trimmed      ',
                    CreateRequest::KEY_TESTS => [],
                ]),
                'expected' => new CreateRequest('trimmed', 'also trimmed', null),
            ],
            'tests items are ignored if not strings' => [
                'request' => new Request([], [
                    CreateRequest::KEY_SOURCE_ID => 'source_id',
                    CreateRequest::KEY_LABEL => 'label',
                    CreateRequest::KEY_TESTS => [
                        100,
                        'Tests/test1.yaml',
                        true,
                        null,
                        'Tests/test2.yaml',
                        3.14,
                    ],
                ]),
                'expected' => new CreateRequest('source_id', 'label', ['Tests/test1.yaml', 'Tests/test2.yaml']),
            ],
        ];
    }
}
