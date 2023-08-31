<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\Personne;
use App\Entity\Direction;
use App\Entity\Fonction;
use App\Entity\SousDirection;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PersonneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('createdAt')
            ->add('updatedAt')
            ->add('matricule')
            ->add('civilite')
            ->add('nom')
            ->add('prenom')
            ->add('structure')
            ->add('fonction',EntityType::class,[
                'label' => 'Fonction',
                'class' => Fonction::class,
                'required' => false,
                'attr' => [
                    'class' => 'select2'
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('f')
                        ->orderBy('f.designation', 'ASC');
                    },
                    'choice_label' => 'designation',
            
                ])
            ->add('service',EntityType::class,[
                'label' => 'Service',
                'class' => Service::class,
                'required' => false,
                'attr' => [
                    'class' => 'select2'
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->orderBy('s.designation', 'ASC');
                    },
                    'choice_label' => 'designation',
            
                ])
            ->add('direction',EntityType::class,[
                'label' => 'Direction',
                'class' => Direction::class,
                'required' => false,
                'attr' => [
                    'class' => 'select2'
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->orderBy('d.designation', 'ASC');
                    },
                    'choice_label' => 'designation',
            
                ])
                ->add('sousdirection',EntityType::class,[
                    'label' => 'Sous-direction',
                    'class' => SousDirection::class,
                    'required' => false,
                    'attr' => [
                        'class' => 'select2'
                    ],
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
            ->add('grade')
            ->add('sexe')
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
