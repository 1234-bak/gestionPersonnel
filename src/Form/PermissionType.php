<?php

namespace App\Form;

use App\Entity\Personne;
use App\Entity\Permission;
use App\Entity\TypePermission;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PermissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', EntityType::class, [
                'class' => TypePermission::class,
                'label' => "Veuillez choisir le motif de la demande",
                'required' => false,
                'attr' => [
                    'class' => 'select2'
                ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.libelle', 'ASC');
                },
                'choice_label' => 'libelle',
                
            ])
            ->add('datedebut', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'attr' => [
                    'autocomplete' => 'off',
                ],
            ])
            ->add('datefin', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'attr' => [
                    'autocomplete' => 'off',
                ],
            ])
            ->add('datereprise', DateType::class, [
                'label' => 'Date de reprise',
                'widget' => 'single_text',
                'attr' => [
                    'autocomplete' => 'off',
                ],
            ])
            ->add('duree', TextType::class, ['label' => 'Durée de la permission'])
            ->add('motif', TextareaType::class, ['label' => "Saisir le motif de la permission si le type n'est pas susmentionné"])
            ->add('statut', TextType::class, [
                'attr' => [
                    'style' => 'none',
                ],
            ])
            ->add('photo', FileType::class, [
                'label' => "Veuillez charger une preuve (Facultative). Assurez-vous de charger un fichier image ou un document PDF !",
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
                    ]),
                ],
            ])
            ->add('editer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Permission::class,
        ]);
    }
}