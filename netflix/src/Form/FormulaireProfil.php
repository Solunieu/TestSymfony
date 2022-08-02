<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormulaireProfil extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('avatar', FileType::class, [
				'mapped' => false,
				'required' => false,
				'label' => false,
				'constraints' => [
					new File([
						'maxSize' => '4096k',
						'maxSizeMessage' => 'Attention ! Taille maximale acceptée : {{ limit }}',
						'mimeTypes' => [
							'image/png',
							'image/jpeg'
						],
						'mimeTypesMessage' => 'Attention ! Formats acceptés : JPG et PNG'
					])
				]
			])
            ->add('email', EmailType::class, [
				'required' => true,
				'label' => false,
				'disabled' => true
			])
			->add('pseudo', TextType::class, [
				'required' => false,
				'label' => false,
				'attr' => ['placeholder' => 'Votre pseudo']
			])
			->add('submit', SubmitType::class, [
				'label' => 'Modifier'
			]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}