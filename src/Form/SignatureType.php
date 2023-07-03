<?php

namespace App\Form;

use App\Entity\Signature;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SignatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        // ->add('personne')
        ->add('photo', FileType::class, [
            'label' => "Veuillez charger une nouvelle signature, cette signature seras prioritaire sur toutes votre signature précédente  !",
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
            'data_class' => Signature::class,
        ]);
    }
}
