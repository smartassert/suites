<?php

namespace App\Controller;

use App\Entity\Suite;
use App\Model\EntityId;
use App\Repository\SuiteRepository;
use App\Request\SuiteRequest;
use App\Response\ErrorResponse;
use App\Security\UserSuiteAccessChecker;
use SmartAssert\YamlFile\Filename;
use SmartAssert\YamlFile\Validator\YamlFilenameValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Ulid;

class SuiteController extends AbstractController
{
    public function __construct(
        private YamlFilenameValidator $yamlFilenameValidator,
        private SuiteRepository $repository,
        private UserSuiteAccessChecker $userSuiteAccessChecker,
    ) {
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(UserInterface $user, SuiteRequest $request): JsonResponse
    {
        $suite = new Suite($user->getUserIdentifier(), EntityId::create(), '');

        return $this->setSuite($suite, $request);
    }

    #[Route(SuiteRoutes::ROUTE_SUITE, name: 'update', methods: ['PUT'])]
    public function update(Suite $suite, SuiteRequest $request): JsonResponse
    {
        $this->userSuiteAccessChecker->denyAccessUnlessGranted($suite);

        return $this->setSuite($suite, $request);
    }

    #[Route(SuiteRoutes::ROUTE_SUITE, name: 'delete', methods: ['DELETE'])]
    public function delete(Suite $suite): Response
    {
        $this->userSuiteAccessChecker->denyAccessUnlessGranted($suite);

        $this->repository->remove($suite);

        return new Response(null, 200);
    }

    private function setSuite(Suite $suite, SuiteRequest $request): JsonResponse
    {
        $sourceId = $request->sourceId;
        if (null === $sourceId) {
            return new ErrorResponse('source_id/missing');
        }

        if (false === Ulid::isValid($sourceId)) {
            return new ErrorResponse('source_id/invalid');
        }

        $label = $request->label;
        if (null === $label) {
            return new ErrorResponse('label/missing');
        }

        $requestTests = $request->tests;
        $tests = [];

        if (is_array($requestTests)) {
            $invalidTests = [];

            foreach ($requestTests as $requestTest) {
                $requestTest = trim($requestTest);

                $validation = $this->yamlFilenameValidator->validate(Filename::parse($requestTest));
                if ($validation->isValid()) {
                    $tests[] = $requestTest;
                } else {
                    $invalidTests[] = $requestTest;
                }
            }

            if ([] !== $invalidTests) {
                return new ErrorResponse('tests/invalid', [
                    'invalid_paths' => $invalidTests,
                ]);
            }
        }

        $suite->setSourceId($sourceId);
        $suite->setLabel($label);
        $suite->setTests($tests);

        $this->repository->add($suite);

        return new JsonResponse($suite);
    }
}
