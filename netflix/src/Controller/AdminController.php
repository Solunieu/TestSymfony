<?php

namespace App\Controller;

use App\Entity\Film;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\FormulaireFilm;
use App\Form\RegistrationFormType;
use App\Repository\FilmRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;
use App\Form\FormulaireProfil;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(Request $requete, EntityManagerInterface $entityManager, FilmRepository $filmRepository, UtilisateurRepository $utilisateurRepository): Response
    {
		// Vérifier si l'utilisateur a bien les droits ADMIN (protection contre le piratage)
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$formulaire = $this->createForm(FormulaireFilm::class);
		$formulaire->handleRequest($requete);
		
		// Si on ajoute un nouveau film
		if ($formulaire->isSubmitted() && $formulaire->isValid())
		{
			$fichier_recu = $formulaire->get('titre')->getData();
			$nom_fichier_origine = pathinfo($fichier_recu->getClientOriginalName(), PATHINFO_FILENAME);
			// On prepare un nouveau nom avec la fonction uniqid qui permet de generer un identifiant unique basé sur la date et l'heure courante en microsecondes
			$nom_fichier = uniqid().'.'.$fichier_recu->guessExtension();

			// On deplace le fichier recu dans le repertoire defini dans services.yaml
			try
			{
					$fichier_recu->move($this->getParameter('films_directory'),$nom_fichier);
			} 
			catch (FileException $erreur) 
			{
					// echec lors du rangement de l'avatar dans le dossier avatar_directory
					$formulaire->addError(new FormError('L\'affiche n\'a pas pu être enregistrée !'));
			}
			
			// On crée un objet film
			$film = new Film();
			
			// On enregistre le nom du fichier dans l'objet Film
			$film->setTitre($nom_fichier);

			// On enregistre le synopsis dans l'objet film
			$film->setSynopsis($formulaire->get('synopsis')->getData());
			
			// On enregistre l'acteur principal dans l'objet film (meme s'il n'est pas rentré par l'utilisateur)
			$film->setActeurPrincipal($formulaire->get('acteur_principal')->getData());

			// On lance la mise à jour en base
			$entityManager->persist($film);
            $entityManager->flush();
        }

		// Si on veut supprimer un film
		if (isset($_GET['supprimerFilm']) && is_numeric($_GET['supprimerFilm']))
		{
			// On supprime l'image d'affiche du film
			$systemeFichiers = new Filesystem();
			$systemeFichiers->remove([$this->getParameter('films_directory').'/'.$filmRepository->find($_GET['supprimerFilm'])->getTitre()]);
			
			// Ensuite on supprime le film
			$filmRepository->remove($filmRepository->find($_GET['supprimerFilm']));
		}

		// Si on veut supprimer un utilisateur
		if (isset($_GET['supprimerUtilisateur']) && is_numeric($_GET['supprimerUtilisateur']))
		{
			// On supprime l'avatar de l'utilisateur (si l'utilisateur en possède)
			if ($utilisateurRepository->find($_GET['supprimerUtilisateur'])->getAvatar() != "")
			{
				$systemeFichiers = new Filesystem();
				$systemeFichiers->remove([$this->getParameter('avatars_directory').'/'.$utilisateurRepository->find($_GET['supprimerUtilisateur'])->getAvatar()]);
			}
			// Ensuite on supprime le film
			$utilisateurRepository->remove($utilisateurRepository->find($_GET['supprimerUtilisateur']));
		}

		// Affichage de la page administration
        return $this->render('admin/index.html.twig', [
            'formulaire' => $formulaire->createView(),
			'films' => $filmRepository->findAll(),
			'utilisateurs' => $utilisateurRepository->findAll(),
        ]);
    }

	/**
	* @Route("/admin/modifierUtilisateur", name="admin_modifier_utilisateur")
	*/
	public function modifierUtilisateur(Request $requete, UtilisateurRepository $utilisateurRepository, EntityManagerInterface $entityManager): Response
	{
		// Vérifier si l'utilisateur a bien les droits ADMIN (protection contre le piratage)
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		if (isset($_GET['utilisateurId']) && is_numeric($_GET['utilisateurId']))
		{
			// On recupere les informations utilisateur
			$utilisateur = $utilisateurRepository->find($_GET['utilisateurId']);
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
							$formulaire->addError(new FormError('L\'enregistrement du nouveau avatar a échoué !'));
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

				// Si on a affecte le droit admin 
				if ($requete->request->get('roles') == "on")
				{
					$utilisateur->setRoles(["ROLE_ADMIN"]);
				}
				else
				{
					$utilisateur->setRoles([]);
				}

				// On lance la mise à jour en base
				$entityManager->persist($utilisateur);
				$entityManager->flush();
			}
		}
		else $this->redirectToRoute('connexion'); // Si l'id utilisateur n'a pas ete fourni (cas de tentative de piratage)
		// Affichage de la page modification Utilisateur
		return $this->render('admin/modifierUtilisateur.html.twig', [
			'formulaire' => $formulaire->createView(),
			'utilisateur' => $utilisateur,
        ]);
	}

	/**
	* @Route("/admin/modifierFilm", name="admin_modifier_film")
	*/
	public function modifierFilm(Request $requete, FilmRepository $filmRepository, EntityManagerInterface $entityManager): Response
	{
		// Vérifier si l'utilisateur a bien les droits ADMIN (protection contre le piratage)
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		if (isset($_GET['film']) && is_numeric($_GET['film']))
		{
			// On recupere les informations film
			$film = $filmRepository->find($_GET['film']);

			// On appelle le FormulaireFilm mettre en place le formulaire
			$formulaire = $this->createForm(FormulaireFilm::class,$film);
			
			// On récupère les informations du formulaire qui a été soumis par l'utilisateur
			$formulaire->handleRequest($requete);
			
			if ($formulaire->isSubmitted() && $formulaire->isValid())
			{
				$fichier_recu = $formulaire->get('titre')->getData();
				if ($fichier_recu != "")
				{
					$nom_fichier_origine = pathinfo($fichier_recu->getClientOriginalName(), PATHINFO_FILENAME);
					// On prepare un nouveau nom avec la fonction uniqid qui permet de generer un identifiant unique basé sur la date et l'heure courante en microsecondes
					$nom_fichier = uniqid().'.'.$fichier_recu->guessExtension();

					// On deplace le fichier recu dans le repertoire defini dans services.yaml
					try
					{
							$fichier_recu->move($this->getParameter('films_directory'),$nom_fichier);
					} 
					catch (FileException $erreur) 
					{
							// echec lors du rangement de l'affiche dans le dossier avatar_directory
							$formulaire->addError(new FormError('La nouvelle affiche n\'a pas pu être enregistrée !'));
					}
					
					// On enregistre le nom du fichier dans l'objet Film
					$film->setTitre($nom_fichier);
				}

				// On enregistre le synopsis dans l'objet film
				$film->setSynopsis($formulaire->get('synopsis')->getData());
				
				// On enregistre l'acteur principal dans l'objet film (meme s'il n'est pas rentré par l'utilisateur)
				$film->setActeurPrincipal($formulaire->get('acteur_principal')->getData());

				// On lance la mise à jour en base
				$entityManager->persist($film);
				$entityManager->flush();
			}
		}
		else $this->redirectToRoute('connexion'); // Si l'id utilisateur n'a pas ete fourni (cas de tentative de piratage)
		// Affichage de la page modification film
		return $this->render('admin/modifierFilm.html.twig', [
			'formulaire' => $formulaire->createView(),
			'film' => $film,
        ]);
	}
	
	
	/**
	* @Route("/admin/reinitialiserMDP", name="admin_reinitialiser_MDP")
	*/
	public function reinitialiserMDP(Request $requete, UtilisateurRepository $utilisateurRepository): Response
	{
		// Vérifier si l'utilisateur a bien les droits ADMIN (protection contre le piratage)
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		if (isset($_GET['utilisateurId']) && is_numeric($_GET['utilisateurId']))
		{
			// On recupere les informations utilisateur
			$utilisateur = $utilisateurRepository->find($_GET['utilisateurId']);
			// On appelle le FormulaireProfil mettre en place le formulaire
			$formulaire = $this->createForm(RegistrationFormType::class,$utilisateur);
			
			// On récupère les informations du formulaire qui a été soumis par l'utilisateur
			$formulaire->handleRequest($requete);
			
			if ($formulaire->isSubmitted() && $formulaire->isValid())
			{
				// Verifier si le mot de passe est identique
				if (strcmp($formulaire->get('verification')->getData(), $formulaire->get('plainPassword')->getData()) == 0)
				{
					// encode the plain password
					$utilisateur->setPassword(
					$userPasswordHasher->hashPassword(
							$utilisateur,
							$formulaire->get('plainPassword')->getData()
						)
					);

					$entityManager->persist($utilisateur);
					$entityManager->flush();

					return $this->redirectToRoute('admin');
				}
				else
				{
					$formulaire->addError(new FormError('Les mots de passes ne sont pas identiques !'));
				}
			}
		}
		else $this->redirectToRoute('connexion'); // Si l'id utilisateur n'a pas ete fourni (cas de tentative de piratage)
		// Affichage de la page modification Utilisateur
		return $this->render('admin/reinitialiserMDP.html.twig', [
			'formulaire' => $formulaire->createView(),
			'utilisateur' => $utilisateur,
        ]);
	}
}
