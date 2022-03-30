<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Repository\SuiteRepository;
use Doctrine\ORM\EntityManagerInterface;

class EntityRemover
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SuiteRepository $suiteRepository,
    ) {
    }

    public function removeAll(): void
    {
        foreach ($this->suiteRepository->findAll() as $entity) {
            $this->entityManager->remove($entity);
        }

        $this->entityManager->flush();
    }
}
