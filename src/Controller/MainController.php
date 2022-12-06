<?php

namespace App\Controller;

use http\Client\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
#[Route("/", name: "main_")]
class MainController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('main/home.html.twig');
    }
}
