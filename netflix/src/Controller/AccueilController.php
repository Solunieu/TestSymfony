<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Favoris;
use App\Repository\FavorisRepository;
use App\Repository\FilmRepository;

class AccueilController extends AbstractController
{
    /**
     * @Route("/", name="accueil")
     */
    public function index(FilmRepository $filmRepository, FavorisRepository $favorisRepository, EntityManagerInterface $entityManager): Response
    {
    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
		$utilisateurId = $this->getUser()->getId();
		// Si on ajoute un film dans les favoris (en cliquant sur une image parmi les films disponibles)
		if (isset($_GET['ajouterFavori']) && is_numeric($_GET['ajouterFavori']))
		{
			// On crée un nouvel objet Favori
			$favori = new Favoris();
			// On lui attribue l'id Utilisateur qui est celui qui est actuellement connecté
			$favori->setIdUtilisateur($utilisateurId);
			// On lui dit l'id du film que l'utilisateur a cliqué
			$favori->setIdFilm($_GET['ajouterFavori']);
			// On met à jour la base de données
			$entityManager->persist($favori);
            $entityManager->flush();
		}
		// Si on supprime un film des favoris (en cliquant sur une image parmi les films favoris)
		if (isset($_GET['supprimerFavori']) && is_numeric($_GET['supprimerFavori']))
		{
			$favorisRepository->supprimer($_GET['supprimerFavori'],$utilisateurId);
		}
        return $this->render('accueil/index.html.twig', [
			'favoris' => $favorisRepository->getFavoris($utilisateurId),
			'films' => $filmRepository->findAll()
        ]);
    }
}
