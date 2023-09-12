<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use App\Form\RoleType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class AdminEditUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            
            ->add('matricule', TextType::class, [
                'constraints' =>[
                    new NotBlank(["message"=>"merci de saisir votre matricule"]),
                ],
                'required' => true,
                'attr' =>[
                    'class' => 'form_control'
                ],
                'label' => 'Matricule',
            ])
            // ->add('plainPassword', RepeatedType::class, [
            //     'type' => PasswordType::class,
            //     'invalid_message' => 'Les champs du mot de passe doivent correspondre.',
            //     'options' => ['attr' => ['class' => 'password-field']],
            //     'required' => true,
            //     'first_options'  => ['label' => 'Entrez votre mot de passe'],
            //     'second_options' => ['label' => 'Confirmez le mot de passe'],
            //     'constraints' => [
            //         new NotBlank([
            //             'message' => 'Please enter a password',
            //         ]),
            //         new Length([
            //             'min' => 6,
            //             'minMessage' => 'Votre mot de passe doit comporter au moins {{ limit }} caratères',
            //             // max length allowed by Symfony for security reasons
            //             'max' => 4096,
            //         ]),
            //     ],
            // ])
            ->add('roles',ChoiceType::class,[
                'attr' => ['class' => 'choice'],
                'label' => 'Choisir le rôle de l\'utilisateur',
                'choices' => [
                    "Agent" => "ROLE_USER",
                    "Administrateur" => "ROLE_ADMIN",
                    "Gestionnaire Ressource Humaine" => "ROLE_GRH",
                    "Chef de Service" => "ROLE_CS",
                    "Directeur" => "ROLE_DIR",
                    "Sous-directeur" => "ROLE_SD",
                    "Directeur de Cabinet" => "ROLE_DIRCAB",
                ],
                // 'class' => Role::class,
                "expanded" => true,
                "multiple" => true,
                // 'query_builder' => function (EntityRepository $er) {
                //     return $er->createQueryBuilder('r')
                //     ->orderBy('r.libelle', 'ASC');
                // },
                // 'choice_label' => 'libelle',
                ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
