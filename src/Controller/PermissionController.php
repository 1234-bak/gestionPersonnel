<?php

namespace App\Controller;

use App\Entity\Personne;
use App\Entity\Permission;
use App\Form\PermissionType;
use App\Service\UploaderService;
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

    #[Route('/permission/liste', name: 'permission.liste')]
    public function indexAll(UserInterface $user,ManagerRegistry $doctrine): Response
    {

        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }
        $permissions = $doctrine->getRepository(Permission::class)->findAll();

        return $this->render('permission/liste-permission.html.twig', [
            'permissions' => $permissions,
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
            'permissions' => $permissions
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
        if ($permission->getStatut() !== Permission::STATUT_EN_ATTENTE) {
            // throw new StatutErrorException('La déclaration ne peut pas être annulée car son statut est différent de "en attente".',400);
            die('La permission ne peut pas être modifier');
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
            'permissions' => $permissions
        ]);
    }

    #[Route('/permission/{id<\d+>}', name: 'permission.show')]
    public function show(UserInterface $user,Permission $permission = null,EntityManagerInterface $entityManager): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            // throw $this->createAccessDeniedException('Accès refusé.');
            die('accès réfusé');
        }
        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
        }
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);
        if (!$permission) {
            $this->addFlash('error', "La permission n'existe pas");
            return $this->redirectToRoute('permission.afficher');
        }

        return $this->render('permission/show-permission.html.twig', [
            'permission' => $permission,
            'personne' => $personne
        ]);
    }
     
    #[Route('/permission/mespermissions', name: 'permission.afficher')]
    public function afficherPermissions(ManagerRegistry $doctrine,UserInterface $user): Response
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

        $permission = $personne->getPermission();

        return $this->render('permission/mespermissions.html.twig', [
            'personne' => $personne,
            'permissions' => $permission,
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
            throw $this->createAccessDeniedException('Accès refusé.');
        }
       
        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
        }
        $personne = $doctrine->getRepository(Personne::class)->find($personneId);

        if (!$personne) {
            throw $this->createNotFoundException('Personne non trouvée.');
        }

        $permission = $doctrine->getRepository(Permission::class)->find($permissionId);

        if (!$permission) {
            throw $this->createNotFoundException('Déclaration non trouvée.');
        }

        if ($permission->getPersonne() !== $personne) {
            throw $this->createNotFoundException('La permission ne correspond pas à la personne spécifiée.');
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
            throw $this->createAccessDeniedException('Accès refusé.');
        }
        $permission = $doctrine->getRepository(Permission::class)->find($permissionId);

        if (!$permission) {
            throw $this->createNotFoundException('Déclaration non trouvée.');
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
            die('Déclaration non trouvée.');
        }

        // Vérifier si l'utilisateur est authentifié
        $user = $this->security->getUser();
        if (!$user) {
            // throw new AccessDeniedHttpException('Accès refusé.');
            die('Accès refusé.');
        }

        // Vérifier si l'utilisateur a le droit d'annuler la permission
        $personne = $user->getPersonne();
        if (!$personne || $permission->getPersonne() !== $personne) {
            // throw new StatutErrorException('Vous n\'êtes pas autorisé à annuler cette permission.',400);
            die('Vous n\'êtes pas autorisé à annuler cette permission.');
        }

        // Vérifier le statut de la permission
        if ($permission->getStatut() !== Permission::STATUT_EN_ATTENTE) {
            // throw new StatutErrorException('La permission ne peut pas être annulée car son statut est différent de "en attente".',400);
            die('La permission ne peut pas être annulée car son statut est différent de "en attente".');
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
        $permissionRepository = $doctrine->getRepository(Permission::class);
        $permission = $permissionRepository->find($permissionId);

        if (!$permission) {
            // throw new StatutErrorException('Déclaration non trouvée.',400);
            die('Déclaration non trouvée.');
        }

        // Vérifier si l'utilisateur est authentifié
        $user = $this->security->getUser();
        if (!$user) {
            // throw new StatutErrorException('Accès refusé.',400);
            die('Accès refusé.');
        }

        // Vérifier si l'utilisateur a le droit de valider la permission
        $personne = $user->getPersonne();
        if (!$personne || $permission->getPersonne() !== $personne) {
            // throw new StatutErrorException('Vous n\'êtes pas autorisé à annuler cette permission.',400);
            die('Vous n\'êtes pas autorisé à valider cette permission.');
        }

        // Vérifier le statut de la permission
        if ($permission->getStatut() !== Permission::STATUT_EN_ATTENTE) {
            // throw new StatutErrorException('La permission ne peut pas être validée car son statut est différent de "en attente".',400);
            die('La permission ne peut pas être validée car son statut est différent de "en attente".');
        }

        // valider la permission en changeant son statut
        $permission->setStatut(Permission::STATUT_VALIDEE);

        $entityManager = $doctrine->getManager();
        $entityManager->flush();

        return $this->redirectToRoute('permission.liste', ['permissionId' => $permissionId]);
    }

    #[Route('/permission/rejeter/{permissionId}', name: 'permission.rejeter')]
    public function rejeterPermission(
        ManagerRegistry $doctrine,
        Request $request,
        $permissionId
    ): Response {
        $permissionRepository = $doctrine->getRepository(Permission::class);
        $permission = $permissionRepository->find($permissionId);

        if (!$permission) {
            // throw new StatutErrorException('Déclaration non trouvée.',400);
            die('Déclaration non trouvée.');
        }

        // Vérifier si l'utilisateur est authentifié
        $user = $this->security->getUser();
        if (!$user) {
            // throw new StatutErrorException('Accès refusé.',400);
            die('Accès refusé.');
        }

        // Vérifier si l'utilisateur a le droit de rejeter la permission
        $personne = $user->getPersonne();
        if (!$personne || $permission->getPersonne() !== $personne) {
            // throw new StatutErrorException('Vous n\'êtes pas autorisé à annuler cette permission.',400);
            die('Vous n\'êtes pas autorisé à rejeter cette permission.');
        }

        // Vérifier le statut de la permission
        if ($permission->getStatut() !== Permission::STATUT_EN_ATTENTE) {
            // throw new StatutErrorException('La permission ne peut pas être validée car son statut est différent de "en attente".',400);
            die('La permission ne peut pas être validée car son statut est différent de "en attente".');
        }

        // rejeter la permission en changeant son statut
        $permission->setStatut(Permission::STATUT_REJETEE);

        $entityManager = $doctrine->getManager();
        $entityManager->flush();

        return $this->redirectToRoute('permission.liste', ['permissionId' => $permissionId]);
    }
}
