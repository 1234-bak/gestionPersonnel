<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\Privilege;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle',TextType::class,['label' => 'Libellé'])
            ->add('description',TextareaType::class,['label' => 'Description'])
            ->add('createdAt')
            ->add('updatedAt')
            ->add('privilege',EntityType::class,[
                'attr' => ['class' => 'choice'],
                'expanded' => false,
                'class' => Privilege::class,
                'required' => false,
                'multiple' => true,
                // 'attr' => [
                //     'class' => 'select2'
                // ],
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.libelle', 'ASC');
                    },
                    'choice_label' => 'libelle',
            
                ])
            ->add('editer',SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Role::class,
        ]);
    }
}
