<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Controller\SuiteRoutes;
use App\Entity\Suite;
use App\Exception\SuiteNotFoundException;
use App\Repository\SuiteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class SuiteResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private readonly SuiteRepository $repository,
    ) {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return Suite::class === $argument->getType();
    }

    /**
     * @return \Traversable<Suite>
     *
     * @throws SuiteNotFoundException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): \Traversable
    {
        if ($this->supports($request, $argument)) {
            $suiteId = $request->attributes->get(SuiteRoutes::ROUTE_SUITE_ID_ATTRIBUTE);
            $suiteId = is_string($suiteId) ? $suiteId : '';

            $suite = $this->repository->find($suiteId);
            if (null === $suite) {
                throw new SuiteNotFoundException($suiteId);
            }

            yield $suite;
        }
    }
}
