<?php 
// src/Form/NoteServiceType.php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class NoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numref', TextareaType::class, [
                'label' => 'Code de référence',
            ])
            ->add('objet', TextareaType::class, [
                'label' => 'Objet',
            ])
            ->add('statut', TextType::class, [
                'attr' => [
                    'style' => 'none',
                ],
            ])
            // ->add('part1', TextareaType::class, [
            //     'label' => 'Partie 1 du corps',
            // ])
            // ->add('part2', TextareaType::class, [
            //     'label' => 'Partie 2 du corps',
            // ])
            // ->add('part3', TextareaType::class, [
            //     'label' => 'Partie 3 du corps',
            // ])
            // ->add('part4', TextareaType::class, [
            //     'label' => 'Partie 4 du corps',
            // ])
            // ->add('part5', TextareaType::class, [
            //     'label' => 'Partie 5 du corps',
            // ])
            ->add('pj', TextareaType::class, [
                'label' => 'Pièce jointe',
            ])
            ->add('date', DateType::class, ['label' => "Date d'édition",
                'widget' => 'single_text',
                'attr' => [
                    'autocomplete' => 'off',
                ],
            ])    
            ->add('contenu', TextareaType::class, [
            'label' => 'Contenu',
            'attr' => ['rows' => 10],
            ])
            // ->add('destinataire', EntityType::class, [
            //     'class' => Personne::class,
            //     'label' => 'Destinataires',
            //     'multiple' => true,
            //     'expanded' => true, // Afficher sous forme de cases à cocher
            //     'required' => false, // Laisser optionnel si nécessaire
            // ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configurez la classe d'entité associée à ce formulaire
            // 'data_class' => NoteService::class,
        ]);
    }
}
