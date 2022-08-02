<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'app_blog')]
    public function index(): Response
    {
        $fruits = ['banana','pomme','fraise','watermelon',];

        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'fruits' => $fruits,
        ]);
    }

    #[Route('/blog/{fruit}', name:'app_details')]
     public function details(string $fruit): Response
     {
         return $this->render('blog/details.html.twig', [
            'fruit' => $fruit
        ]);
     }
}