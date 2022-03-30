<?php

declare(strict_types=1);

namespace App\Tests\Functional\Entity;

use App\Entity\Suite;
use App\Model\EntityId;
use App\Repository\SuiteRepository;
use App\Tests\Services\EntityRemover;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use webignition\ObjectReflector\ObjectReflector;

class SuiteTest extends WebTestCase
{
    private SuiteRepository $repository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $repository = self::getContainer()->get(SuiteRepository::class);
        \assert($repository instanceof SuiteRepository);
        $this->repository = $repository;

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        \assert($entityManager instanceof EntityManagerInterface);
        $this->entityManager = $entityManager;

        $entityRemover = self::getContainer()->get(EntityRemover::class);
        if ($entityRemover instanceof EntityRemover) {
            $entityRemover->removeAll();
        }
    }

    /**
     * @dataProvider createDataProvider
     *
     * @param null|array<int, string> $tests
     */
    public function testCreate(string $userId, string $sourceId, string $label, ?array $tests): void
    {
        self::assertSame(0, $this->repository->count([]));

        $suite = new Suite($userId, $sourceId, $label, $tests);

        $this->entityManager->persist($suite);
        $this->entityManager->flush();

        self::assertSame(1, $this->repository->count([]));

        self::assertSame($userId, ObjectReflector::getProperty($suite, 'userId'));
        self::assertSame($sourceId, ObjectReflector::getProperty($suite, 'sourceId'));
        self::assertSame($label, ObjectReflector::getProperty($suite, 'label'));
        self::assertSame($tests, ObjectReflector::getProperty($suite, 'tests'));
    }

    /**
     * @return array<mixed>
     */
    public function createDataProvider(): array
    {
        $userIdNoTests = EntityId::create();
        $sourceIdNoTests = EntityId::create();
        $labelNoTests = 'suite no tests';
        $userIdHasTests = EntityId::create();
        $sourceIdHasTests = EntityId::create();
        $labelHasTests = 'suite has tests';

        return [
            'no tests' => [
                'userId' => $userIdNoTests,
                'sourceId' => $sourceIdNoTests,
                'label' => $labelNoTests,
                'tests' => null,
            ],
            'has tests' => [
                'userId' => $userIdHasTests,
                'sourceId' => $sourceIdHasTests,
                'label' => $labelHasTests,
                'tests' => [
                    'Test/test1.yaml',
                    'Test/test2.yaml',
                    'Test/test3.yaml',
                ],
            ],
        ];
    }
}
