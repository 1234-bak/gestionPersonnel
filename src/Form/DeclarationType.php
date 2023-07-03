<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Entity\Declaration;
use App\Entity\Typedeclaration;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class DeclarationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomconcerne', TextType::class, ['label' => 'Nom du (ou de la) concerné(e)'])
            ->add('prenomconcerne', TextType::class, ['label' => 'Prénom du (ou de la) concernée'])
            ->add('datedeclaration', DateType::class, ['label' => 'Date',
            'widget' => 'single_text',
            'attr' => [
                'autocomplete' => 'off',
            ],
            ])
            ->add('createdAt')
            ->add('updatedAt')
            ->add('type', EntityType::class, [
                'class' => Typedeclaration::class,
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.libelle', 'ASC');
                },
                'choice_label' => 'libelle',
            ])
            ->add('photo', FileType::class, [
                'label' => "Veuillez charger un justificatif (Certificat de naissance ou de décès). Assurez-vous de charger un fichier image ou un document PDF !",
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/png',
                            'image/jpeg',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez charger un fichier ou un document valide.',
                    ])
                ],
            ])
            ->add('statut',TextType::class,['attr' => [
                'style' => 'none'
            ]])
            ->add('hasProgrammeObsq', ChoiceType::class, [
                'label' => 'Choisir si (oui ou non) il y a un programme des obsèques',
                'choices' => [
                    'Oui' => true,
                    'Non' => false,
                ],
                
                'expanded' => true,
                'multiple' => false, 
                // 'required' => false, 
                'mapped' => true, 
            ])
            ->add('programmeobsq', FileType::class, [
                'label' => "Veuillez joindre le programme des obsèque. Assurez-vous de charger un fichier image ou un document PDF !",
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/png',
                            'image/jpeg',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez charger un fichier ou un document valide.',
                    ])
                ],
            ])
            ->add('editer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Declaration::class,
        ]);
    }
}
