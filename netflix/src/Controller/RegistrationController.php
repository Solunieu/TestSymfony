<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/inscription", name="inscription")
     */
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new Utilisateur();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
			// Verifier si le mot de passe est identique
			if (strcmp($form->get('verification')->getData(), $form->get('plainPassword')->getData()) == 0)
			{
				// encode the plain password
				$user->setPassword(
				$userPasswordHasher->hashPassword(
						$user,
						$form->get('plainPassword')->getData()
					)
				);

				$entityManager->persist($user);
				$entityManager->flush();

				return $this->redirectToRoute('connexion');
			}
			else
			{
				$form->addError(new FormError('Les mots de passes ne sont pas identiques !'));
			}
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
