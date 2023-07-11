<?php

namespace App\Security\Voter;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class PostVoter extends Voter
{
    public function __construct(
        private readonly Security $security
    ) {

    }


    protected function supports(string $attribute, mixed $subject): bool
    {

        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [Post::EDIT, Post::VIEW])
            && $subject instanceof Post;
    }

    /** @param Post $subject */

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
//        if (!$user instanceof UserInterface) {
//            return false;
//        }
        $isAuth = $user instanceof UserInterface;


        if($this->security->isGranted('ROLE_ADMIN'))
        {
            return true;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case Post::EDIT:
                return $isAuth
                    && ($subject->getAuthor()->getId() === $user->getId() ||
                        $this->security->isGranted('ROLE_EDITOR')
                    );
            case Post::VIEW:
                return true;
        }
        return false;
    }
}
