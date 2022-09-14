<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Suite;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserSuiteAccessChecker
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    /**
     * @throws AccessDeniedException
     */
    public function denyAccessUnlessGranted(Suite $suite): void
    {
        $attribute = SuiteAccessVoter::ACCESS;

        if (false === $this->authorizationChecker->isGranted($attribute, $suite)) {
            $exception = new AccessDeniedException();
            $exception->setAttributes($attribute);
            $exception->setSubject($suite);

            throw $exception;
        }
    }
}
