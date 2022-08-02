<?php

namespace App\Form;

use App\Entity\Film;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Textarea;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormulaireFilm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', FileType::class, [
				'mapped' => false,
				'required' => true,
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
            ->add('synopsis', TextareaType::class, [
				'required' => true,
				'label' => false,
				'attr' => ['placeholder' => 'Synopsis du film']
			])
			->add('acteur_principal', TextType::class, [
				'required' => false,
				'label' => false,
				'attr' => ['placeholder' => 'Qui est l\'acteur principal du film ?']
			])
			->add('submit', SubmitType::class, [
				'label' => 'Ajouter un film'
			]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Film::class,
        ]);
    }
}