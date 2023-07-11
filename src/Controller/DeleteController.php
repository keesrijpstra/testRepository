<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeleteController extends AbstractController
{
    #[Route('/post/delete/{id}', name: 'app_delete', priority: 2)]
    public function deletePost(Post $post, PostRepository $repository): Response
    {
        $repository->remove($post, true);

        $this->addFlash('success', 'Your Post has been deleted successfully!');

        return $this->redirectToRoute('app_show_all');
    }

    #[Route('/comment/delete/{id}', name: 'app_delete_comment', priority: 2)]
    public function deleteComment(Comment $comment, CommentRepository $repository): Response
    {
        $repository->remove($comment, true);

        $this->addFlash('success', 'Your comment has been deleted successfully!');

        return $this->redirectToRoute('app_show_all');
    }
}
