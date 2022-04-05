<?php

declare(strict_types=1);

namespace App\Tests\Functional\ArgumentResolver;

use App\ArgumentResolver\SuiteResolver;
use App\Controller\SuiteRoutes;
use App\Entity\Suite;
use App\Exception\SuiteNotFoundException;
use App\Model\EntityId;
use App\Repository\SuiteRepository;
use App\Tests\Services\EntityRemover;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\TraceableValueResolver;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use webignition\ObjectReflector\ObjectReflector;

class SuiteResolverTest extends WebTestCase
{
    private SuiteResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $resolver = self::getContainer()->get(SuiteResolver::class);
        \assert($resolver instanceof TraceableValueResolver);

        $resolver = ObjectReflector::getProperty($resolver, 'inner');
        \assert($resolver instanceof SuiteResolver);

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
                        ->andReturn(Suite::class)
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
     * @dataProvider resolveThrowsSuiteNotFoundExceptionDataProvider
     */
    public function testResolveThrowsSuiteNotFoundException(Request $request, string $expectedExceptionSuiteId): void
    {
        $argumentMetadata = \Mockery::mock(ArgumentMetadata::class);
        $argumentMetadata
            ->shouldReceive('getType')
            ->andReturn(Suite::class)
        ;

        $generator = $this->resolver->resolve($request, $argumentMetadata);

        try {
            iterator_to_array($generator);
            self::fail(SuiteNotFoundException::class . ' not thrown');
        } catch (SuiteNotFoundException $exception) {
            self::assertSame($expectedExceptionSuiteId, $exception->getSuiteId());
        }
    }

    /**
     * @return array<mixed>
     */
    public function resolveThrowsSuiteNotFoundExceptionDataProvider(): array
    {
        return [
            'empty request' => [
                'request' => new Request(),
                'expectedExceptionSuiteId' => '',
            ],
            'suiteId present but empty' => [
                'request' => new Request([], [], [SuiteRoutes::ROUTE_SUITE_ID_ATTRIBUTE => '']),
                'expectedExceptionSuiteId' => '',
            ],
            'suiteId present' => [
                'request' => new Request([], [], [SuiteRoutes::ROUTE_SUITE_ID_ATTRIBUTE => 'non-empty value']),
                'expectedExceptionSuiteId' => 'non-empty value',
            ],
        ];
    }

    public function testResolveSuccess(): void
    {
        $entityRemover = self::getContainer()->get(EntityRemover::class);
        if ($entityRemover instanceof EntityRemover) {
            $entityRemover->removeAll();
        }

        $suiteId = EntityId::create();

        $suite = new Suite(EntityId::create(), EntityId::create(), 'label value');
        ObjectReflector::setProperty($suite, Suite::class, 'id', $suiteId);

        $repository = self::getContainer()->get(SuiteRepository::class);
        \assert($repository instanceof SuiteRepository);
        $repository->add($suite);

        $argumentMetadata = \Mockery::mock(ArgumentMetadata::class);
        $argumentMetadata
            ->shouldReceive('getType')
            ->andReturn(Suite::class)
        ;

        $request = new Request([], [], [SuiteRoutes::ROUTE_SUITE_ID_ATTRIBUTE => $suiteId]);

        $generator = $this->resolver->resolve($request, $argumentMetadata);
        $items = iterator_to_array($generator);

        self::assertCount(1, $items);

        $yieldedSuite = $items[0];
        self::assertInstanceOf(Suite::class, $yieldedSuite);
        self::assertSame($suite, $yieldedSuite);
    }
}
