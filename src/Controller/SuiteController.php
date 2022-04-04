<?php

namespace App\Controller;

use App\Entity\Suite;
use App\Model\EntityId;
use App\Repository\SuiteRepository;
use App\Request\CreateRequest;
use App\Response\ErrorResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Ulid;

class SuiteController extends AbstractController
{
    #[Route('/', name: 'create', methods: ['POST'])]
    public function index(CreateRequest $request, SuiteRepository $repository): Response
    {
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
        $tests = null;

        if (is_array($requestTests)) {
            $tests = [];

            foreach ($requestTests as $requestTest) {
                if (is_string($requestTest) && '' !== trim($requestTest)) {
                    $tests[] = trim($requestTest);
                }
            }

            if ([] === $tests) {
                $tests = null;
            }
        }

        $suite = new Suite($userId, $sourceId, $label, $tests);

        $repository->add($suite);

        return new Response();
    }
}
