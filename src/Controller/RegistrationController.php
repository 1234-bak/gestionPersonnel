<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Personne;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/verify-matricule', name: 'app_verify_matricule')]
    public function verifyMatricule(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder(null, [
            'action' => $this->generateUrl('app_verify_matricule'),
            'method' => 'POST'
        ])
        ->add('matricule', TextType::class, [
            'label' => 'Matricule',
        ])
        ->add('datenaiss', BirthdayType::class, ['label' => 'Date de naissance',
        'widget' => 'single_text',
        'attr' => [
        'autocomplete' => 'off',
        ],
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'Lancer la vérification',
        ])
        ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $matricule = $data['matricule'];
            $datenaiss = $data['datenaiss'];

            // Effectuer la vérification
            $memberRepository = $entityManager->getRepository(Personne::class);
            $existingMember = $memberRepository->findOneBy(['matricule' => $matricule, 'datenaiss' => $datenaiss]);
            if ($existingMember) {
                // Le matricule existe, rediriger vers la page d'inscription
                $this->addFlash('succes', 'Vérification réussie');
                return $this->redirectToRoute('app_register', ['matricule' => $matricule, 'datenaiss' => $datenaiss->format('Y-m-d')]);
            } else {
                // Le matricule n'existe pas, afficher un message flash
                $this->addFlash('error', 'Matricule ou date de naissance invalide.');
            }
        }

        return $this->render('registration/verify_matricule.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/register/{matricule?0}/{datenaiss?0}', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, string $matricule, DateTime $datenaiss): Response
    {
        
        $existingPersonne = $entityManager->getRepository(Personne::class)->findOneBy(['matricule' => $matricule, 'datenaiss' => $datenaiss]);

        if (!$existingPersonne) {
            // Le matricule n'existe pas, afficher un message flash
            $this->addFlash('error', 'Informations invalides.');
            return $this->redirectToRoute('app_verify_matricule');
        }

        try {
            $user = new User();
            $user->setPersonne($existingPersonne);
            $user->setMatricule($matricule);
            $matriculeUser = $user->getPersonne();
            $form = $this->createForm(RegistrationFormType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // encode the plain password
                $user->setPassword(
                    $userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData())
                );

                $entityManager->persist($user);
                $entityManager->flush();

                // do anything else you need here, like send an email
                $this->addFlash('success',"Compte crée avec succès ! appuyez sur la touche ok pour vous connecté ");
                // return $this->redirectToRoute('app_login');
            }
        }catch (UniqueConstraintViolationException $e) {
            // Redirigez vers la page d'erreur personnalisée avec le message d'erreur
            $errorMessage = "Une erreur s'est produite lors de la création du compte : " . $e->getMessage();
            $this->addFlash('error',"vous ne pouvez plus créer de compte car vous en avez dejà un");
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'personne' => $existingPersonne,
            'matriculeUser' =>$matriculeUser,
            'matricule' => $matricule,
            'user' => $user
            // 'user' => $user
        ]);
    }

    #[Route('/show-information/{matricule}/{datenaiss}', name: 'app_show_information')]
    public function showInformation(string $matricule, DateTime $datenaiss, EntityManagerInterface $entityManager): Response
    {
        // Convertir la valeur de datenaiss en objet DateTime
        $personne = $entityManager->getRepository(Personne::class)->findOneBy(['matricule' => $matricule, 'datenaiss' => $datenaiss]);

        if (!$personne) {
            // Le matricule n'existe pas, afficher un message flash
            $this->addFlash('error', 'matricule ou date de naissance invalides.');
            return $this->redirectToRoute('app_verify_matricule');
        }

        // Afficher les informations dans une vue dédiée
        return $this->render('personne/show-information.html.twig', [
            'personne' => $personne,
            'matricule' => $matricule

        ]);
    }


}
