<?php 
// src/Form/NoteServiceType.php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', TextareaType::class, [
                'label' => 'Date d\'édition',
            ])
            // ->add('emmeteur', TextType::class, [
            //     'label' => 'Emmeteur',
            // ])
            // ->add('destinataire', TextType::class, [
            //     'label' => 'Destinataire',
            // ])
            ->add('numref', TextareaType::class, [
                'label' => 'Code de référence',
            ])
            ->add('objet', TextareaType::class, [
                'label' => 'Objet',
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => ['rows' => 10],
            ]);
            // ->add('expediteur', TextType::class, [
            //     'label' => 'Poste de l\'emmeteur',
            // ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configurez la classe d'entité associée à ce formulaire
            // 'data_class' => NoteService::class,
        ]);
    }
}
