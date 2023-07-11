<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FollowerController extends AbstractController
{
    #[Route('/follow/{id}', name: 'app_follow')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function follow(User $userToFollow, ManagerRegistry $doctrine, Request $request): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($userToFollow !== $currentUser->getId()) {
            $currentUser->follow($userToFollow);
            $doctrine->getManager()->flush();
        }

        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/unfollow/{id}', name: 'app_unfollow')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function unfollow(User $userToUnfollow, ManagerRegistry $doctrine, Request $request): Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($userToUnfollow->getId() !== $currentUser->getId()) {
            $currentUser->unfollow($userToUnfollow);
            $doctrine->getManager()->flush();
        }

        return $this->redirect($request->headers->get('referer'));

    }
}
