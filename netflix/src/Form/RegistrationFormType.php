<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Email;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Votre adresse mail'
                ],
                'constraints' => [
                    new Email([
                        'message' => 'Votre adresse mail n\'est pas valide !',
                    ]),
                ]
            ])
			// Conditions mot de passe valide :
			// Au moins 1 chiffre [ 0 1 2 3 4 5 6 7 8 9 ]
			// Au moins 1 lettre minuscule [ a b c ... x y z ]
			// Au moins 1 lettre majuscule [ A B C ... X Y Z ]
			// Au moins 1 caractère spécial [ ~ ! @ # $ % ^ & * ( ) - _ = + [ ] { } ; : , . < > / ? | ]
            ->add('plainPassword', PasswordType::class, [
				'label' => false,
				'mapped' => false,
				'attr' => [
					'autocomplete' => 'new-password',
					'placeholder' => 'Votre mot de passe'
				],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez rentrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 8,
                            'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères !',
                        'max' => 255,
                            'maxMessage' => 'Votre mot de passe ne doit pas dépasser {{ limit }} caractères !',
                    ]),
					new Regex([
						'pattern' => '/[~!@#\$%\^&\*\(\)-_=+\[\]{};:,\.<>\/\?|]+/',
						'message' => 'Votre mot de passe doit contenir au moins 1 caractère spécial !',
					]),
					new Regex([
						'pattern' => '/[0-9]+/',
						'message' => 'Votre mot de passe doit contenir au moins 1 chiffre !',
					]),
					new Regex([
						'pattern' => '/[a-z]+/',
						'message' => 'Votre mot de passe doit contenir au moins 1 lettre minuscule !',
					]),
					new Regex([
						'pattern' => '/[A-Z]+/',
						'message' => 'Votre mot de passe doit contenir au moins 1 lettre majuscule !',
					]),
                ],
            ])
			->add('verification',PasswordType::class, [
				'label' => false,
				'mapped' => false,
				'attr' => [
					'autocomplete' => 'new-password',
					'placeholder' => 'Retapez votre mot de passe'
				]
			])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}