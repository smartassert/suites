<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\Request\SuiteRequest;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class SuiteRequestResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return SuiteRequest::class === $argument->getType();
    }

    /**
     * @return \Traversable<SuiteRequest>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): \Traversable
    {
        if ($this->supports($request, $argument)) {
            $requestPayload = $request->request;

            $sourceId = $this->getNonEmptyStringPropertyFromPayload($requestPayload, SuiteRequest::KEY_SOURCE_ID);
            $label = $this->getNonEmptyStringPropertyFromPayload($requestPayload, SuiteRequest::KEY_LABEL);

            $requestTests = $requestPayload->all(SuiteRequest::KEY_TESTS);
            $tests = null;
            foreach ($requestTests as $requestTest) {
                if (is_string($requestTest)) {
                    if (null === $tests) {
                        $tests = [];
                    }

                    $tests[] = $requestTest;
                }
            }

            yield new SuiteRequest($sourceId, $label, $tests);
        }
    }

    private function getNonEmptyStringPropertyFromPayload(ParameterBag $payload, string $key): ?string
    {
        if (false === $payload->has($key)) {
            return null;
        }

        $value = $payload->get($key);
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return '' === $value ? null : $value;
    }
}
