<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Personne;
use App\Entity\Declaration;
use App\Form\DeclarationType;
use App\Service\UploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Exception\StatutErrorException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DeclarationController extends AbstractController
{
    // Injecter le composant Security
    public function __construct(private Security $security)
    {
    }
    #[Route('/template', name: 'app_declaration')]
    public function index(): Response
    {
        return $this->render('admin-template.html.twig');
    }
    
    #[Route('/declaration/liste', name: 'declaration.liste')]
    public function indexAll(UserInterface $user,ManagerRegistry $doctrine): Response
    {

        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }
        $declarations = $doctrine->getRepository(Declaration::class)->findAll();

        return $this->render('declaration/liste-declaration.html.twig', [
            'declarations' => $declarations,
        ]);
    }

    #[Route('declaration/add/{id?0}', name: 'declaration.add')]
    public function add(
        Declaration $declaration = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user, // Ajout de UserInterface comme paramètre
    ): Response {
         // Récupérer l'utilisateur actuellement connecté


         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
             throw $this->createAccessDeniedException('Accès refusé.');
         }
 
         // Récupérer l'ID de la personne à partir de l'utilisateur
        
            if ($user && $user->getPersonne()) {
                $personneId = $user->getPersonne()->getId();
            }

         // ...
 
         $personne = $entityManager->getRepository(Personne::class)->find($personneId);

        if (!$personne) {
            throw $this->createNotFoundException('Personne non trouvée.');
        }
        
        $new = false;
        if (!$declaration) {
            $declaration = new Declaration();
            $new = true;
        }

        $form = $this->createForm(DeclarationType::class, $declaration);
        $form->remove('createdAt');
        $form->remove('updatedAt');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $declaration->setStatut("en attente");
            $photo = $form->get('photo')->getData();

            // Vérifie si une photo a été téléchargée
            if ($photo) {
                $directory = $this->getParameter('preuve_declaration_directory');
                $declaration->setPreuve($uploaderService->uploadFile($photo, $directory));
            }

            if ($new) {
                $personne->addDeclaration($declaration);
            }

            $entityManager->persist($declaration);
            $entityManager->flush();

            $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success", $declaration->getId() . ' ' . $message);

            return $this->redirectToRoute('declaration.afficher');
        }
        $repository = $entityManager->getRepository(Declaration::class);
        $declarations = $repository->findAll();


        return $this->render('declaration/edit-declaration.html.twig', [
            'form' => $form->createView(),
            'declarations' => $declarations
        ]);
    }

    #[Route('declaration/edit/{id?0}', name: 'declaration.edit')]
    public function edit(
        Declaration $declaration = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user, // Ajout de UserInterface comme paramètre
    ): Response {
         // Récupérer l'utilisateur actuellement connecté


         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
             throw $this->createAccessDeniedException('Accès refusé.');
         }
 
         // Récupérer l'ID de la personne à partir de l'utilisateur
        
            if ($user && $user->getPersonne()) {
                $personneId = $user->getPersonne()->getId();
            }

         // ...
 
         $personne = $entityManager->getRepository(Personne::class)->find($personneId);

        if (!$personne) {
            throw $this->createNotFoundException('Personne non trouvée.');
        }
        // Vérifier le statut de la déclaration
        if ($declaration->getStatut() !== Declaration::STATUT_EN_ATTENTE) {
            // throw new StatutErrorException('La déclaration ne peut pas être annulée car son statut est différent de "en attente".',400);
            die('La déclaration ne peut pas être annulée car son statut est différent de "en attente".');
        }
        $new = false;
        if (!$declaration) {
            $declaration = new Declaration();
            $new = true;
        }

        $form = $this->createForm(DeclarationType::class, $declaration);
        $form->remove('createdAt');
        $form->remove('updatedAt');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $declaration->setStatut("en attente");
            $photo = $form->get('photo')->getData();

            // Vérifie si une photo a été téléchargée
            if ($photo) {
                $directory = $this->getParameter('preuve_declaration_directory');
                $declaration->setPreuve($uploaderService->uploadFile($photo, $directory));
            }

            if ($new) {
                $personne->addDeclaration($declaration);
            }

            $entityManager->persist($declaration);
            $entityManager->flush();

            $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success", $declaration->getId() . ' ' . $message);

            return $this->redirectToRoute('declaration.afficher');
        }
        $repository = $entityManager->getRepository(Declaration::class);
        $declarations = $repository->findAll();


        return $this->render('declaration/edit-declaration.html.twig', [
            'form' => $form->createView(),
            'declarations' => $declarations
        ]);
    }

    
    #[Route('/declaration/{id<\d+>}', name: 'declaration.detail')]
    public function detail(UserInterface $user,Declaration $declaration = null): Response
    {

        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            // throw $this->createAccessDeniedException('Accès refusé.');
            die('accès réfusé');
        }
        if (!$declaration) {
            $this->addFlash('error', "La déclaration n'existe pas");
            return $this->redirectToRoute('declaration.liste');
        }

        return $this->render('declaration/detail.html.twig', [
            'declaration' => $declaration
        ]);
    }

    #[Route('/declaration/{id<\d+>}', name: 'declaration.show')]
    public function show(UserInterface $user,Declaration $declaration = null): Response
    {

        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            // throw $this->createAccessDeniedException('Accès refusé.');
            die('accès réfusé');
        }
        if (!$declaration) {
            $this->addFlash('error', "La déclaration n'existe pas");
            return $this->redirectToRoute('declaration.afficher');
        }

        return $this->render('declaration/show-declaration.html.twig', [
            'declaration' => $declaration
        ]);
    }
     
    #[Route('/declaration/mesdeclarations', name: 'declaration.afficher')]
    public function afficherDeclarations(ManagerRegistry $doctrine,UserInterface $user): Response
    {
        // Récupérer l'utilisateur actuellement connecté

        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        // Récupérer l'ID de la personne à partir de l'utilisateur
       
           if ($user && $user->getPersonne()) {
               $personneId = $user->getPersonne()->getId();
           }

        // ...
        $personne = $doctrine->getRepository(Personne::class)->find($personneId);

        if (!$personne) {
            throw $this->createNotFoundException('Personne non trouvée.');
        }

        $declaration = $personne->getDeclaration();

        return $this->render('declaration/mesdeclarations.html.twig', [
            'personne' => $personne,
            'declarations' => $declaration,
        ]);
    }

    #[Route('/declaration/supprimer/{personneId}/{declarationId}', name: 'declaration.supprimer')]
    public function supprimerDeclaration(
        ManagerRegistry $doctrine,
        Request $request,
        $personneId,
        $declarationId,
        UserInterface $user
    ): Response {

        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }
       
        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
        }
        $personne = $doctrine->getRepository(Personne::class)->find($personneId);

        if (!$personne) {
            throw $this->createNotFoundException('Personne non trouvée.');
        }

        $declaration = $doctrine->getRepository(Declaration::class)->find($declarationId);

        if (!$declaration) {
            throw $this->createNotFoundException('Déclaration non trouvée.');
        }

        if ($declaration->getPersonne() !== $personne) {
            throw $this->createNotFoundException('La déclaration ne correspond pas à la personne spécifiée.');
        }

        if ($declaration->getStatut() === 'en attente') {
            $personne->removeDeclaration($declaration);

            $entityManager = $doctrine->getManager();
            $entityManager->remove($declaration);
            $entityManager->flush();
        } else {
            // Gérer le cas où le statut n'est pas "annulé", par exemple afficher un message d'erreur ou rediriger vers une autre page
            return $this->redirectToRoute('declaration.detail', ['id' => $declarationId]);
        }

        return $this->redirectToRoute('declaration.afficher', ['personneId' => $personneId]);
    }

    #[Route('/declaration/changer-statut/{declarationId}/{nouveauStatut}', name: 'declaration.changer_statut')]
    public function changerStatutDeclaration(
        UserInterface $user,
        ManagerRegistry $doctrine,
        Request $request,
        $declarationId,
        $nouveauStatut
    ): Response {

        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }
        $declaration = $doctrine->getRepository(Declaration::class)->find($declarationId);

        if (!$declaration) {
            throw $this->createNotFoundException('Déclaration non trouvée.');
        }

        // Modifier le statut de la déclaration
        $declaration->setStatut($nouveauStatut);

        $entityManager = $doctrine->getManager();
        $entityManager->flush();

        return $this->redirectToRoute('declaration.detail', ['id' => $declarationId]);
    }

    
    #[Route('/declaration/annuler/{declarationId}', name: 'declaration.annuler')]
    public function annulerDeclaration(
        ManagerRegistry $doctrine,
        Request $request,
        $declarationId
    ): Response {
        $declarationRepository = $doctrine->getRepository(Declaration::class);
        $declaration = $declarationRepository->find($declarationId);

        if (!$declaration) {
            // throw new StatutErrorException('Déclaration non trouvée.',400);
            die('Déclaration non trouvée.');
        }

        // Vérifier si l'utilisateur est authentifié
        $user = $this->security->getUser();
        if (!$user) {
            // throw new AccessDeniedHttpException('Accès refusé.');
            die('Accès refusé.');
        }

        // Vérifier si l'utilisateur a le droit d'annuler la déclaration
        $personne = $user->getPersonne();
        if (!$personne || $declaration->getPersonne() !== $personne) {
            // throw new StatutErrorException('Vous n\'êtes pas autorisé à annuler cette déclaration.',400);
            die('Vous n\'êtes pas autorisé à annuler cette déclaration.');
        }

        // Vérifier le statut de la déclaration
        if ($declaration->getStatut() !== Declaration::STATUT_EN_ATTENTE) {
            // throw new StatutErrorException('La déclaration ne peut pas être annulée car son statut est différent de "en attente".',400);
            die('La déclaration ne peut pas être annulée car son statut est différent de "en attente".');
        }
        $personne->removeDeclaration($declaration);

        $entityManager = $doctrine->getManager();
        $entityManager->remove($declaration);
        $entityManager->flush();

        return $this->redirectToRoute('declaration.afficher', ['declarationId' => $declarationId]);
    }

    #[Route('/declaration/valider/{declarationId}', name: 'declaration.valider')]
    public function validerDeclaration(
        ManagerRegistry $doctrine,
        Request $request,
        $declarationId
    ): Response {
        $declarationRepository = $doctrine->getRepository(Declaration::class);
        $declaration = $declarationRepository->find($declarationId);

        if (!$declaration) {
            // throw new StatutErrorException('Déclaration non trouvée.',400);
            die('Déclaration non trouvée.');
        }

        // Vérifier si l'utilisateur est authentifié
        $user = $this->security->getUser();
        if (!$user) {
            // throw new StatutErrorException('Accès refusé.',400);
            die('Accès refusé.');
        }

        // Vérifier si l'utilisateur a le droit de valider la déclaration
        $personne = $user->getPersonne();
        if (!$personne || $declaration->getPersonne() !== $personne) {
            // throw new StatutErrorException('Vous n\'êtes pas autorisé à annuler cette déclaration.',400);
            die('Vous n\'êtes pas autorisé à valider cette déclaration.');
        }

        // Vérifier le statut de la déclaration
        if ($declaration->getStatut() !== Declaration::STATUT_EN_ATTENTE) {
            // throw new StatutErrorException('La déclaration ne peut pas être validée car son statut est différent de "en attente".',400);
            die('La déclaration ne peut pas être validée car son statut est différent de "en attente".');
        }

        // valider la déclaration en changeant son statut
        $declaration->setStatut(Declaration::STATUT_VALIDEE);

        $entityManager = $doctrine->getManager();
        $entityManager->flush();

        return $this->redirectToRoute('declaration.liste', ['declarationId' => $declarationId]);
    }

    #[Route('/declaration/rejeter/{declarationId}', name: 'declaration.rejeter')]
    public function rejeterDeclaration(
        ManagerRegistry $doctrine,
        Request $request,
        $declarationId
    ): Response {
        $declarationRepository = $doctrine->getRepository(Declaration::class);
        $declaration = $declarationRepository->find($declarationId);

        if (!$declaration) {
            // throw new StatutErrorException('Déclaration non trouvée.',400);
            die('Déclaration non trouvée.');
        }

        // Vérifier si l'utilisateur est authentifié
        $user = $this->security->getUser();
        if (!$user) {
            // throw new StatutErrorException('Accès refusé.',400);
            die('Accès refusé.');
        }

        // Vérifier si l'utilisateur a le droit de rejeter la déclaration
        $personne = $user->getPersonne();
        if (!$personne || $declaration->getPersonne() !== $personne) {
            // throw new StatutErrorException('Vous n\'êtes pas autorisé à annuler cette déclaration.',400);
            die('Vous n\'êtes pas autorisé à rejeter cette déclaration.');
        }

        // Vérifier le statut de la déclaration
        if ($declaration->getStatut() !== Declaration::STATUT_EN_ATTENTE) {
            // throw new StatutErrorException('La déclaration ne peut pas être validée car son statut est différent de "en attente".',400);
            die('La déclaration ne peut pas être validée car son statut est différent de "en attente".');
        }

        // rejeter la déclaration en changeant son statut
        $declaration->setStatut(Declaration::STATUT_REJETEE);

        $entityManager = $doctrine->getManager();
        $entityManager->flush();

        return $this->redirectToRoute('declaration.liste', ['declarationId' => $declarationId]);
    }
}
