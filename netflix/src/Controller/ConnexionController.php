<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConnexionController extends AbstractController
{
    /**
     * @Route("/connexion", name="connexion")
     */
    public function index(AuthenticationUtils $authentification): Response
    {
                // Gestion d'erreurs lors d'authentification
                $erreur = $authentification->getLastAuthenticationError();

                // adresse mail rentré par l'utilisateur :
                $adresse_mail = $authentification->getLastUsername();

        return $this->render('connexion/index.html.twig', [
            'adresse_mail' => $adresse_mail,
                        'erreur' => $erreur
        ]);
    }
}