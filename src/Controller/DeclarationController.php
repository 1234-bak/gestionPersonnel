<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Personne;
use App\Entity\FileDeces;
use App\Entity\FileNaiss;
use App\Entity\Declaration;
use App\Service\PdfService;
use App\Entity\Notification;
use App\Form\DeclarationType;
use App\Service\UploaderService;
use App\Exception\StatutErrorException;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    #[Route('declaration/liste/{id?0}', name: 'declaration.liste')]
    public function indexAll(
        Declaration $declaration = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user, 
        NotificationRepository $notificationRepository
    ): Response {
        $roles = 'ROLE_GRH';
        $this->denyAccessUnlessGranted($roles, null, 'Vous n\'avez pas la permission d\'accéder à cette page.');
         if (!$user) {
            $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
         }
 
         // Récupérer l'ID de la personne à partir de l'utilisateur
        
            if ($user && $user->getPersonne()) {
                $personneId = $user->getPersonne()->getId();
            }
 
         $personne = $entityManager->getRepository(Personne::class)->find($personneId);
        
        $repository = $entityManager->getRepository(Declaration::class);
        $nbDeclaration = $repository->count([]);
        $declarations = $repository->findBy([], ['id' => 'DESC']);

        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('declaration/liste-declaration.html.twig', [
            'personne' => $personne,
            'nbDeclaration' => $nbDeclaration,
            'declarations' => $declarations,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('declaration/add/{id?0}', name: 'declaration.add')]
    public function add(
        Declaration $declaration = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
        NotificationRepository $notificationRepository
    ): Response {

        $roles = 'ROLE_USER';
        $this->denyAccessUnlessGranted($roles, null, 'Vous n\'avez pas la permission d\'accéder à cette page.');
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
            $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
         }
 
         // Récupérer l'ID de la personne à partir de l'utilisateur
        
            if ($user && $user->getPersonne()) {
                $personneId = $user->getPersonne()->getId();
            }

         // ...
 
         $personne = $entityManager->getRepository(Personne::class)->find($personneId);

        if (!$personne) {
            $this->addFlash('error',"Agent introuvable !");
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
            $photonaisss = $form->get('fichiernaiss')->getData();
            $photodecess = $form->get('fichierdeces')->getData();
            $directory = $this->getParameter('preuve_declaration_directory');
            if ($photonaisss) {
                foreach ($photonaisss as $photo) {
                    if ($photo instanceof UploadedFile) {
                        $fileNaiss = new FileNaiss(); // Créez une instance de l'entité FileNaiss
                        $fileNaiss->setDeclaration($declaration);
                        $entityManager->persist($fileNaiss);
                        $fileNaiss->setPath($uploaderService->uploadFile($photo, $directory));
                    }
                }
            }

            if ($photodecess) {
                foreach ($photodecess as $photo) {
                    if ($photo instanceof UploadedFile) {
                        $fileDeces = new FileDeces(); // Créez une instance de l'entité FileDeces
                        $fileDeces->setDeclaration($declaration);
                        $entityManager->persist($fileDeces);
                        $fileDeces->setPath($uploaderService->uploadFile($photo, $directory));
                    }
                }
            }
            if ($new) {
                $personne->addDeclaration($declaration);
            }
            $fonction = $personne->getFonction();

            // Initialiser un tableau pour stocker les désignations de fonctions spécifiques
            $fonctionsSpecifiques = ["Directeur", "Sous-directeur", "Chef de service"];

            // Vérifier si la fonction de la personne correspond à l'une des fonctions spécifiques
            if ($fonction && in_array($fonction->getDesignation(), $fonctionsSpecifiques)) {
                // Créer et envoyer une notification à la personne
                $notification = new Notification();
                $notification->setMessage('Une nouvelle déclaration a été envoyée.');
                $notification->addDestinataire($personne);
                $notification->setDateenvoi(new \DateTime());
                $entityManager->persist($notification);
            }

            $entityManager->persist($declaration);
            $entityManager->flush();
            
            $message = $new ? "a été envoyé avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success", 'Votre déclaration ' . $message);

            return $this->redirectToRoute('declaration.afficher');
        }
        $repository = $entityManager->getRepository(Declaration::class);
        $declarations = $repository->findAll();
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('declaration/edit-declaration.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'declarations' => $declarations,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('declaration/edit/{id?0}', name: 'declaration.edit')]
    public function edit(
        $id,
        Declaration $declaration = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user, 
        NotificationRepository $notificationRepository
    ): Response {

        $roles = 'ROLE_USER';
        $this->denyAccessUnlessGranted($roles, null, 'Vous n\'avez pas la permission d\'accéder à cette page.');
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
            $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
        }
 
         // Récupérer l'ID de la personne à partir de l'utilisateur
        
            if ($user && $user->getPersonne()) {
                $personneId = $user->getPersonne()->getId();
            } 
            $personne = $entityManager->getRepository(Personne::class)->find($personneId);

            if (!$personne) {
                $this->addFlash('error',"Agent introuvable !");
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
            $photonaisss = $form->get('fichiernaiss')->getData();
            $photodecess = $form->get('fichierdeces')->getData();
            $directory = $this->getParameter('preuve_declaration_directory');
            if ($photonaisss) {
                foreach ($photonaisss as $photo) {
                    if ($photo instanceof UploadedFile) {
                        $fileNaiss = new FileNaiss(); // Créez une instance de l'entité FileNaiss
                        $fileNaiss->setDeclaration($declaration);
                        $entityManager->persist($fileNaiss);
                        $fileNaiss->setPath($uploaderService->uploadFile($photo, $directory));
                    }
                }
            }

            if ($photodecess) {
                foreach ($photodecess as $photo) {
                    if ($photo instanceof UploadedFile) {
                        $fileDeces = new FileDeces(); // Créez une instance de l'entité FileDeces
                        $fileDeces->setDeclaration($declaration);
                        $entityManager->persist($fileDeces);
                        $fileDeces->setPath($uploaderService->uploadFile($photo, $directory));
                    }
                }
            }
            if ($new) {
                $personne->addDeclaration($declaration);
            }
            $fonction = $personne->getFonction();

            // Initialiser un tableau pour stocker les désignations de fonctions spécifiques
            $fonctionsSpecifiques = ["Directeur", "Sous-directeur", "Chef de service"];

            // Vérifier si la fonction de la personne correspond à l'une des fonctions spécifiques
            if ($fonction && in_array($fonction->getDesignation(), $fonctionsSpecifiques)) {
                // Créer et envoyer une notification à la personne
                $notification = new Notification();
                $notification->setMessage('Une nouvelle déclaration a été envoyée.');
                $notification->addDestinataire($personne);
                $notification->setDateenvoi(new \DateTime());
                $entityManager->persist($notification);
            }

            $entityManager->persist($declaration);
            $entityManager->flush();
            
            $message = $new ? "a été envoyé avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success", 'Votre déclaration ' . $message);
            return $this->redirectToRoute('declaration.afficher');
        }
        // Vérifier le statut de la déclaration
        if ($declaration->getStatut() !== Declaration::STATUT_EN_ATTENTE) {
            $this->addFlash('error','Vous ne pouvez plus modifier cette déclaration.');
            return $this->redirectToRoute('declaration.afficher', ['declarationId' => $id]);
        }
        $repository = $entityManager->getRepository(Declaration::class);
        $declarations = $repository->findAll();
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('declaration/edit-declaration.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'declarations' => $declarations,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }
    
    #[Route('/mesdeclaration/{id<\d+>}', name: 'mesdeclaration.detail')]
    public function detailMesDeclarations(UserInterface $user,Declaration $declaration = null): Response
    {
        $roles = 'ROLE_USER';
        $this->denyAccessUnlessGranted($roles, null, 'Vous n\'avez pas la permission d\'accéder à cette page.');

        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
        }
        if (!$declaration) {
            $this->addFlash('error', "Déclaration introuvable");
            return $this->redirectToRoute('declaration.liste');
        }

        return $this->render('declaration/detail-mesdeclarations.html.twig', [
            'declaration' => $declaration,
            'user' => $user
        ]);
    }

    #[Route('/declaration/{id<\d+>}', name: 'declaration.detail')]
    public function detailDeclarations(UserInterface $user,Declaration $declaration = null): Response
    {
        $roles = 'ROLE_GRH';
        $this->denyAccessUnlessGranted($roles, null, 'Vous n\'avez pas la permission d\'accéder à cette page.');

        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            // $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
            $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
        }
        if (!$declaration) {
            $this->addFlash('error', "Déclaration introuvable");
            return $this->redirectToRoute('declaration.liste');
        }

        return $this->render('declaration/detail-declarations.html.twig', [
            'declaration' => $declaration,
            'user' => $user
        ]);
    }

    #[Route('/mesdeclarationsvalidees/{id<\d+>}', name: 'declarationvalidees.detail')]
    public function showDeclarationValidee(UserInterface $user,Declaration $declaration = null,EntityManagerInterface $entityManager,PdfService $pdf): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            // throw $this->createAccessDeniedException('Accès refusé.');
            $this->addFlash('error',"Accès refusé ! vous avoir un compte !");
        }
        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
        }
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);
        if (!$declaration) {
            $this->addFlash('error', "Déclaration introuvable !");
            return $this->redirectToRoute('declaration.afficher');
        }

        $html = $this->render('declaration/doc-declarationvalidees.html.twig', [
            'declaration' => $declaration,
            'personne' => $personne,
            'user' => $user
        ]);
        $pdf->showPdfFile($html);

        return new Response("PDF généré avec succès.");
    }
     
    #[Route('/declaration/mesdeclarations/{id?0}', name: 'declaration.afficher')]
    public function afficherDeclarations(
        Declaration $declaration = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
        NotificationRepository $notificationRepository
    ): Response {

        $roles = 'ROLE_USER';
        $this->denyAccessUnlessGranted($roles, null, 'Vous n\'avez pas la permission d\'accéder à cette page.');
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
             $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
         }
 
         // Récupérer l'ID de la personne à partir de l'utilisateur
        
            if ($user && $user->getPersonne()) {
                $personneId = $user->getPersonne()->getId();
            }
 
         $personne = $entityManager->getRepository(Personne::class)->find($personneId);

        if (!$personne) {
            $this->addFlash('error','Agent introuvable');
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
                $photonaiss = $form->get('fichiernaiss')->getData();
                $photodeces = $form->get('fichierdeces')->getData();

            // Vérifier si une photo a été téléchargée
            if ($photonaiss instanceof UploadedFile) {
                $directory = $this->getParameter('preuve_declaration_directory');
                $declaration->setFichiernaiss($uploaderService->uploadFile($photonaiss, $directory));
            }

            // Vérifier si un acte de décès a été téléchargé
            if ($photodeces instanceof UploadedFile) {
                $directory = $this->getParameter('preuve_declaration_directory');
                $declaration->setFichierdeces($uploaderService->uploadFile($photodeces, $directory));
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
        $nbMesDeclaration = $repository->count([]);
        $declarations = $personne->getDeclaration();
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('declaration/mesdeclarations.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'nbMesDeclaration' => $nbMesDeclaration,
            'declarations' => $declarations,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('/declaration/mesdeclarationsvalidees/{id?0}', name: 'declarationvalider.afficher')]
    public function afficherDeclarationsvalidees(
        Declaration $declaration = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
        NotificationRepository $notificationRepository
    ): Response {

        $roles = 'ROLE_USER';
        $this->denyAccessUnlessGranted($roles, null, 'Vous n\'avez pas la permission d\'accéder à cette page.');
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
             $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
         }
 
         // Récupérer l'ID de la personne à partir de l'utilisateur
        
            if ($user && $user->getPersonne()) {
                $personneId = $user->getPersonne()->getId();
            }
 
         $personne = $entityManager->getRepository(Personne::class)->find($personneId);

        if (!$personne) {
            $this->addFlash('error','Agent introuvable');
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
                $photonaiss = $form->get('fichiernaiss')->getData();
                $photodeces = $form->get('fichierdeces')->getData();

            // Vérifier si une photo a été téléchargée
            if ($photonaiss instanceof UploadedFile) {
                $directory = $this->getParameter('preuve_declaration_directory');
                $declaration->setFichiernaiss($uploaderService->uploadFile($photonaiss, $directory));
            }

            // Vérifier si un acte de décès a été téléchargé
            if ($photodeces instanceof UploadedFile) {
                $directory = $this->getParameter('preuve_declaration_directory');
                $declaration->setFichierdeces($uploaderService->uploadFile($photodeces, $directory));
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
        $nbMesDeclaration = $repository->count([]);
        $declarations = $personne->getDeclaration()->filter(function ($declaration) {
            return $declaration->getStatut() === 'validée';
        });

        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('declaration/mesdeclarationsvalidees.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'nbMesDeclaration' => $nbMesDeclaration,
            'declarations' => $declarations,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('/declaration/mesdeclarationsnonvalidees/{id?0}', name: 'declarationNonvalider.afficher')]
    public function afficherDeclarationsNonvalidees(
        Declaration $declaration = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
        NotificationRepository $notificationRepository
    ): Response {

        $roles = 'ROLE_USER';
        $this->denyAccessUnlessGranted($roles, null, 'Vous n\'avez pas la permission d\'accéder à cette page.');
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
             $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
         }
 
         // Récupérer l'ID de la personne à partir de l'utilisateur
        
            if ($user && $user->getPersonne()) {
                $personneId = $user->getPersonne()->getId();
            }
 
         $personne = $entityManager->getRepository(Personne::class)->find($personneId);

        if (!$personne) {
            $this->addFlash('error','Agent introuvable');
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
                $photonaiss = $form->get('fichiernaiss')->getData();
                $photodeces = $form->get('fichierdeces')->getData();

            // Vérifier si une photo a été téléchargée
            if ($photonaiss instanceof UploadedFile) {
                $directory = $this->getParameter('preuve_declaration_directory');
                $declaration->setFichiernaiss($uploaderService->uploadFile($photonaiss, $directory));
            }

            // Vérifier si un acte de décès a été téléchargé
            if ($photodeces instanceof UploadedFile) {
                $directory = $this->getParameter('preuve_declaration_directory');
                $declaration->setFichierdeces($uploaderService->uploadFile($photodeces, $directory));
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
        $nbMesDeclaration = $repository->count([]);
        $declarations = $personne->getDeclaration()->filter(function ($declaration) {
            return $declaration->getStatut() === 'en attente';
        });

        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('declaration/mesdeclarationsnonvalidees.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'nbMesDeclaration' => $nbMesDeclaration,
            'declarations' => $declarations,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
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
        $roles = 'ROLE_USER';
        $this->denyAccessUnlessGranted($roles, null, 'Vous n\'avez pas la permission d\'accéder à cette page.');
        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
        }
       
        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
        }
        $personne = $doctrine->getRepository(Personne::class)->find($personneId);

        if (!$personne) {
            $this->addFlash('error','Agent introuvable');
        }

        $declaration = $doctrine->getRepository(Declaration::class)->find($declarationId);

        if (!$declaration) {
            $this->addFlash('error',"Déclaration introuvable !");
        }

        if ($declaration->getPersonne() !== $personne) {
            $this->addFlash('error',"Déclaration introuvable !");
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
        $this->addFlash('success','Déclaration supprimée avec succès');
        return $this->redirectToRoute('declaration.afficher', ['personneId' => $personneId]);
    }


    #[Route('/declaration/annuler/{declarationId}', name: 'declaration.annuler')]
    public function annulerDeclaration(
        ManagerRegistry $doctrine,
        Request $request,
        $declarationId
    ): Response {
        $roles = 'ROLE_USER';
        $this->denyAccessUnlessGranted($roles, null, 'Vous n\'avez pas la permission d\'accéder à cette page.');
        $declarationRepository = $doctrine->getRepository(Declaration::class);
        $declaration = $declarationRepository->find($declarationId);

        if (!$declaration) {
            $this->addFlash('error',"Déclaration non trouvé !");
        }

        // Vérifier si l'utilisateur est authentifié
        $user = $this->security->getUser();
        if (!$user) {
            $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
        }

        // Vérifier si l'utilisateur a le droit d'annuler la déclaration
        $personne = $user->getPersonne();
        if (!$personne || $declaration->getPersonne() !== $personne) {
            $this->addFlash('error','Vous n\'êtes pas autorisé à annuler cette déclaration.');
        }

        // Vérifier le statut de la déclaration
        if ($declaration->getStatut() !== Declaration::STATUT_EN_ATTENTE) {
            $this->addFlash('error',"Vous ne pouvez plus annuler cette déclaration");
            return $this->redirectToRoute('declaration.afficher', ['declarationId' => $declarationId]);
        }
        $personne->removeDeclaration($declaration);

        $entityManager = $doctrine->getManager();
        $entityManager->remove($declaration);
        $entityManager->flush();
        $this->addFlash('success','Déclaration supprimée avec succès');
        return $this->redirectToRoute('declaration.afficher', ['declarationId' => $declarationId]);
    }

    #[Route('/declaration/valider/{declarationId}', name: 'declaration.valider')]
    public function validerDeclaration(
        ManagerRegistry $doctrine,
        Request $request,
        $declarationId
    ): Response {
        $roles = 'ROLE_GRH';
        $this->denyAccessUnlessGranted($roles, null, 'Vous n\'avez pas la permission d\'accéder à cette page.');
        $declarationRepository = $doctrine->getRepository(Declaration::class);
        $declaration = $declarationRepository->find($declarationId);

        if (!$declaration) {
            // throw new StatutErrorException('Déclaration non trouvée.',400);
            $this->addFlash('error',"Déclaration non trouvé");
        }

        // Vérifier si l'utilisateur est authentifié
        $user = $this->security->getUser();
        if (!$user) {
            // throw new StatutErrorException('Accès refusé.',400);
            $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
        }

        // Vérifier si l'utilisateur a le droit de valider la déclaration
        $personne = $user->getPersonne();
        if (!$personne || $declaration->getPersonne() !== $personne) {
            $this->addFlash('error','Vous n\'êtes pas autorisé à valider cette déclaration.');
        }

        // Vérifier le statut de la déclaration
        if ($declaration->getStatut() !== Declaration::STATUT_EN_ATTENTE) {
            $this->addFlash('error',"Impossible de valider la déclaration");
            return $this->redirectToRoute('declaration.detail', ['id' => $declaration->getId()]);
        }

        // valider la déclaration en changeant son statut
        $declaration->setStatut(Declaration::STATUT_VALIDEE);

        $entityManager = $doctrine->getManager();
        $entityManager->flush();
        $this->addFlash('success','Déclaration validée avec success');
        return $this->redirectToRoute('declaration.liste', ['declarationId' => $declarationId]);
    }

    #[Route('/declaration/rejeter/{declarationId}', name: 'declaration.rejeter')]
    public function rejeterDeclaration(
        ManagerRegistry $doctrine,
        Request $request,
        $declarationId
    ): Response {
        $roles = 'ROLE_GRH';
        $this->denyAccessUnlessGranted($roles, null, 'Vous n\'avez pas la permission d\'accéder à cette page.');
        $declarationRepository = $doctrine->getRepository(Declaration::class);
        $declaration = $declarationRepository->find($declarationId);

        if (!$declaration) {
            $this->addFlash('error','Déclaration non trouvée.');
        }

        // Vérifier si l'utilisateur est authentifié
        $user = $this->security->getUser();
        if (!$user) {
            // throw new StatutErrorException('Accès refusé.',400);
            $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
        }

        // Vérifier si l'utilisateur a le droit de rejeter la déclaration
        $personne = $user->getPersonne();
        if (!$personne || $declaration->getPersonne() !== $personne) {
            $this->addFlash('error','Vous n\'êtes pas autorisé à rejeter cette déclaration.');
        }

        // Vérifier le statut de la déclaration
        if ($declaration->getStatut() !== Declaration::STATUT_EN_ATTENTE) {
            // throw new StatutErrorException('La déclaration ne peut pas être validée car son statut est différent de "en attente".',400);
            $this->addFlash('error',"Impossible d'annuler la déclaration");
            return $this->redirectToRoute('declaration.detail', ['id' => $declaration->getId()]);
        }

        // rejeter la déclaration en changeant son statut
        $declaration->setStatut(Declaration::STATUT_REJETEE);

        $entityManager = $doctrine->getManager();
        $entityManager->flush();
        $this->addFlash('success','Déclaration rejetée avec succès');
        return $this->redirectToRoute('declaration.liste', ['declarationId' => $declarationId]);
    }
}
