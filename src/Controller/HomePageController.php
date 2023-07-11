<?php

namespace App\Controller;

use App\Entity\Board;
use App\Repository\BoardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomePageController extends AbstractController
{
//    #[Route('/home/page', name: 'app_home_page')]
//    public function index(BoardRepository $repository): Response
//    {
//        $getAllBoards = $repository->findAll();
//        $boards = $getAllBoards;
//
//        return $this->render('home_page/boards.html.twig', [
//            'boards' => $boards,
//        ]);
//    }
//
//    #[Route('/board/{board}', name: 'app_show_board')]
//    public function showOne(Board $board): Response
//    {
//
//        return $this->render('home_page/board_show_one.html.twig', [
//            'boardPosts' => $board->getPosts(),
//        ]);
//    }
}
