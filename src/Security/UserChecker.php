<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    /**
     * @param User $user
     *
     * @return void
     */
    public function checkPreAuth(UserInterface $user)
    {
        if (null === $user->getBannedUntil()) {
            return;
        }

        $now = new \DateTime();

        if ($now < $user->getBannedUntil()) {
            throw new AccessDeniedException('The user is banned');
        }
    }

    /**
     * @param User $user
     *
     * @return void
     */
    public function checkPostAuth(UserInterface $user)
    {
    }
}
