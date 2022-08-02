<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\FormulaireProfil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class ProfilController extends AbstractController
{
    /**
     * @Route("/profil", name="profil")
     */
    public function index(Request $requete, EntityManagerInterface $entityManager): Response
    {
		// On vérifie si l'utilisateur s'est bien connecté sur son compte
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
		// On récupère les informations utilisateur
		$utilisateur = $this->getUser();
		// On appelle le FormulaireProfil mettre en place le formulaire
		$formulaire = $this->createForm(FormulaireProfil::class,$utilisateur);
		// On récupère les informations du formulaire qui a été soumis par l'utilisateur
		$formulaire->handleRequest($requete);

        if ($formulaire->isSubmitted() && $formulaire->isValid())
		{
			// Si on a soumis un avatar - on met donc a jour le fichier et la base
			$fichier_recu = $formulaire->get('avatar')->getData();
			if ($fichier_recu)
			{
				$nom_fichier_origine = pathinfo($fichier_recu->getClientOriginalName(), PATHINFO_FILENAME);
				// On prepare un nouveau nom avec la fonction uniqid qui permet de generer un identifiant unique basé sur la date et l'heure courante en microsecondes
				$nom_fichier = uniqid().'.'.$fichier_recu->guessExtension();

				// On deplace le fichier recu dans le repertoire defini dans services.yaml
				try
				{
						$fichier_recu->move($this->getParameter('avatars_directory'),$nom_fichier);
				} 
				catch (FileException $erreur) 
				{
						// echec lors du rangement de l'avatar dans le dossier avatar_directory
						// afficher un message d'erreur
				}
				
				// On a réussi à déplacer le fichier, on supprime donc l'ancien avatar
				// Attention au cas où l'utilisateur n'avait pas d'avatar
				if ($utilisateur->getAvatar() != "")
				{
					$systemeFichiers = new Filesystem();
					$systemeFichiers->remove([$this->getParameter('avatars_directory').'/'.$utilisateur->getAvatar()]);
				}
				
				// On met à jour l'avatar dans l'objet Utilisateur
				$utilisateur->setAvatar($nom_fichier);
			}

			// Si on a mis à jour le pseudo
			if (strcmp($utilisateur->getPseudo(),$formulaire->get('pseudo')->getData()) != 0)
			{
				// On met a jour le pseudo
				$utilisateur->setPseudo($formulaire->get('pseudo')->getData());
			}

			// On lance la mise à jour en base
			$entityManager->persist($utilisateur);
            $entityManager->flush();
        }
        return $this->render('profil/index.html.twig', [
			'formulaire' => $formulaire->createView(),
			'avatars_directory' => $this->getParameter('avatars_directory')
        ]);
    }
}