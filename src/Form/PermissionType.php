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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PermissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('typepermission',ChoiceType::class,[
            'choices' => [
                "Décès d'un ascendant ou d'un descendant en ligne directe" => "Décès d'un ascendant ou d'un descendant en ligne directe",
                "Mariage de l'agent ou d'un enfant de l'agent" => "Mariage de l'agent ou d'un enfant de l'agent",
                "Naissance survenue au foyer du fonctionnaire" => "Naissance survenue au foyer du fonctionnaire",
                "autres" => "autres"
            ],
            "expanded" => true,
            "multiple" => false,
            'mapped' => true, 
            'label_attr' => ['class' => 'radio-label'],
            'attr' => ['class' => 'radio-input'],

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
            ->add('duree', TextType::class, [
                'label' => 'Durée maximale de la permission',
                'required' => false,
                'attr' => [
                    'readonly' => 'readonly',
                    
                ],
            ])
            ->add('motif', TextareaType::class, ['label' => "Saisir le motif de la permission"])
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
            ->add('envoyer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Permission::class,
        ]);
    }
}