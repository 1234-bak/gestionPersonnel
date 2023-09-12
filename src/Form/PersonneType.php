<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\Fonction;
use App\Entity\Personne;
use App\Entity\Direction;
use App\Entity\SousDirection;
use Doctrine\ORM\EntityRepository;

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
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;

class PersonneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('createdAt')
            ->add('updatedAt')
            ->add('matricule')
            ->add('civilite', ChoiceType::class, [
                'attr' => ['class' => 'choice'],
                'label' => 'Civilité',
                'choices' => [
                    'Mr' => 'Mr',
                    'Mme' => 'Mme',
                ],
                
                'expanded' => false,
                'multiple' => false, 
                // 'required' => false, 
                'mapped' => true,
            ])
            ->add('nom')
            ->add('prenom')
            ->add('structure', TextType::class, [
                'data' => "Ministère de la fonction publique",
            ])
            
            ->add('fonction',EntityType::class,[
                'attr' => ['class' => 'choice'],
                'label' => 'Fonction',
                'class' => Fonction::class,
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('f')
                        ->orderBy('f.designation', 'ASC');
                    },
                    'choice_label' => 'designation',
            
                ])
            ->add('service',EntityType::class,[
                'attr' => ['class' => 'choice'],
                'label' => 'Service',
                'class' => Service::class,
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->orderBy('s.designation', 'ASC');
                    },
                    'choice_label' => 'designation',
            
                ])
            ->add('direction',EntityType::class,[
                'attr' => ['class' => 'choice'],
                'label' => 'Direction',
                'class' => Direction::class,
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->orderBy('d.designation', 'ASC');
                    },
                    'choice_label' => 'designation',
            
                ])
                ->add('sousdirection',EntityType::class,[
                    'attr' => ['class' => 'choice'],
                    'label' => 'Sous-direction',
                    'class' => SousDirection::class,
                    'required' => false,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('s')
                            ->orderBy('s.designation', 'ASC');
                        },
                        'choice_label' => 'designation',
                
                    ])
            ->add('datenaiss',BirthdayType::class,['label' => 'Date de naissance',
            'widget' => 'single_text',
            'attr' => [
                'autocomplete' => 'off',
            ],
            ])
            ->add('lieunaiss',TextType::class,['label' => 'Lieu de naissance'])
            ->add('nompere',TextType::class,['label' => 'Nom du père'])
            ->add('nommere',TextType::class,['label' => 'Nom de la mère'])
            ->add('nbreenfant',TextType::class,['label' => "Nombre d'enfant"])
            ->add('grade', ChoiceType::class, [
                'attr' => ['class' => 'choice'],
                'label' => 'Grade',
                'choices' => [
                    'A1' => 'A1',
                    'A2' => 'A2',
                    'A3' => 'A3',
                    'A4' => 'A4',
                    'A5' => 'A5',
                    'A6' => 'A6',
                    'A7' => 'A7',
                    'B1' => 'B1',
                    'B2' => 'B2',
                    'B3' => 'B3',
                    'B4' => 'B4',
                    'B5' => 'B5',
                    'B6' => 'B6',
                    'B7' => 'B7',
                    'C1' => 'C1',
                    'C2' => 'C2',
                    'C3' => 'C3',
                    'C4' => 'C4',
                    'C5' => 'C5',
                    'C6' => 'C6',
                    'C7' => 'C7',
                    'D1' => 'D1',
                    'D2' => 'D2',
                    'D3' => 'D3',
                    'D4' => 'D4',
                    'D5' => 'D5',
                    'D6' => 'D6',
                    'D7' => 'D7',
                    
                ],
                
                'expanded' => false,
                'multiple' => false, 
                // 'required' => false, 
                'mapped' => true,
            ])
            ->add('sexe', ChoiceType::class, [
                'attr' => ['class' => 'choice'],
                'label' => 'Sexe',
                'choices' => [
                    'Homme' => 'Homme',
                    'Femme' => 'Femme',
                ],
                
                'expanded' => false,
                'multiple' => false, 
                // 'required' => false, 
                'mapped' => true,
            ])
            ->add('telephone')
            ->add('photo', FileType::class, [
                'label' => 'Charger une Image de Profil au format("jpg", "JPG","jpeg", "JPEG","png","PNG")',
                // unmapped means that this field is not associated to any entity property
                'mapped' => false,
                // make it optional so you don't have to re-upload the image
                // every time you edit the Personne details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/png',
                            'image/jpeg',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide.',
                    ])
                ],
            ])
            ->add('signature', FileType::class, [
                'label' => 'Charger une Signature au format("jpg", "JPG","jpeg", "JPEG","png","PNG","gif",GIF")',
                // unmapped means that this field is not associated to any entity property
                'mapped' => false,
                // make it optional so you don't have to re-upload the image
                // every time you edit the Personne details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/png',
                            'image/jpeg',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide.',
                    ])
                ],
            ])
            ->add('editer',SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Personne::class,
        ]);
    }
}
