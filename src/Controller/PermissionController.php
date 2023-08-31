<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Personne;
use App\Entity\Permission;
use App\Service\PdfService;
use App\Form\PermissionType;
use App\Service\UploaderService;
use App\Exception\StatutErrorException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PermissionController extends AbstractController
{
    public function __construct(private Security $security)
    {
    }
    #[Route('/permission', name: 'app_permission')]
    public function index(): Response
    {
        return $this->render('permission/index.html.twig', [
            'controller_name' => 'PermissionController',
        ]);
    }

    #[Route('grh/permission/liste/{id?0}', name: 'grh-permission.liste')]
    public function afficherbygrh(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
    ): Response {
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB', 'ROLE_MIN', 'ROLE_DIR', 'ROLE_CS', 'ROLE_SD'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            $this->addFlash('error',"vous n'avez pas la permission d'acceder à cette page");
        }
        
         if (!$user) {
            $this->addFlash('error',"Accès réfusé ! vous devez avoir un compte");
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
        if (!$permission) {
            $permission = new Permission();
            $new = true;
        }

        $form = $this->createForm(PermissionType::class, $permission);
        $form->remove('createdAt');
        $form->remove('updatedAt');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $permission->setStatut("en attente");
            $photo = $form->get('photo')->getData();

            // Vérifie si une photo a été téléchargée
            if ($photo) {
                $directory = $this->getParameter('preuve_permission_directory');
                $permission->setPreuve($uploaderService->uploadFile($photo, $directory));
            }

            if ($new) {
                $personne->addPermission($permission);
            }

            $entityManager->persist($permission);
            $entityManager->flush();

            $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success", $permission->getId() . ' ' . $message);

            return $this->redirectToRoute('permission.afficher');
        }
        $repository = $entityManager->getRepository(Permission::class);
        $nbPermission = $repository->count([]);
        $permissions = $repository->findAll();


        return $this->render('permission/liste-permission-grh.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'nbPermission' => $permission,
            'permissions' => $permissions,
            'user' => $user
        ]);
    }

    #[Route('dir/permission/liste/{id?0}', name: 'dir-permission.liste')]
    public function afficherbydir(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user, // Ajout de UserInterface comme paramètre
    ): Response {
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB', 'ROLE_MIN', 'ROLE_DIR', 'ROLE_CS', 'ROLE_SD'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            $this->addFlash('error',"vous n'avez pas la permission d'acceder à cette page");       
        }
        
        // $this->denyAccessUnlessGranted($role,'Vous n\'avez pas la permission d\'accéder à cette page.');
        // Vérifier si l'utilisateur est authentifié
         if (!$user) {
            $this->addFlash('error',"Accès réfusé ! vous devez avoir un compte");         }
 
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
        if (!$permission) {
            $permission = new Permission();
            $new = true;
        }

        $form = $this->createForm(PermissionType::class, $permission);
        $form->remove('createdAt');
        $form->remove('updatedAt');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $permission->setStatut("en attente");
            $photo = $form->get('photo')->getData();

            // Vérifie si une photo a été téléchargée
            if ($photo) {
                $directory = $this->getParameter('preuve_permission_directory');
                $permission->setPreuve($uploaderService->uploadFile($photo, $directory));
            }

            if ($new) {
                $personne->addPermission($permission);
            }

            $entityManager->persist($permission);
            $entityManager->flush();

            $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success", $permission->getId() . ' ' . $message);

            return $this->redirectToRoute('permission.afficher');
        }
        $repository = $entityManager->getRepository(Permission::class);
        $nbPermission = $repository->count([]);
        $permissions = $repository->findAll();


        return $this->render('permission/liste-permission-dir.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'permission' => $nbPermission,
            'permissions' => $permissions,
            'user' => $user
        ]);
    }
    #[Route('sd/permission/liste/{id?0}', name: 'sd-permission.liste')]
    public function afficherbysd(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user, // Ajout de UserInterface comme paramètre
    ): Response {
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB', 'ROLE_MIN', 'ROLE_DIR', 'ROLE_CS', 'ROLE_SD'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            $this->addFlash('error',"vous n'avez pas la permission d'acceder à cette page");        
        }
        
        // $this->denyAccessUnlessGranted($role,'Vous n\'avez pas la permission d\'accéder à cette page.');
        // Vérifier si l'utilisateur est authentifié
         if (!$user) {
            $this->addFlash('error',"Accès refusé ! vous devez avoir un compte");
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
        if (!$permission) {
            $permission = new Permission();
            $new = true;
        }

        $form = $this->createForm(PermissionType::class, $permission);
        $form->remove('createdAt');
        $form->remove('updatedAt');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $permission->setStatut("en attente");
            $photo = $form->get('photo')->getData();

            // Vérifie si une photo a été téléchargée
            if ($photo) {
                $directory = $this->getParameter('preuve_permission_directory');
                $permission->setPreuve($uploaderService->uploadFile($photo, $directory));
            }

            if ($new) {
                $personne->addPermission($permission);
            }

            $entityManager->persist($permission);
            $entityManager->flush();

            $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success", $permission->getId() . ' ' . $message);

            return $this->redirectToRoute('permission.afficher');
        }
        $repository = $entityManager->getRepository(Permission::class);
        $nbPermission = $repository->count([]);
        $permissions = $repository->findAll();


        return $this->render('permission/liste-permission-sd.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'nbPermission' => $nbPermission,
            'permissions' => $permissions,
            'user' => $user
        ]);
    }

    #[Route('cs/permission/liste/{id?0}', name: 'cs-permission.liste')]
    public function afficherbycs(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user, // Ajout de UserInterface comme paramètre
    ): Response {
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB', 'ROLE_MIN', 'ROLE_DIR', 'ROLE_CS', 'ROLE_SD'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            $this->addFlash('error',"vous n'avez pas la permission d'acceder à cette page");
        }
        
        // $this->denyAccessUnlessGranted($role,'Vous n\'avez pas la permission d\'accéder à cette page.');
        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            $this->addFlash('error',"Accès réfusé ! vous devez avoir un compte");
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
        if (!$permission) {
            $permission = new Permission();
            $new = true;
        }

        $form = $this->createForm(PermissionType::class, $permission);
        $form->remove('createdAt');
        $form->remove('updatedAt');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $permission->setStatut("en attente");
            $photo = $form->get('photo')->getData();

            // Vérifie si une photo a été téléchargée
            if ($photo) {
                $directory = $this->getParameter('preuve_permission_directory');
                $permission->setPreuve($uploaderService->uploadFile($photo, $directory));
            }

            if ($new) {
                $personne->addPermission($permission);
            }

            $entityManager->persist($permission);
            $entityManager->flush();

            $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success", $permission->getId() . ' ' . $message);

            return $this->redirectToRoute('permission.afficher');
        }
        $repository = $entityManager->getRepository(Permission::class);
        $nbPermission = $repository->count([]);
        $permissions = $repository->findAll();


        return $this->render('permission/liste-permission-cs.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'nbPermission' => $nbPermission,
            'permissions' => $permissions,
            'user' => $user
        ]);
    }

    #[Route('dircab/permission/liste/{id?0}', name: 'dircab-permission.liste')]
    public function afficherbydircab(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user, // Ajout de UserInterface comme paramètre
    ): Response {
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB', 'ROLE_MIN', 'ROLE_DIR', 'ROLE_CS', 'ROLE_SD'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            $this->addFlash('error',"vous n'avez pas la permission d'acceder à cette page");
        }
        
        // $this->denyAccessUnlessGranted($role,'Vous n\'avez pas la permission d\'accéder à cette page.');
        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            $this->addFlash('error',"Accès réfusé ! vous devez avoir un compte");
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
        if (!$permission) {
            $permission = new Permission();
            $new = true;
        }

        $form = $this->createForm(PermissionType::class, $permission);
        $form->remove('createdAt');
        $form->remove('updatedAt');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $permission->setStatut("en attente");
            $photo = $form->get('photo')->getData();

            // Vérifie si une photo a été téléchargée
            if ($photo) {
                $directory = $this->getParameter('preuve_permission_directory');
                $permission->setPreuve($uploaderService->uploadFile($photo, $directory));
            }

            if ($new) {
                $personne->addPermission($permission);
            }

            $entityManager->persist($permission);
            $entityManager->flush();

            $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success", $permission->getId() . ' ' . $message);

            return $this->redirectToRoute('permission.afficher');
        }
        $repository = $entityManager->getRepository(Permission::class);
        $nbPermission = $repository->count([]);
        $permissions = $repository->findAll();


        return $this->render('permission/liste-permission-dircab.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'nbPermission' => $nbPermission,
            'permissions' => $permissions,
            'user' => $user
        ]);
    }

  
    #[Route('permission/add/{id?0}', name: 'permission.add')]
    public function add(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user, // Ajout de UserInterface comme paramètre
    ): Response {
         // Récupérer l'utilisateur actuellement connecté


         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
            $this->addFlash('error',"accès réfusé ! vous devez avoir un compte");
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
        if (!$permission) {
            $permission = new Permission();
            $new = true;
        }

        $form = $this->createForm(PermissionType::class, $permission);
        $form->remove('createdAt');
        $form->remove('updatedAt');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $permission->setStatut("en attente");
            $photo = $form->get('photo')->getData();

            // Vérifie si une photo a été téléchargée
            if ($photo) {
                $directory = $this->getParameter('preuve_permission_directory');
                $permission->setPreuve($uploaderService->uploadFile($photo, $directory));
            }

            if ($new) {
                $personne->addPermission($permission);
            }

            $entityManager->persist($permission);
            $entityManager->flush();

            $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success", $permission->getId() . ' ' . $message);

            return $this->redirectToRoute('permission.afficher');
        }
        $repository = $entityManager->getRepository(Permission::class);
        $permissions = $repository->findAll();


        return $this->render('permission/edit-permission.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'permissions' => $permissions,
            'user' => $user           
        ]);
    }


    #[Route('permission/edit/{id?0}', name: 'permission.edit')]
    public function edit(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user, // Ajout de UserInterface comme paramètre
    ): Response {
         // Récupérer l'utilisateur actuellement connecté

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
            $this->addFlash('error',"Agent introuvable");
        }
        // Vérifier le statut de la déclaration
        if ($permission->getStatut() !== Permission::STATUT_EN_ATTENTE) {
            // throw new StatutErrorException('La déclaration ne peut pas être annulée car son statut est différent de "en attente".',400);
            $this->addFlash('error',"Impossible de modifier la demande de permission !");
        }
        $new = false;
        if (!$permission) {
            $permission = new Permission();
            $new = true;
        }

        $form = $this->createForm(PermissionType::class, $permission);
        $form->remove('createdAt');
        $form->remove('updatedAt');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $permission->setStatut("en attente");
            $photo = $form->get('photo')->getData();

            // Vérifie si une photo a été téléchargée
            if ($photo) {
                $directory = $this->getParameter('preuve_permission_directory');
                $permission->setPreuve($uploaderService->uploadFile($photo, $directory));
            }

            if ($new) {
                $personne->addPermission($permission);
            }

            $entityManager->persist($permission);
            $entityManager->flush();

            $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success", $permission->getId() . ' ' . $message);

            return $this->redirectToRoute('permission.afficher');
        }
        $repository = $entityManager->getRepository(Permission::class);
        $permissions = $repository->findAll();


        return $this->render('permission/edit-permission.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'permissions' => $permissions,
            'user' => $user
        ]);
    }

    #[Route('/permission/{id<\d+>}', name: 'permission.show')]
    public function show(UserInterface $user,Permission $permission = null,EntityManagerInterface $entityManager): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            // throw $this->createAccessDeniedException('Accès refusé.');
            $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
        }
        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
        }
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);
        if (!$permission) {
            $this->addFlash('error', "Demande de permission introuvable !");
            return $this->redirectToRoute('permission.afficher');
        }

        return $this->render('permission/doc-permission.html.twig', [
            'permission' => $permission,
            'personne' => $personne,
            'user' => $user
        ]);
    }
     
    #[Route('/mespermission/{id<\d+>}', name: 'mespermission.show')]
    public function showMesPermissions(UserInterface $user,Permission $permission = null,EntityManagerInterface $entityManager): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            // throw $this->createAccessDeniedException('Accès refusé.');
            $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
        }
        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
        }
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);
        if (!$permission) {
            $this->addFlash('error', "La permission n'existe pas");
            return $this->redirectToRoute('permission.afficher');
        }

        return $this->render('permission/doc-mespermissions.html.twig', [
            'permission' => $permission,
            'personne' => $personne,
            'user' => $user
        ]);
    }

    #[Route('/mespermissionvalidees/{id<\d+>}', name: 'mespermissionvalidees.show')]
    public function showPermissionValidee(UserInterface $user,Permission $permission = null,EntityManagerInterface $entityManager,PdfService $pdf): Response
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
        if (!$permission) {
            $this->addFlash('error', "Demande de permission introuvable !");
            return $this->redirectToRoute('permission.afficher');
        }

        $html = $this->render('permission/doc-mespermissionsvalidees.html.twig', [
            'permission' => $permission,
            'personne' => $personne,
            'user' => $user
        ]);
        $pdf->showPdfFile($html);

        return new Response("PDF généré avec succès.");
    }

    #[Route('permission/mesPermissions/{id?0}', name: 'permission.afficher')]
    public function afficherPermissions(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
    ): Response {
         // Récupérer l'utilisateur actuellement connecté


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
        if (!$permission) {
            $permission = new Permission();
            $new = true;
        }

        $form = $this->createForm(PermissionType::class, $permission);
        $form->remove('createdAt');
        $form->remove('updatedAt');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $permission->setStatut("en attente");
            $photo = $form->get('photo')->getData();

            // Vérifie si une photo a été téléchargée
            if ($photo) {
                $directory = $this->getParameter('preuve_permission_directory');
                $permission->setPreuve($uploaderService->uploadFile($photo, $directory));
            }

            if ($new) {
                $personne->addPermission($permission);
            }

            $entityManager->persist($permission);
            $entityManager->flush();

            $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success", $permission->getId() . ' ' . $message);

            return $this->redirectToRoute('permission.afficher');
        }
        $repository = $entityManager->getRepository(Permission::class);
        $nbMesPermission = $repository->count([]);
        $permissions = $personne->getPermission();


        return $this->render('permission/mespermissions.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'nbMesPermission' => $nbMesPermission,
            'permissions' => $permissions,
            'user' => $user
        ]);
    }

    #[Route('permission/mesPermissionsValidees/{id?0}', name: 'permissionsValidees.afficher')]
    public function afficherPermissionsValidees(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
    ): Response {
         // Récupérer l'utilisateur actuellement connecté

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
            $this->addFlash('error',"Agent introuvable");
        }

        $new = false;
        if (!$permission) {
            $permission = new Permission();
            $new = true;
        }

        $form = $this->createForm(PermissionType::class, $permission);
        $form->remove('createdAt');
        $form->remove('updatedAt');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $permission->setStatut("en attente");
            $photo = $form->get('photo')->getData();

            // Vérifie si une photo a été téléchargée
            if ($photo) {
                $directory = $this->getParameter('preuve_permission_directory');
                $permission->setPreuve($uploaderService->uploadFile($photo, $directory));
            }

            if ($new) {
                $personne->addPermission($permission);
            }

            $entityManager->persist($permission);
            $entityManager->flush();

            $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success", $permission->getId() . ' ' . $message);

            return $this->redirectToRoute('permission.afficher');
        }
        $repository = $entityManager->getRepository(Permission::class);
        $nbMesPermissionValidees = $repository->count([]);
        $permissions = $personne->getPermission()->filter(function ($permission) {
            return $permission->getStatut() === 'validée';
        });
        

        return $this->render('permission/mespermissionsvalidees.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'nbMesPermissionValidees' => $nbMesPermissionValidees,
            'permissions' => $permissions,
            'user' => $user
        ]);
    }

    #[Route('/permission/supprimer/{personneId}/{permissionId}', name: 'permission.supprimer')]
    public function supprimerPermission(
        ManagerRegistry $doctrine,
        Request $request,
        $personneId,
        $permissionId,
        UserInterface $user
    ): Response {

        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
        }
       
        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
        }
        $personne = $doctrine->getRepository(Personne::class)->find($personneId);

        if (!$personne) {
            $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
        }

        $permission = $doctrine->getRepository(Permission::class)->find($permissionId);

        if (!$permission) {
            $this->addFlash('error',"Demande de permission introuvable !");
        }

        if ($permission->getPersonne() !== $personne) {
            $this->addFlash('error','Demande de permission introuvable !');
        }

        if ($permission->getStatut() === 'en attente') {
            $personne->removePermission($permission);

            $entityManager = $doctrine->getManager();
            $entityManager->remove($permission);
            $entityManager->flush();
        } else {
            // Gérer le cas où le statut n'est pas "annulé", par exemple afficher un message d'erreur ou rediriger vers une autre page
            return $this->redirectToRoute('permission.detail', ['id' => $permissionId]);
        }

        return $this->redirectToRoute('permission.afficher', ['personneId' => $personneId]);
    }

    #[Route('/permission/changer-statut/{permissionId}/{nouveauStatut}', name: 'permission.changer_statut')]
    public function changerStatutPermission(
        UserInterface $user,
        ManagerRegistry $doctrine,
        Request $request,
        $permissionId,
        $nouveauStatut
    ): Response {

        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
        }
        $permission = $doctrine->getRepository(Permission::class)->find($permissionId);

        if (!$permission) {
            $this->addFlash('error',"Demande de permission introuvable");
        }

        // Modifier le statut de la permission
        $permission->setStatut($nouveauStatut);

        $entityManager = $doctrine->getManager();
        $entityManager->flush();

        return $this->redirectToRoute('permission.detail', ['id' => $permissionId]);
    }
    
    #[Route('/permission/annuler/{permissionId}', name: 'permission.annuler')]
    public function annulerPermission(
        ManagerRegistry $doctrine,
        Request $request,
        $permissionId
    ): Response {
        $permissionRepository = $doctrine->getRepository(Permission::class);
        $permission = $permissionRepository->find($permissionId);

        if (!$permission) {
            // throw new StatutErrorException('Déclaration non trouvée.',400);
            $this->addFlash('error',"Demande de permission introuvable");
        }

        // Vérifier si l'utilisateur est authentifié
        $user = $this->security->getUser();
        if (!$user) {
            // throw new AccessDeniedHttpException('Accès refusé.');
            $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
        }

        // Vérifier si l'utilisateur a le droit d'annuler la permission
        $personne = $user->getPersonne();
        if (!$personne || $permission->getPersonne() !== $personne) {
            // throw new StatutErrorException('Vous n\'êtes pas autorisé à annuler cette permission.',400);
            $this->addFlash('error','Vous n\'êtes pas autorisé à annuler cette permission.');
        }
        // Vérifier le statut de la permission
        if ($permission->getStatut() !== Permission::STATUT_EN_ATTENTE) {
            $this->addFlash('error',"Impossible d'annuler cette demande de permission");
            return $this->redirectToRoute('permission.afficher', ['permissionId' => $permissionId]);
        }
        
        $personne->removePermission($permission);

        $entityManager = $doctrine->getManager();
        $entityManager->remove($permission);
        $entityManager->flush();

        return $this->redirectToRoute('permission.afficher', ['permissionId' => $permissionId]);


    }

    #[Route('/permission/valider/{permissionId}', name: 'permission.valider')]
    public function validerPermission(
        ManagerRegistry $doctrine,
        Request $request,
        $permissionId
    ): Response {
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB','ROLE_DIR', 'ROLE_SD','ROLE_CS','ROLE_MIN'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }
        $permissionRepository = $doctrine->getRepository(Permission::class);
        $permission = $permissionRepository->find($permissionId);

        if (!$permission) {
            // throw new StatutErrorException('Déclaration non trouvée.',400);
            $this->addFlash('error',"Demande de permission introuvable !");
        }

        // Vérifier si l'utilisateur est authentifié
        $user = $this->security->getUser();
        if (!$user) {
            // throw new StatutErrorException('Accès refusé.',400);
            $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
        }

        // Vérifier si l'utilisateur a le droit de valider la permission
        $personne = $user->getPersonne();
        if (!$personne || $permission->getPersonne() !== $personne) {
            // throw new StatutErrorException('Vous n\'êtes pas autorisé à annuler cette permission.',400);
            $this->addFlash('error','Vous n\'êtes pas autorisé à valider cette permission.');
        }

        // Vérifier le statut de la permission
        if ($permission->getStatut() !== Permission::STATUT_EN_ATTENTE) {
            // throw new StatutErrorException('La permission ne peut pas être validée car son statut est différent de "en attente".',400);
            $this->addFlash('error',"Impossible de valider cette demande de permission");
            return $this->redirectToRoute('permission.show', ['id' => $permission->getId()]);

        }

        // valider la permission en changeant son statut
        $permission->setStatut(Permission::STATUT_VALIDEE);

        $entityManager = $doctrine->getManager();
        $entityManager->flush();

        // Redirection vers la route spécifique en fonction du rôle
        if ($this->isGranted('ROLE_GRH')) {
            return $this->redirectToRoute('grh-permission.liste');
        } elseif ($this->isGranted('ROLE_DIRCAB')) {
            return $this->redirectToRoute('dircab-permission.liste');
        }elseif ($this->isGranted('ROLE_DIR')) {
            return $this->redirectToRoute('dir-permission.liste');
        }elseif ($this->isGranted('ROLE_SD')) {
            return $this->redirectToRoute('sd-permission.liste');
        }elseif ($this->isGranted('ROLE_CS')) {
            return $this->redirectToRoute('cs-permission.liste');
        }elseif ($this->isGranted('ROLE_MIN')) {
            return $this->redirectToRoute('ministre-permission.liste');
        }
    }

    #[Route('/permission/rejeter/{permissionId}', name: 'permission.rejeter')]
    public function rejeterPermission(
        ManagerRegistry $doctrine,
        Request $request,
        $permissionId
    ): Response {
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB','ROLE_DIR', 'ROLE_SD','ROLE_CS','ROLE_MIN'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            return $this->redirectToRoute('permission.afficher');
        }
        $permissionRepository = $doctrine->getRepository(Permission::class);
        $permission = $permissionRepository->find($permissionId);

        if (!$permission) {
            // throw new StatutErrorException('Déclaration non trouvée.',400);
            $this->addFlash('error',"Demande de permission introuvable !");
        }

        // Vérifier si l'utilisateur est authentifié
        $user = $this->security->getUser();
        if (!$user) {
            // throw new StatutErrorException('Accès refusé.',400);
            $this->addFlash('error',"Accès réfusé ! Vous devez avoir un compte");
        }

        // Vérifier si l'utilisateur a le droit de rejeter la permission
        $personne = $user->getPersonne();
        if (!$personne || $permission->getPersonne() !== $personne) {
            // throw new StatutErrorException('Vous n\'êtes pas autorisé à annuler cette permission.',400);
            $this->addFlash('error','Vous n\'êtes pas autorisé à rejeter cette permission.');
        }

        // Vérifier le statut de la permission
        if ($permission->getStatut() !== Permission::STATUT_EN_ATTENTE) {
            // throw new StatutErrorException('La permission ne peut pas être validée car son statut est différent de "en attente".',400);
            $this->addFlash('error',"Impossible de rejeter cette demande de permission");
            return $this->redirectToRoute('permission.show', ['id' => $permission->getId()]);
        }

        // rejeter la permission en changeant son statut
        $permission->setStatut(Permission::STATUT_REJETEE);

        $entityManager = $doctrine->getManager();
        $entityManager->flush();

        if ($this->isGranted('ROLE_GRH')) {
            return $this->redirectToRoute('grh-permission.liste');
        } elseif ($this->isGranted('ROLE_DIRCAB')) {
            return $this->redirectToRoute('dircab-permission.liste');
        }elseif ($this->isGranted('ROLE_DIR')) {
            return $this->redirectToRoute('dir-permission.liste');
        }elseif ($this->isGranted('ROLE_SD')) {
            return $this->redirectToRoute('sd-permission.liste');
        }elseif ($this->isGranted('ROLE_CS')) {
            return $this->redirectToRoute('cs-permission.liste');
        }elseif ($this->isGranted('ROLE_MIN')) {
            return $this->redirectToRoute('ministre-permission.liste');
        }
    }

}
