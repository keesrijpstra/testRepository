<?php

namespace App\Controller;

use App\Entity\Board;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\BoardRepository;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostController extends AbstractController
{
    #[Route('/post', name: 'app_show_all')]
    public function showAll(PostRepository $posts): Response
    {
        $allPosts = $posts->findAllWithComments();

        return $this->render('Post/posts.html.twig', [
            'posts' => $allPosts,
        ]);
    }

    #[Route('/post/top-liked', name: 'app_show_top-liked')]
    public function topLiked(PostRepository $posts): Response
    {
        $allPosts = $posts->findAllWithMinLikes(2);

        return $this->render('Post/top_liked.html.twig', [
            'posts' => $allPosts,
        ]);
    }

    #[Route('/post/follows', name: 'app_show_post_follows')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function follows(PostRepository $posts): Response
    {
        /** @var $currentUser User */
        $currentUser = $this->getUser();

        return $this->render('Post/follows.html.twig', [
            'posts' => $posts->findAllByAuthors($currentUser->getFollows()),
        ]);
    }

    #[Route('/post/boards', name: 'app_show_post_boards')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function boards(BoardRepository $repository): Response
    {
        $boards = $repository->findAll();

        return $this->render('Post/boards.html.twig', [
            'posts' => $boards,
        ]);
    }

    #[Route('/post/{board}/boards', name: 'app_show_posts_by_board')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getPostsByBoard(Board $board): Response
    {
        $posts = $board->getPosts();
        $boardName = $board->getName();

        return $this->render('Post/boards_with_their_posts.html.twig', [
            'posts' => $posts,
            'boardName' => $boardName,
        ]);
    }

    #[Route('/post/{post}', name: 'app_show_one')]
    #[IsGranted(Post::VIEW, 'post')]
    public function showOne(Post $post): Response
    {
        return $this->render('Post/index.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/post/add', name: 'app_show_add', priority: 2)]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function add(Request $request, PostRepository $repository, SluggerInterface $slugger): Response
    {
        $newPost = new Post();
        $form = $this->createForm(PostType::class, $newPost);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $postImage */
            $postImage = $form->get('postImage')->getData();
            $postData = $form->getData();
            $selectedBoard = $form->get('board')->getData();
            $newPost->setAuthor($this->getUser());
            $newPost->setCreationDate(new \DateTime());
            $newPost->setBoard($selectedBoard);
            if ($postImage) {
                $originalFileName = pathinfo(
                    $postImage->getClientOriginalName(),
                    PATHINFO_FILENAME
                );
                $safeFilename = $slugger->slug($originalFileName);
                $newFileName = $safeFilename.'-'.uniqid().'.'.$postImage->guessExtension();

                try {
                    $postImage->move(
                        $this->getParameter('post_directory'),
                        $newFileName
                    );
                } catch (FileException $e) {
                }

                $newPost->setImage($newFileName);

                $repository->save($newPost, true);
                $this->addFlash('success', 'Your Post image was updated.');
            }

            $repository->save($newPost, true);

            $this->addFlash('success', 'Your post has been added!');

            return $this->redirectToRoute('app_show_all');
        }

        return $this->renderForm(
            'Post/add.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/post/{post}/edit', name: 'app_show_edit', priority: 2)]
    #[IsGranted(Post::EDIT, 'post')]
    public function editPost(Post $post, Request $request, PostRepository $repository, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $postImage */
            $postImage = $form->get('postImage')->getData();
            $postData = $form->getData();
            if ($postImage) {
                /** @var Post $postData */
                $oldImage = $postData->getImage();
                $originalFileName = pathinfo(
                    $postImage->getClientOriginalName(),
                    PATHINFO_FILENAME
                );
                $safeFilename = $slugger->slug($originalFileName);
                $newFileName = $safeFilename.'-'.uniqid().'.'.$postImage->guessExtension();

                try {
                    $postImage->move(
                        $this->getParameter('post_directory'),
                        $newFileName
                    );
                } catch (FileException $e) {
                }

                if ($oldImage) {
                    $oldImagePath = $this->getParameter('post_directory').'/'.$oldImage;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $postData->setImage($newFileName);

                $repository->save($postData, true);
                $this->addFlash('success', 'Your Post image was updated.');
            }

            $repository->save($postData, true);

            $this->addFlash('success', 'Your post has been updated!');

            return $this->redirectToRoute('app_show_all');
        }

        return $this->renderForm(
            'Post/edit.html.twig', [
            'form' => $form,
            'post' => $post,
        ]);
    }

    #[Route('/post/{post}/comment', name: 'app_add_comment')]
    public function addComment(
        Post $post,
        SluggerInterface $slugger,
        Request $request,
        CommentRepository $commentRepository
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $poep = new Comment();
        $form = $this->createForm(CommentType::class, $poep);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $commentImage */
            $commentImage = $form->get('commentImage')->getData();
            $comment = $form->getData();
            $poep->setPost($post);
            $poep->setAuthor($this->getUser());
            $poep->setCreatedAt(new \DateTime());
            if ($commentImage) {
                $originalFileName = pathinfo(
                    $commentImage->getClientOriginalName(),
                    PATHINFO_FILENAME
                );
                $safeFilename = $slugger->slug($originalFileName);
                $newFileName = $safeFilename.'-'.uniqid().'.'.$commentImage->guessExtension();

                try {
                    $commentImage->move(
                        $this->getParameter('comment_directory'),
                        $newFileName
                    );
                } catch (FileException $e) {
                }

                $comment->setImage($newFileName);

                $commentRepository->save($comment, true);
                $this->addFlash('success', 'Your Comment image was updated.');
            }

            $commentRepository->save($comment, true);

            $this->addFlash('success', 'Your comment has been updated!');

            return $this->redirectToRoute(
                'app_show_one',
                ['post' => $post->getId()]);
        }

        return $this->renderForm(
            'Post/comment.html.twig', [
            'form' => $form,
            'post' => $post,
        ]);
    }

    #[Route('/comment/{comment}/edit', name: 'app_comment_edit', priority: 2)]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function editComment(
        Comment $comment,
        Request $request,
        CommentRepository $repository,
        SluggerInterface $slugger
    ): Response {
        $post = $comment->getPost();
        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $commentImage */
            $commentImage = $form->get('commentImage')->getData();
            $commentData = $form->getData();

            // Remove the current comment image if it exists
            $currentImage = $comment->getImage();
            if ($currentImage) {
                $currentImagePath = $this->getParameter('comment_directory').'/'.$currentImage;
                if (file_exists($currentImagePath)) {
                    unlink($currentImagePath);
                }
            }

            if ($commentImage) {
                $originalFileName = pathinfo(
                    $commentImage->getClientOriginalName(),
                    PATHINFO_FILENAME
                );
                $safeFilename = $slugger->slug($originalFileName);
                $newFileName = $safeFilename.'-'.uniqid().'.'.$commentImage->guessExtension();

                try {
                    $commentImage->move(
                        $this->getParameter('comment_directory'),
                        $newFileName
                    );
                } catch (FileException $e) {
                }

                $comment->setImage($newFileName);
                $repository->save($comment, true);
                $this->addFlash('success', 'Your Comment image was updated.');
            }

            $repository->save($commentData, true);
            $this->addFlash('success', 'Your comment has been updated!');

            return $this->redirectToRoute('app_show_one', ['post' => $post->getId()]);
        }

        return $this->renderForm('Post/comment_edit.html.twig', [
            'form' => $form,
            'post' => $post,
            'comment' => $comment,
        ]);
    }
}
