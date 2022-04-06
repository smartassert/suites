<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Suite;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class SuiteAccessVoter extends Voter
{
    public const ACCESS = 'access';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (self::ACCESS !== $attribute) {
            return false;
        }

        if (!$subject instanceof Suite) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        return $subject instanceof Suite && $subject->getUserId() === $user->getUserIdentifier();
    }
}
