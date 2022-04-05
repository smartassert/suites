<?php

namespace App\Controller;

use App\Entity\Suite;
use App\Model\EntityId;
use App\Repository\SuiteRepository;
use App\Request\CreateRequest;
use App\Response\ErrorResponse;
use SmartAssert\YamlFile\Filename;
use SmartAssert\YamlFile\Validator\YamlFilenameValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Ulid;

class SuiteController extends AbstractController
{
    #[Route('/', name: 'create', methods: ['POST'])]
    public function index(
        CreateRequest $request,
        YamlFilenameValidator $yamlFilenameValidator,
        SuiteRepository $repository
    ): JsonResponse {
        // @todo: replace with injected user's id in #10
        $userId = EntityId::create();

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

                $validation = $yamlFilenameValidator->validate(Filename::parse($requestTest));
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

        $suite = new Suite($userId, $sourceId, $label, $tests);

        $repository->add($suite);

        return new JsonResponse($suite);
    }
}
