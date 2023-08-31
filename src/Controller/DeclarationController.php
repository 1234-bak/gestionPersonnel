<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Personne;
use App\Entity\FileDeces;
use App\Entity\FileNaiss;
use App\Entity\Declaration;
use App\Form\DeclarationType;
use App\Service\UploaderService;
use App\Exception\StatutErrorException;
use Doctrine\ORM\EntityManagerInterface;

use Doctrine\Persistence\ManagerRegistry;
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
        UserInterface $user, // Ajout de UserInterface comme paramètre
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
        $nbDeclaration = $repository->count([]);
        $declarations = $repository->findAll();


        return $this->render('declaration/liste-declaration.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'nbDeclaration' => $nbDeclaration,
            'declarations' => $declarations,
            'user' => $user
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
            'personne' => $personne,
            'declarations' => $declarations,
            'user' => $user
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
        // Vérifier le statut de la déclaration
        if ($declaration->getStatut() !== Declaration::STATUT_EN_ATTENTE) {
            // throw new StatutErrorException('La déclaration ne peut pas être annulée car son statut est différent de "en attente".',400);
            $this->addFlash('error','Impossible de modififier la déclaration.');
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
            'personne' => $personne,
            'declarations' => $declarations,
            'user' => $user
        ]);
    }

    
    #[Route('/mesdeclaration/{id<\d+>}', name: 'mesdeclaration.detail')]
    public function detailMesDeclarations(UserInterface $user,Declaration $declaration = null): Response
    {
        $roles = 'ROLE_USER';
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
     
    #[Route('/declaration/mesdeclarations/{id?0}', name: 'declaration.afficher')]
    public function afficherDeclarations(
        Declaration $declaration = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user, // Ajout de UserInterface comme paramètre
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


        return $this->render('declaration/mesdeclarations.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'nbMesDeclaration' => $nbMesDeclaration,
            'declarations' => $declarations,
            'user' => $user
        ]);
    }

    #[Route('/declaration/mesdeclarationsvalidees/{id?0}', name: 'declarationvalider.afficher')]
    public function afficherDeclarationsvalidees(
        Declaration $declaration = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user, // Ajout de UserInterface comme paramètre
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
        $declarations = $personne->getPermission()->filter(function ($declaration) {
            return $declaration->getStatut() === 'validée';
        });


        return $this->render('declaration/mesdeclarations.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'nbMesDeclaration' => $nbMesDeclaration,
            'declarations' => $declarations,
            'user' => $user
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
        $roles = 'ROLE_GRH';
        $this->denyAccessUnlessGranted($roles, null, 'Vous n\'avez pas la permission d\'accéder à cette page.');
        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
        }
        $declaration = $doctrine->getRepository(Declaration::class)->find($declarationId);

        if (!$declaration) {
            $this->addFlash('error',"Déclaration introuvable !");
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
        $roles = 'ROLE_USER';
        $this->denyAccessUnlessGranted($roles, null, 'Vous n\'avez pas la permission d\'accéder à cette page.');
        $declarationRepository = $doctrine->getRepository(Declaration::class);
        $declaration = $declarationRepository->find($declarationId);

        if (!$declaration) {
            // throw new StatutErrorException('Déclaration non trouvée.',400);
            $this->addFlash('error',"Déclaration non trouvé !");
        }

        // Vérifier si l'utilisateur est authentifié
        $user = $this->security->getUser();
        if (!$user) {
            // throw new AccessDeniedHttpException('Accès refusé.');
            $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
        }

        // Vérifier si l'utilisateur a le droit d'annuler la déclaration
        $personne = $user->getPersonne();
        if (!$personne || $declaration->getPersonne() !== $personne) {
            // throw new StatutErrorException('Vous n\'êtes pas autorisé à annuler cette déclaration.',400);
            $this->addFlash('error','Vous n\'êtes pas autorisé à annuler cette déclaration.');
        }

        // Vérifier le statut de la déclaration
        if ($declaration->getStatut() !== Declaration::STATUT_EN_ATTENTE) {
            // throw new StatutErrorException('La déclaration ne peut pas être annulée car son statut est différent de "en attente".',400);
            $this->addFlash('error',"Impossible d'ennuler cette déclaration");
            return $this->redirectToRoute('permission.afficher', ['declarationId' => $declarationId]);
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
            // throw new StatutErrorException('Vous n\'êtes pas autorisé à annuler cette déclaration.',400);
            $this->addFlash('error','Vous n\'êtes pas autorisé à valider cette déclaration.');
        }

        // Vérifier le statut de la déclaration
        if ($declaration->getStatut() !== Declaration::STATUT_EN_ATTENTE) {
            // throw new StatutErrorException('La déclaration ne peut pas être validée car son statut est différent de "en attente".',400);
            $this->addFlash('error',"Impossible de valider la déclaration");
            return $this->redirectToRoute('declaration.detail', ['id' => $declaration->getId()]);
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
        $roles = 'ROLE_GRH';
        $this->denyAccessUnlessGranted($roles, null, 'Vous n\'avez pas la permission d\'accéder à cette page.');
        $declarationRepository = $doctrine->getRepository(Declaration::class);
        $declaration = $declarationRepository->find($declarationId);

        if (!$declaration) {
            // throw new StatutErrorException('Déclaration non trouvée.',400);
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
            // throw new StatutErrorException('Vous n\'êtes pas autorisé à annuler cette déclaration.',400);
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

        return $this->redirectToRoute('declaration.liste', ['declarationId' => $declarationId]);
    }
}
