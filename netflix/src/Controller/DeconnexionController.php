<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DeconnexionController extends AbstractController
{
    /**
     * @Route("/deconnexion", name="deconnexion")
     */
    public function index(): void
    {
                // Normalement, on ne met rien ici mais on laisse une exception au cas où
                throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }
}