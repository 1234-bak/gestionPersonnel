<?php

namespace App\Controller;

use Knp\Snappy\Pdf;
use App\Entity\User;
use App\Entity\Personne;
use App\Entity\Permission;
use App\Service\PdfService;
use App\Entity\Notification;
use App\Form\PermissionType;
use App\Service\UploaderService;
use App\Exception\StatutErrorException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\NotificationRepository;
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

    #[Route('permissiongrh/liste/{id?0}', name: 'grh-permission.liste')]
    public function afficherbygrh(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
        NotificationRepository $notificationRepository
    ): Response {
        $roles = ['ROLE_GRH','ROLE_DIR', 'ROLE_CS', 'ROLE_SD'];

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

        $repository = $entityManager->getRepository(Permission::class);
        $permissions = $repository->createQueryBuilder('p')
        ->where('p.delai > :delai')
        ->setParameter('delai', 3)
        ->orderBy('p.datedebut', 'DESC')
        ->getQuery()
        ->getResult();
        $nbPermission = count($permissions);
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('permission/liste-permission-grh.html.twig', [
            'personne' => $personne,
            'nbPermission' => $permission,
            'permissions' => $permissions,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('permissiondir/liste/{id?0}', name: 'dir-permission.liste')]
    public function afficherbydir(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        UserInterface $user,
        NotificationRepository $notificationRepository
    ): Response {
        $roles = ['ROLE_GRH','ROLE_DIR', 'ROLE_CS', 'ROLE_SD'];

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
 
         $personne = $entityManager->getRepository(Personne::class)->find($personneId);

         $repository = $entityManager->getRepository(Permission::class);
         $qb = $repository->createQueryBuilder('p')
             ->innerJoin('p.personne', 'per')
             ->innerJoin('per.fonction', 'fonc')
             ->where('fonc.designation = :designationAgent OR fonc.designation = :designationChefService OR fonc.designation = :designationSD')
             ->setParameters([
                 'designationAgent' => 'Agent', 
                 'designationChefService' => 'Chef de service',
                 'designationSD' => 'Sous-directeur',
             ]);
     
         $permissions = $qb->orderBy('p.datedebut', 'DESC')
             ->getQuery()
             ->getResult();
    
        $nbPermission = count($permissions);
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('permission/liste-permission-dir.html.twig', [
            'personne' => $personne,
            'nbPermission' => $nbPermission,
            'permissions' => $permissions,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('permissiondrh/liste/{id?0}', name: 'drh-permission.liste')]
    public function afficherbyDRH(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        UserInterface $user,
        NotificationRepository $notificationRepository
    ): Response {
        $roles = ['ROLE_GRH','ROLE_DIR', 'ROLE_CS', 'ROLE_SD'];

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
 
         $personne = $entityManager->getRepository(Personne::class)->find($personneId);

         $repository = $entityManager->getRepository(Permission::class);
         $qb = $repository->createQueryBuilder('p')
             ->innerJoin('p.personne', 'per')
             ->innerJoin('per.fonction', 'fonc')
             ->where('fonc.designation = :designationAgent OR fonc.designation = :designationChefService OR fonc.designation = :designationSD')
             ->setParameters([
                 'designationAgent' => 'Agent', 
                 'designationChefService' => 'Chef de service',
                 'designationSD' => 'Sous-directeur',
             ]);
     
         $permissions = $qb->orderBy('p.datedebut', 'DESC')
             ->getQuery()
             ->getResult();
    
        $nbPermission = count($permissions);
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('permission/liste-permission-drh.html.twig', [
            'personne' => $personne,
            'nbPermission' => $nbPermission,
            'permissions' => $permissions,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('permissionsd/liste/{id?0}', name: 'sd-permission.liste')]
    public function afficherbysd(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
        NotificationRepository $notificationRepository
    ): Response {
        $roles = ['ROLE_GRH','ROLE_DIR', 'ROLE_CS', 'ROLE_SD'];

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

         $repository = $entityManager->getRepository(Permission::class);
         $qb = $repository->createQueryBuilder('p')
             ->innerJoin('p.personne', 'per')
             ->innerJoin('per.fonction', 'fonc')
             ->where('fonc.designation = :designationAgent OR fonc.designation = :designationChefService')
             ->setParameters([
                 'designationAgent' => 'Agent', 
                 'designationChefService' => 'Chef de service',
             ]);
     
         $permissions = $qb->orderBy('p.datedebut', 'DESC')
             ->getQuery()
             ->getResult();
     
         $nbPermission = count($permissions);
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('permission/liste-permission-sd.html.twig', [
            'personne' => $personne,
            'nbPermission' => $nbPermission,
            'permissions' => $permissions,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('permissioncs/liste/{id?0}', name: 'cs-permission.liste')]
    public function afficherbycs(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
        NotificationRepository $notificationRepository
    ): Response {
        $roles = ['ROLE_GRH','ROLE_DIR', 'ROLE_CS', 'ROLE_SD'];

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

        
         $repository = $entityManager->getRepository(Permission::class);
         $qb = $repository->createQueryBuilder('p')
             ->innerJoin('p.personne', 'per')
             ->innerJoin('per.fonction', 'fonc')
             ->where('fonc.designation = :designationAgent')
             ->setParameters([
                 'designationAgent' => 'Agent', 
             ]);
     
         $permissions = $qb->orderBy('p.datedebut', 'DESC')
             ->getQuery()
             ->getResult();
        $nbPermission = count($permissions);
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('permission/liste-permission-cs.html.twig', [
            'personne' => $personne,
            'nbPermission' => $nbPermission,
            'permissions' => $permissions,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('permissiondircab/liste/{id?0}', name: 'dircab-permission.liste')]
    public function afficherbydircab(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
        NotificationRepository $notificationRepository
    ): Response {
        $roles = ['ROLE_GRH','ROLE_DIR', 'ROLE_CS', 'ROLE_SD','ROLE_DIRCAB'];

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
         
            if ($user && $user->getPersonne()) {
                $personneId = $user->getPersonne()->getId();
            }
 
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);

        $repository = $entityManager->getRepository(Permission::class);
        $qb = $repository->createQueryBuilder('p')
            ->innerJoin('p.personne', 'per')
            ->innerJoin('per.fonction', 'fonc')
            ->where('fonc.designation = :designation')
            ->setParameter('designation', 'Directeur'); // Remplacez 'Directeur' par la désignation recherchée

        $permissions = $qb->orderBy('p.datedebut', 'DESC')
            ->getQuery()
            ->getResult();
        $nbPermission = count($permissions);
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('permission/liste-permission-dircab.html.twig', [
            'personne' => $personne,
            'nbPermission' => $nbPermission,
            'permissions' => $permissions,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

  
    #[Route('permission/add/{id?0}', name: 'permission.add')]
    public function add(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user, 
        NotificationRepository $notificationRepository
    ): Response {

        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
        $this->addFlash('error',"accès réfusé ! vous devez avoir un compte");
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
        if (!$permission) {
            $permission = new Permission();
            $new = true;
        }

        $form = $this->createForm(PermissionType::class, $permission);
        $form->remove('createdAt');
        $form->remove('updatedAt');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $permission->calculerEtDefinirDelai();
            $permission->setStatut("en attente");
            $permission->setEtatdircab("en attente");
            $permission->setEtatdir("en attente");
            $permission->setEtatsd("en attente");
            $permission->setEtatcs("en attente");
            $photo = $form->get('photo')->getData();

            // Vérifie si une photo a été téléchargée
            if ($photo) {
                $directory = $this->getParameter('preuve_permission_directory');
                $permission->setPreuve($uploaderService->uploadFile($photo, $directory));
            }

            if ($new) {
                $personne->addPermission($permission);
            }

            if ($permission->getDuree() == "02 jours" && $permission->getDelai() > 2) {
                $this->addFlash('error', 'La durée de la permission ne peut pas dépasser la durée maximale autorisée.');
                return $this->redirectToRoute('permission.add');
            }
            if ($permission->getDuree() == "03 jours" && $permission->getDelai() > 3) {
                $this->addFlash('error', 'La durée de la permission ne peut pas dépasser la durée maximale autorisée.');
                return $this->redirectToRoute('permission.add');

            }
            if($permission->getDuree() == "05 jours" && $permission->getDelai() > 5) {
                $this->addFlash('error', 'La durée de la permission ne peut pas dépasser la durée maximale autorisée.');
                return $this->redirectToRoute('permission.add');
            }

            $fonction = $personne->getFonction();

            // Initialiser un tableau pour stocker les désignations de fonctions spécifiques
            $fonctionsSpecifiques = ["Directeur", "Sous-directeur", "Chef de service"];

            // Vérifier si la fonction de la personne correspond à l'une des fonctions spécifiques
            if ($fonction && in_array($fonction->getDesignation(), $fonctionsSpecifiques)) {
                // Créer et envoyer une notification à la personne
                $notification = new Notification();
                $notification->setMessage('Une nouvelle demande de permission a été envoyée.');
                $notification->addDestinataire($personne);
                $notification->setDateenvoi(new \DateTime());
                $entityManager->persist($notification);
            }

            $entityManager->persist($permission);
            $entityManager->flush();

            $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success",'Votre demande ' . $message);

            if ($permission->getDelai() <= 3) {
                return $this->redirectToRoute('permissionCD.afficher');
            }else {
                return $this->redirectToRoute('permissionLD.afficher');
            }
        }
        $repository = $entityManager->getRepository(Permission::class);
        $permissions = $repository->findAll();
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('permission/edit-permission.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'permissions' => $permissions,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif         
        ]);
    }

    #[Route('permission/edit/{id?0}', name: 'permission.edit')]
    public function edit(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
        NotificationRepository $notificationRepository
    ): Response {
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
            $permission->calculerEtDefinirDelai();
            $permission->setStatut("en attente");
            $permission->setEtatdircab("en attente");
            $permission->setEtatdir("en attente");
            $permission->setEtatsd("en attente");
            $permission->setEtatcs("en attente");
            $photo = $form->get('photo')->getData();

            // Vérifie si une photo a été téléchargée
            if ($photo) {
                $directory = $this->getParameter('preuve_permission_directory');
                $permission->setPreuve($uploaderService->uploadFile($photo, $directory));
            }

            if ($new) {
                $personne->addPermission($permission);
            }

            if ($permission->getDuree() == "02 jours" && $permission->getDelai() > 2) {
                $this->addFlash('error', 'La durée de la permission ne peut pas dépasser la durée maximale autorisée.');
                
                return $this->redirectToRoute('permission.edit',['permissionId'=> $permission->getId()]);
            }

            if ($permission->getDuree() == "03 jours" && $permission->getDelai() > 3) {
                $this->addFlash('error', 'La durée de la permission ne peut pas dépasser la durée maximale autorisée.');
                
                return $this->redirectToRoute('permission.edit',['permissionId'=> $permission->getId()]);

            }

            if($permission->getDuree() == "05 jours" && $permission->getDelai() > 5) {
                $this->addFlash('error', 'La durée de la permission ne peut pas dépasser la durée maximale autorisée.');
                
                return $this->redirectToRoute('permission.edit',['permissionId'=> $permission->getId()]);
            }

            if($permission->getDuree() == "05 jours" && $permission->getTypepermission() == "autres") {
                $this->addFlash('error', 'La durée de votre permission ne doit pas dépasser 05 jours.');
                return $this->redirectToRoute('permission.edit',['permissionId'=> $permission->getId()]);
            }
            
            $fonction = $personne->getFonction();

            // Initialiser un tableau pour stocker les désignations de fonctions spécifiques
            $fonctionsSpecifiques = ["Directeur", "Sous-directeur", "Chef de service"];

            // Vérifier si la fonction de la personne correspond à l'une des fonctions spécifiques
            if ($fonction && in_array($fonction->getDesignation(), $fonctionsSpecifiques)) {
                // Créer et envoyer une notification à la personne
                $notification = new Notification();
                $notification->setMessage('Une nouvelle demande de permission a été envoyée.');
                $notification->addDestinataire($personne);
                $notification->setDateenvoi(new \DateTime());
                $entityManager->persist($notification);
            }

    
            $entityManager->persist($permission);
            $entityManager->flush();

            $message = $new ? "a été envoyé avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success", 'Votre demande ' . $message);

            if ($permission->getDelai() <= 3) {
                return $this->redirectToRoute('permissionCD.afficher');
            }else {
                return $this->redirectToRoute('permissionLD.afficher');
            }
        }
        //Vérifier le statut de la demande
        if ($permission->getStatut() !== Permission::STATUT_EN_ATTENTE && $permission->getEtatcs() !== Permission::STATUT_EN_ATTENTE && $permission->getEtatsd() !== Permission::STATUT_EN_ATTENTE && $permission->getEtatdir()) {
            $this->addFlash('error',"Vous ne pouvez plus modifier cette demande de permission");
            return $this->redirectToRoute('permissionCD.afficher', ['permissionId' => $permission]);
        }
        $repository = $entityManager->getRepository(Permission::class);
        $permissions = $repository->findAll();
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('permission/edit-permission.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'permissions' => $permissions,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
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
     
    #[Route('/mespermission/show/{id<\d+>}', name: 'mespermission.show')]
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

    #[Route('/mespermissionvalidees/show/{id<\d+>}', name: 'mespermissionvalidees.show')]
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

    #[Route('permission/mesPermissionsCourteDurée/liste/{id?0}', name: 'permissionCD.afficher')]
    public function afficherPermissionsCD(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
        NotificationRepository $notificationRepository
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
            $permission->calculerEtDefinirDelai();
            $permission->setStatut("en attente");
            $permission->setEtatdircab("en attente");
            $permission->setEtatdir("en attente");
            $permission->setEtatsd("en attente");
            $permission->setEtatcs("en attente");
            $photo = $form->get('photo')->getData();

            // Vérifie si une photo a été téléchargée
            if ($photo) {
                $directory = $this->getParameter('preuve_permission_directory');
                $permission->setPreuve($uploaderService->uploadFile($photo, $directory));
            }

            if ($new) {
                $personne->addPermission($permission);
            }

            if ($permission->getDuree() == "02 jours" && $permission->getDelai() > 2) {
                $this->addFlash('error', 'La durée de la permission ne peut pas dépasser la durée maximale autorisée.');
                return $this->redirectToRoute('permissionCD.afficher');
            }
            if ($permission->getDuree() == "03 jours" && $permission->getDelai() > 3) {
                $this->addFlash('error', 'La durée de la permission ne peut pas dépasser la durée maximale autorisée.');
                return $this->redirectToRoute('permissionCD.afficher');

            }
            if($permission->getDuree() == "05 jours" && $permission->getDelai() > 5) {
                $this->addFlash('error', 'La durée de la permission ne peut pas dépasser la durée maximale autorisée.');
                return $this->redirectToRoute('permissionCD.afficher');
            }

            if($permission->getDuree() == "05 jours" && $permission->getTypepermission() == "autres") {
                $this->addFlash('error', 'La durée de votre permission ne doit pas dépasser 05 jours.');
                return $this->redirectToRoute('permissionCD.afficher');
            }

            $entityManager->persist($permission);
            $entityManager->flush();

            if ($permission->getDelai() <= 3) {
                $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
                $this->addFlash("success", $permission->getId() . ' ' . $message);
                return $this->redirectToRoute('permissionCD.afficher');
            }else {
                $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
                $this->addFlash("success", $permission->getId() . ' ' . $message);
                return $this->redirectToRoute('permissionLD.afficher');
            }
        }
        $repository = $entityManager->getRepository(Permission::class);
        $nbMesPermission = $repository->count([]);
        $permissions = $personne->getPermission()->filter(function ($permission) {
            return $permission->getDelai() <= 3;
        });
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('permission/mespermissionsCD.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'nbMesPermission' => $nbMesPermission,
            'permissions' => $permissions,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('permission/mesPermissionsLongueDurée/liste/{id?0}', name: 'permissionLD.afficher')]
    public function afficherPermissionsLD(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
        NotificationRepository $notificationRepository
    ): Response {

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
            $permission->calculerEtDefinirDelai();
            $permission->setStatut("en attente");
            $permission->setEtatdircab("en attente");
            $permission->setEtatdir("en attente");
            $permission->setEtatsd("en attente");
            $permission->setEtatcs("en attente");
            $photo = $form->get('photo')->getData();

            // Vérifie si une photo a été téléchargée
            if ($photo) {
                $directory = $this->getParameter('preuve_permission_directory');
                $permission->setPreuve($uploaderService->uploadFile($photo, $directory));
            }

            if ($new) {
                $personne->addPermission($permission);
            }

            if ($permission->getDuree() == "02 jours" && $permission->getDelai() > 2) {
                $this->addFlash('error', 'La durée de la permission ne peut pas dépasser la durée maximale autorisée.');
                return $this->redirectToRoute('permissionLD.afficher');
            }
            if ($permission->getDuree() == "03 jours" && $permission->getDelai() > 3) {
                $this->addFlash('error', 'La durée de la permission ne peut pas dépasser la durée maximale autorisée.');
                return $this->redirectToRoute('permissionLD.afficher');

            }
            if($permission->getDuree() == "05 jours" && $permission->getDelai() > 5) {
                $this->addFlash('error', 'La durée de la permission ne peut pas dépasser la durée maximale autorisée.');
                return $this->redirectToRoute('permissionLD.afficher');
            }

            if($permission->getDuree() == "05 jours" && $permission->getTypepermission() == "autres") {
                $this->addFlash('error', 'La durée de votre permission ne doit pas dépasser 05 jours.');
                return $this->redirectToRoute('permissionLD.afficher');
            }

            $entityManager->persist($permission);
            $entityManager->flush();

            if ($permission->getDelai() <= 3) {
                $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
                $this->addFlash("success", $permission->getId() . ' ' . $message);
                return $this->redirectToRoute('permissionCD.afficher');
            }else {
                $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
                $this->addFlash("success", $permission->getId() . ' ' . $message);
                return $this->redirectToRoute('permissionLD.afficher');
            }
        }
        $repository = $entityManager->getRepository(Permission::class);
        $nbMesPermission = $repository->count([]);
        $permissions = $personne->getPermission()->filter(function ($permission) {
            return $permission->getDelai() > 3;
        });
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('permission/mespermissionsLD.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'nbMesPermission' => $nbMesPermission,
            'permissions' => $permissions,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('permission/mesPermissionsValidees/liste/{id?0}', name: 'permissionsValidees.afficher')]
    public function afficherPermissionsValidees(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
        NotificationRepository $notificationRepository
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
            $permission->calculerEtDefinirDelai();
            $permission->setStatut("en attente");
            $permission->setEtatdircab("en attente");
            $permission->setEtatdir("en attente");
            $permission->setEtatsd("en attente");
            $permission->setEtatcs("en attente");
            $photo = $form->get('photo')->getData();

            // Vérifie si une photo a été téléchargée
            if ($photo) {
                $directory = $this->getParameter('preuve_permission_directory');
                $permission->setPreuve($uploaderService->uploadFile($photo, $directory));
            }

            if ($new) {
                $personne->addPermission($permission);
            }

            if ($permission->getDuree() == "02 jours" && $permission->getDelai() > 2) {
                $this->addFlash('error', 'La durée de la permission ne peut pas dépasser la durée maximale autorisée.');
                return $this->redirectToRoute('permissionsValidees.afficher');
            }
            if ($permission->getDuree() == "03 jours" && $permission->getDelai() > 3) {
                $this->addFlash('error', 'La durée de la permission ne peut pas dépasser la durée maximale autorisée.');
                return $this->redirectToRoute('permissionsValidees.afficher');

            }
            if($permission->getDuree() == "05 jours" && $permission->getDelai() > 5) {
                $this->addFlash('error', 'La durée de la permission ne peut pas dépasser la durée maximale autorisée.');
                return $this->redirectToRoute('permissionsValidees.afficher');
            }

            if($permission->getDuree() == "05 jours" && $permission->getTypepermission() == "autres") {
                $this->addFlash('error', 'La durée de votre permission ne doit pas dépasser 05 jours.');
                return $this->redirectToRoute('permissionLD.afficher');
            }
            
            $entityManager->persist($permission);
            $entityManager->flush();
            if ($permission->getDelai() <= 3) {
                $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
                $this->addFlash("success", $permission->getId() . ' ' . $message);
                return $this->redirectToRoute('permissionCD.afficher');
            }else {
                $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
                $this->addFlash("success", $permission->getId() . ' ' . $message);
                return $this->redirectToRoute('permissionLD.afficher');
            }

        }
        $repository = $entityManager->getRepository(Permission::class);
        $nbMesPermissionValidees = $repository->count([]);
        $permissions = $personne->getPermission()->filter(function ($permission) {
            return $permission->getEtatcs() === 'validée' && $permission->getEtatsd() === 'validée' && $permission->getEtatdir() === 'validée';
        });
        
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('permission/mespermissionsvalidees.html.twig', [
            'form' => $form->createView(),
            'personne' => $personne,
            'nbMesPermissionValidees' => $nbMesPermissionValidees,
            'permissions' => $permissions,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('permission/grhPermissionsNonValidees/{id?0}', name: 'grh-permissionsNonValidees.afficher')]
    public function afficherGRHPermissionsNonValidees(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
        NotificationRepository $notificationRepository
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
 
         $personne = $entityManager->getRepository(Personne::class)->find($personneId);

        
         $repository = $entityManager->getRepository(Permission::class);
         $qb = $repository->createQueryBuilder('p')
             ->where('p.statut = :statut')
             ->andWhere('p.delai > :delaiMax')
             ->setParameters([
                 'statut' => 'en attente',
                 'delaiMax' => 3,
             ]);
         
         $permissions = $qb->orderBy('p.datedebut', 'DESC')
             ->getQuery()
             ->getResult();
         
 
        $nbPermissionValidees = count($permissions);
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('permission/grh-mespermissionsnonvalidees.html.twig', [
            'personne' => $personne,
            'nbMesPermissionValidees' => $nbPermissionValidees,
            'permissions' => $permissions,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('permission/dirPermissionsNonValidees/{id?0}', name: 'dir-permissionsNonValidees.afficher')]
    public function afficherDirPermissionsNonValidees(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
        NotificationRepository $notificationRepository
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

        $repository = $entityManager->getRepository(Permission::class);
        $qb = $repository->createQueryBuilder('p')
            ->innerJoin('p.personne', 'per')
            ->innerJoin('per.fonction', 'fonc')
            ->where('fonc.designation = :designationAgent OR fonc.designation = :designationChefService OR fonc.designation = :designationSD')
            ->andWhere('p.etatdir = :etatdir')
            ->setParameters([
                'designationAgent' => 'Agent', 
                'designationChefService' => 'Chef de service',
                'designationSD' => 'Sous-directeur',
                'etatdir' => 'en attente',
            ]);
    
        $permissions = $qb->orderBy('p.datedebut', 'DESC')
            ->getQuery()
            ->getResult();
 
         $nbPermissionValidees = count($permissions);
         $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
         $nbNotif = count($notifications);
        return $this->render('permission/dir-mespermissionsnonvalidees.html.twig', [
            'personne' => $personne,
            'nbMesPermissionValidees' => $nbPermissionValidees,
            'permissions' => $permissions,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('permission/drhPermissionsNonValidees/{id?0}', name: 'drh-permissionsNonValidees.afficher')]
    public function afficherDRHPermissionsNonValidees(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
        NotificationRepository $notificationRepository
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

        $repository = $entityManager->getRepository(Permission::class);
        $qb = $repository->createQueryBuilder('p')
            ->innerJoin('p.personne', 'per')
            ->innerJoin('per.fonction', 'fonc')
            ->where('fonc.designation = :designationAgent OR fonc.designation = :designationChefService OR fonc.designation = :designationSD')
            ->andWhere('p.etatdir = :etatdir')
            ->setParameters([
                'designationAgent' => 'Agent', 
                'designationChefService' => 'Chef de service',
                'designationSD' => 'Sous-directeur',
                'etatdir' => 'en attente',
            ]);
    
        $permissions = $qb->orderBy('p.datedebut', 'DESC')
            ->getQuery()
            ->getResult();
 
         $nbPermissionValidees = count($permissions);
         $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
         $nbNotif = count($notifications);
        return $this->render('permission/drh-mespermissionsnonvalidees.html.twig', [
            'personne' => $personne,
            'nbMesPermissionValidees' => $nbPermissionValidees,
            'permissions' => $permissions,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('permission/dircabPermissionsNonValidees/{id?0}', name: 'dircab-permissionsNonValidees.afficher')]
    public function afficherPermissionsDIRCABNonValidees(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
        NotificationRepository $notificationRepository
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

        $repository = $entityManager->getRepository(Permission::class);
        $qb = $repository->createQueryBuilder('p')
            ->innerJoin('p.personne', 'per')
            ->innerJoin('per.fonction', 'fonc')
            ->where('fonc.designation = :designationDirecteur')
            ->andWhere('p.etatdircab = :etatdircab')
            ->setParameters([
                'designationDirecteur' => 'Directeur',
                'etatdircab' => 'en attente',
            ]);
            
        $permissions = $qb->orderBy('p.datedebut', 'DESC')
            ->getQuery()
            ->getResult();

 
         $nbPermissionValidees = count($permissions);
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('permission/dircab-mespermissionsnonvalidees.html.twig', [
            'personne' => $personne,
            'nbMesPermissionValidees' => $nbPermissionValidees,
            'permissions' => $permissions,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('permission/sdmesPermissionsNonValidees/{id?0}', name: 'sd-permissionsNonValidees.afficher')]
    public function afficherSDPermissionsNonValidees(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
        NotificationRepository $notificationRepository
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
 
         $personne = $entityManager->getRepository(Personne::class)->find($personneId);

        $repository = $entityManager->getRepository(Permission::class);
        $qb = $repository->createQueryBuilder('p')
            ->innerJoin('p.personne', 'per')
            ->innerJoin('per.fonction', 'fonc')
            ->where('fonc.designation = :designationAgent OR fonc.designation = :designationChefService')
            ->andWhere('p.etatsd = :etatsd')
            ->setParameters([
                'designationAgent' => 'Agent', 
                'designationChefService' => 'Chef de service',
                'etatsd' => 'en attente',
            ]);
    
        $permissions = $qb->orderBy('p.datedebut', 'DESC')
            ->getQuery()
            ->getResult();


        $nbPermissionValidees = count($permissions);
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('permission/sd-mespermissionsnonvalidees.html.twig', [
            'personne' => $personne,
            'nbMesPermissionValidees' => $nbPermissionValidees,
            'permissions' => $permissions,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('permission/csmesPermissionsNonValidees/{id?0}', name: 'cs-permissionsNonValidees.afficher')]
    public function afficherCSPermissionsNonValidees(
        Permission $permission = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user,
        NotificationRepository $notificationRepository
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
 
         $personne = $entityManager->getRepository(Personne::class)->find($personneId);

         $repository = $entityManager->getRepository(Permission::class);
         $qb = $repository->createQueryBuilder('p')
             ->innerJoin('p.personne', 'per')
             ->innerJoin('per.fonction', 'fonc')
             ->where('fonc.designation = :designation')
             ->andWhere('p.etatcs = :etatcs')
             ->setParameters([
                 'designation' => 'Agent', 
                 'etatcs' => 'en attente',
             ]);
         
         $permissions = $qb->orderBy('p.datedebut', 'DESC')
             ->getQuery()
             ->getResult();
         

        $nbPermissionValidees = count($permissions);
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('permission/cs-mespermissionsnonvalidees.html.twig', [
            'personne' => $personne,
            'nbMesPermissionValidees' => $nbPermissionValidees,
            'permissions' => $permissions,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
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
        if ($permission->getStatut() !== Permission::STATUT_EN_ATTENTE && $permission->getEtatcs() !== Permission::STATUT_EN_ATTENTE && $permission->getEtatsd() !== Permission::STATUT_EN_ATTENTE && $permission->getEtatdir()) {
            $this->addFlash('error',"Vous ne pouvez plus annuler cette demande");
            return $this->redirectToRoute('permissionCD.afficher', ['permissionId' => $permissionId]);
        }
        
        $personne->removePermission($permission);

        $entityManager = $doctrine->getManager();
        $entityManager->remove($permission);
        $entityManager->flush();
        $this->addFlash('success','Demande annulée avec succès ');
        return $this->redirectToRoute('permissionCD.afficher', ['permissionId' => $permissionId]);


    }

    #[Route('/permission/valider/{permissionId}', name: 'permission.valider')]
    public function validerPermission(
        ManagerRegistry $doctrine,
        Request $request,
        $permissionId,
        NotificationRepository $notificationRepository
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

        if ($this->isGranted('ROLE_GRH')) {
            if ($permission->getStatut() !== Permission::STATUT_EN_ATTENTE) {
                // throw new StatutErrorException('La permission ne peut pas être validée car son statut est différent de "en attente".',400);
                $this->addFlash('error',"Impossible de rejeter cette demande de permission");
                return $this->redirectToRoute('permission.show', ['id' => $permission->getId()]);
            }
    
            // rejeter la permission en changeant son statut
            $permission->setStatut(Permission::STATUT_VALIDEE);
        }

        if ($this->isGranted('ROLE_DIRCAB')) {
            if ($permission->getEtatdircab() !== Permission::STATUT_EN_ATTENTE) {
                // throw new StatutErrorException('La permission ne peut pas être validée car son statut est différent de "en attente".',400);
                $this->addFlash('error',"Impossible de rejeter cette demande de permission");
                return $this->redirectToRoute('permission.show', ['id' => $permission->getId()]);
            }
    
            // rejeter la permission en changeant son statut
            $permission->setEtatdircab(Permission::STATUT_VALIDEE);
        }

        if ($this->isGranted('ROLE_DIR')) {
            if ($permission->getEtatdir() !== Permission::STATUT_EN_ATTENTE) {
                // throw new StatutErrorException('La permission ne peut pas être validée car son statut est différent de "en attente".',400);
                $this->addFlash('error',"Impossible de rejeter cette demande de permission");
                return $this->redirectToRoute('permission.show', ['id' => $permission->getId()]);
            }
    
            // rejeter la permission en changeant son statut
            $permission->setEtatdir(Permission::STATUT_VALIDEE);
        }

        if ($this->isGranted('ROLE_SD')) {
            if ($permission->getEtatsd() !== Permission::STATUT_EN_ATTENTE) {
                // throw new StatutErrorException('La permission ne peut pas être validée car son statut est différent de "en attente".',400);
                $this->addFlash('error',"Impossible de rejeter cette demande de permission");
                return $this->redirectToRoute('permission.show', ['id' => $permission->getId()]);
            }
    
            // rejeter la permission en changeant son statut
            $permission->setEtatsd(Permission::STATUT_VALIDEE);
        }

        if ($this->isGranted('ROLE_CS')) {
            if ($permission->getEtatcs() !== Permission::STATUT_EN_ATTENTE) {
                // throw new StatutErrorException('La permission ne peut pas être validée car son statut est différent de "en attente".',400);
                $this->addFlash('error',"Impossible de rejeter cette demande de permission");
                return $this->redirectToRoute('permission.show', ['id' => $permission->getId()]);
            }
    
            // rejeter la permission en changeant son statut
            $permission->setEtatcs(Permission::STATUT_VALIDEE);
        }
        

        $entityManager = $doctrine->getManager();
        $entityManager->flush();
        $this->addFlash('success','Demande validée avec succès');
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
        if ($this->isGranted('ROLE_GRH')) {
            if ($permission->getStatut() !== Permission::STATUT_EN_ATTENTE) {
                // throw new StatutErrorException('La permission ne peut pas être validée car son statut est différent de "en attente".',400);
                $this->addFlash('error',"Impossible de rejeter cette demande de permission");
                return $this->redirectToRoute('permission.show', ['id' => $permission->getId()]);
            }
    
            // rejeter la permission en changeant son statut
            $permission->setStatut(Permission::STATUT_REJETEE);
        }

        if ($this->isGranted('ROLE_DIRCAB')) {
            if ($permission->getEtatdircab() !== Permission::STATUT_EN_ATTENTE) {
                // throw new StatutErrorException('La permission ne peut pas être validée car son statut est différent de "en attente".',400);
                $this->addFlash('error',"Impossible de rejeter cette demande de permission");
                return $this->redirectToRoute('permission.show', ['id' => $permission->getId()]);
            }
    
            // rejeter la permission en changeant son statut
            $permission->setEtatdircab(Permission::STATUT_REJETEE);
        }

        if ($this->isGranted('ROLE_DIR')) {
            if ($permission->getEtatdir() !== Permission::STATUT_EN_ATTENTE) {
                // throw new StatutErrorException('La permission ne peut pas être validée car son statut est différent de "en attente".',400);
                $this->addFlash('error',"Impossible de rejeter cette demande de permission");
                return $this->redirectToRoute('permission.show', ['id' => $permission->getId()]);
            }
    
            // rejeter la permission en changeant son statut
            $permission->setEtatdir(Permission::STATUT_REJETEE);
        }

        if ($this->isGranted('ROLE_SD')) {
            if ($permission->getEtatsd() !== Permission::STATUT_EN_ATTENTE) {
                // throw new StatutErrorException('La permission ne peut pas être validée car son statut est différent de "en attente".',400);
                $this->addFlash('error',"Impossible de rejeter cette demande de permission");
                return $this->redirectToRoute('permission.show', ['id' => $permission->getId()]);
            }
    
            // rejeter la permission en changeant son statut
            $permission->setEtatsd(Permission::STATUT_REJETEE);
        }

        if ($this->isGranted('ROLE_CS')) {
            if ($permission->getEtatcs() !== Permission::STATUT_EN_ATTENTE) {
                // throw new StatutErrorException('La permission ne peut pas être validée car son statut est différent de "en attente".',400);
                $this->addFlash('error',"Impossible de rejeter cette demande de permission");
                return $this->redirectToRoute('permission.show', ['id' => $permission->getId()]);
            }
    
            // rejeter la permission en changeant son statut
            $permission->setEtatcs(Permission::STATUT_REJETEE);
        }

        $entityManager = $doctrine->getManager();
        $entityManager->flush();
        $this->addFlash('success','Demande rejetéé avec succès');
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
