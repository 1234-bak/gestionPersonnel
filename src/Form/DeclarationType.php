<?php

namespace App\Form;

use App\Entity\Declaration;
use App\Form\FileDecesType;
use App\Form\FileNaissType;
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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class DeclarationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typedeclaration',ChoiceType::class,[
                'choices' => [
                    "Naissance" => "Naissance",
                    "Décès agent" => "Décès agent",
                    "Décès parent" => "Décès parent",
                ],
                "expanded" => true,
                "multiple" => false,
                'mapped' => true, 
                'label_attr' => ['class' => 'radio-label'],
                'attr' => ['class' => 'radio-input'],

            ])
            ->add('enfant', TextType::class, ['label' => "Nom et Prénoms de l'enfant "])
            ->add('matriculedeces', TextType::class, ['label' => "Matricule de l'agent décédé"])
            ->add('parent', TextType::class, ['label' => 'Nom et prenom du parent décédé'])
            ->add('datenaiss', DateType::class, ['label' => "Date de naissance de l'enfant ",
            'widget' => 'single_text',
            'attr' => [
                'autocomplete' => 'off',
            ],
            ])
            ->add('datedeces', DateType::class, ['label' => 'Date du décès',
            'widget' => 'single_text',
            'attr' => [
                'autocomplete' => 'off',
            ],
            ])
            ->add('createdAt')
            ->add('updatedAt')
            ->add('typedeclaration',ChoiceType::class,[
                'choices' => [
                    "Naissance" => "Naissance",
                    "Décès agent" => "Décès agent",
                    "Décès parent" => "Décès parent",
                ],
                "expanded" => true,
                "multiple" => false,
                'mapped' => true, 
                'label_attr' => ['class' => 'radio-label'],
                'attr' => ['class' => 'radio-input'],
    
            ])
            ->add('fichiernaiss', CollectionType::class, [
                'entry_type' => FileType::class,
                'entry_options' => [
                    'label' => "Veuillez charger un justificatif (Certificat de naissance et/ou autres fichier justifiant). Assurez-vous de charger un fichier image ou un document PDF !",
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
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('fichierdeces', CollectionType::class, [
                'entry_type' => FileType::class, // Utilisation de FileType ici
                'entry_options' => [
                    'label' => "Veuillez charger un justificatif (Acte de décès et/ou autres fichier justifiant). Assurez-vous de charger des fichiers image ou des documents PDF !",
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
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
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
                            'image/webp',
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
