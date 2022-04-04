<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Request\CreateRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class CreateRequestResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return CreateRequest::class === $argument->getType();
    }

    /**
     * @return \Traversable<CreateRequest>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): \Traversable
    {
        if ($this->supports($request, $argument)) {
            $requestPayload = $request->request;

            $sourceId = $requestPayload->get(CreateRequest::KEY_SOURCE_ID);
            $sourceId = is_string($sourceId) ? $sourceId : null;

            $label = $requestPayload->get(CreateRequest::KEY_LABEL);
            $label = is_string($label) ? $label : null;

            $requestTests = $requestPayload->all(CreateRequest::KEY_TESTS);
            $tests = null;
            foreach ($requestTests as $requestTest) {
                if (is_string($requestTest)) {
                    if (null === $tests) {
                        $tests = [];
                    }

                    $tests[] = $requestTest;
                }
            }

            yield new CreateRequest($sourceId, $label, $tests);
        }
    }
}
