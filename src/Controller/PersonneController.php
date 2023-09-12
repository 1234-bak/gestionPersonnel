<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Personne;
// use App\Service\MailerService;
use App\Entity\Permission;
use App\Form\PersonneType;
use App\Service\UploaderService;
use App\Repository\NoteRepository;
use App\Repository\UserRepository;
use App\Repository\PersonneRepository;
use App\Repository\PermissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DeclarationRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PersonneController extends AbstractController
{

    #[Route('/admin/index/{id}', name: 'admin')]
    public function admin(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserInterface $user,
        PersonneRepository $personneRepository,
        NotificationRepository $notificationRepository,
        int $id = 0
    ): Response {
        $agent = $personneRepository->find($id);
        $currentUser = $this->getUser();
        $personne = $currentUser->getPersonne();
        $nbUser = $userRepository->count([]);
        $adminUserCount = $userRepository->count(['roles' => ['ROLE_ADMIN']]);
        $nbPersonne = $personneRepository->count([]);
        $userRepository = $entityManager->getRepository(User::class);
        $users = $userRepository->findAll();
        $personneRepository = $entityManager->getRepository(Personne::class);
        $personnes = $personneRepository->findBy([], ['nom' => 'ASC','prenom' => 'ASC']);
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('admin-template.html.twig',[
            'utilisateur'=>$user,
            'users'=>$users,
            'personnes'=>$personnes,
            'personne'=>$personne,
            'nbPersonne'=>$nbPersonne,
            'nbUser'=>$nbUser,
            'adminUserCount'=>$adminUserCount,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }
    #[Route('/cs/index/{id?0}', name: 'cs')]
    public function cs(PermissionRepository $permissionRepository,
        UserInterface $user,
        PersonneRepository $personneRepository,
        EntityManagerInterface $entityManager,
        NotificationRepository $notificationRepository,
        int $id = 0
    ): Response {
        $agent = $personneRepository->find($id);
        $currentUser = $this->getUser();
        $personne = $currentUser->getPersonne();
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
        return $this->render('cs-template.html.twig',[
            'utilisateur'=>$user,
            'personne'=>$personne,
            'nbPermission'=>$nbPermission,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }
    #[Route('/sd/index/{id}', name: 'sd')]
    public function sd(PermissionRepository $permissionRepository,
        NoteRepository $noteRepository,
        UserInterface $user,
        EntityManagerInterface $entityManager,
        PersonneRepository $personneRepository,
        NotificationRepository $notificationRepository,
        int $id = 0,
    ): Response {
        $agent = $personneRepository->find($id);
        $currentUser = $this->getUser();
        $personne = $currentUser->getPersonne();
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
         $nbNote = count($personne->getNote());
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('sd-template.html.twig',[
            'utilisateur'=>$user,
            'personne'=>$personne,
            'nbPermission'=>$nbPermission,
            'nbNote'=>$nbNote,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }
    #[Route('/dir/index/{id}', name: 'dir')]
    public function dir(PermissionRepository $permissionRepository,
        NoteRepository $noteRepository,
        UserInterface $user,
        PersonneRepository $personneRepository,
        EntityManagerInterface $entityManager,
        NotificationRepository $notificationRepository,
        int $id = 0
    ): Response {
        $agent = $personneRepository->find($id);
        $currentUser = $this->getUser();
        $personne = $currentUser->getPersonne();
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
        $nbNote = count($personne->getNote());
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('dir-template.html.twig',[
            'utilisateur'=>$user,
            'personne'=>$personne,
            'nbPermission'=>$nbPermission,
            'nbNote'=>$nbNote,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }
    #[Route('/fc/index/{id?0}', name: 'dircab')]
    public function dircab(
        NoteRepository $noteRepository,
        PermissionRepository $permissionRepository,
        UserInterface $user,
        PersonneRepository $personneRepository,
        EntityManagerInterface $entityManager,
        NotificationRepository $notificationRepository,
        int $id = 0
    ): Response {
        $agent = $personneRepository->find($id);
        $currentUser = $this->getUser();
        $personne = $currentUser->getPersonne();
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
        $nbNote = count($personne->getNote());
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('dircab-template.html.twig',[
            'utilisateur'=>$user,
            'personne'=>$personne,
            'nbNote' => $nbNote,
            'nbPermission'=>$nbPermission,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('grh/index/{id?0}', name: 'grh')]
    public function grh(
        PermissionRepository $permissionRepository,
        DeclarationRepository $declarationRepository,
        NoteRepository $noteRepository,
        UserInterface $user,
        PersonneRepository $personneRepository,
        NotificationRepository $notificationRepository,
        EntityManagerInterface $entityManager,
        int $id = 0
    ): Response {
        $agent = $personneRepository->find($id);
        $currentUser = $this->getUser();
        $personne = $currentUser->getPersonne();
        $nbNote = count($personne->getNote());
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
        $nbDeclaration = $declarationRepository->count([]);
        
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        $entityManager->flush();
        return $this->render('grh-template.html.twig',[
            'utilisateur'=>$user,
            'personne'=>$personne,
            'nbPermission'=>$nbPermission,
            'nbDeclaration'=>$nbDeclaration,
            'nbNote'=>$nbNote,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }
    #[Route('user/index/{id?0}', name: 'user')]
    public function user(UserRepository $userRepository,
        PermissionRepository $permissionRepository,
        DeclarationRepository $declarationRepository,
        UserInterface $user,
        PersonneRepository $personneRepository,
        EntityManagerInterface $entityManager,
        NotificationRepository $notificationRepository,
        int $id = 0
    ): Response {
        $agent = $personneRepository->find($id);
        $currentUser = $this->getUser();
        $personne = $currentUser->getPersonne();
        $nbMesDeclaration = $declarationRepository->count(['personne' => $personne]);

        $permissionsCD = $personne->getPermission()->filter(function ($permission) {
            return $permission->getDelai() <= 3;
        });
        $nbMesPermissionCD = count($permissionsCD);

        $permissionsLD = $personne->getPermission()->filter(function ($permission) {
            return $permission->getDelai() > 3;
        });
        $nbMesPermissionLD = count($permissionsLD);

        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('user-template.html.twig',[
            'utilisateur'=>$user,
            'personne'=>$personne,
            'nbPermissionCD'=>$nbMesPermissionCD,
            'nbPermissionLD'=>$nbMesPermissionLD,
            'nbMesDeclaration'=>$nbMesDeclaration,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }
    
    #[Route("/personne/delete-multiple", name:"personne.delete_multiple", methods:["POST", "DELETE"])]
    public function deleteMultiple(Request $request, EntityManagerInterface $entityManager): Response
    {
        $personnesIds = $request->toArray()['personnes'];

        if (!empty($personnesIds)) {
            $repository = $entityManager->getRepository(Personne::class);

            $queryBuilder = $repository->createQueryBuilder('p')
                ->where('p.id IN (:ids)')
                ->setParameter('ids', $personnesIds);

            $personnes = $queryBuilder->getQuery()->getResult();

            foreach ($personnes as $personne) {
                $entityManager->remove($personne);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Les personnes sélectionnées ont été supprimées avec succès.');

            return $this->redirectToRoute('personne.liste');
        }

        $this->addFlash('error', 'Aucune personne sélectionnée.');

        return $this->redirectToRoute('personne.liste');
    }
    #[Route('personne/liste/{id?0}', name: 'personne.liste')]
    public function indexAll(
        UserInterface $user,
        Personne $personne = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        NotificationRepository $notificationRepository
    ): Response {
        $new = false;
        if (!$personne) {
            $personne = new Personne();
            $new = true;
        }

        $form = $this->createForm(PersonneType::class, $personne);
        $form->remove('createdAt');
        $form->remove('updatedAt');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $photo = $form->get('photo')->getData();

            // Vérifie si une photo a été téléchargée
            if ($photo) {
                $directory = $this->getParameter('image_personne_directory');
                $personne->setImage($uploaderService->uploadFile($photo, $directory));
            }

            if ($new) {
                $entityManager->persist($personne);
            }

            $entityManager->flush();
            $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
            // $mailMessage = $personne->getNom() . ' ' . $personne->getPrenom() . ' ' . $message;
            $this->addFlash("success", $personne->getPrenom() . ' ' . $message);
            // $mailer->sendEmail($mailMessage);
            return $this->redirectToRoute('personne.liste');
        }

        $repository = $entityManager->getRepository(Personne::class);
        $nbPersonne = $repository->count([]);
        $personnes = $repository->findAll();
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('personne/liste-agent.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'nbPersonne' => $nbPersonne,
            'personnes' => $personnes,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }
    
    #[Route('personneEditbyAdmin/edit/{id?0}', name: 'admin_personne.edit')]
    public function editbyAdmin(
        UserInterface $user,
        Personne $personne = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        NotificationRepository $notificationRepository
    ): Response {
        $new = false;
        if (!$personne) {
            $personne = new Personne();
            $new = true;
        }

        $form = $this->createForm(PersonneType::class, $personne);
        $form->remove('createdAt');
        $form->remove('updatedAt');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $photo = $form->get('photo')->getData();

            $signature = $form->get('signature')->getData();
            // Vérifie si une photo a été téléchargée
            if ($photo) {
                $directory = $this->getParameter('image_personne_directory');
                $personne->setImage($uploaderService->uploadFile($photo, $directory));
            }


            // Vérifie si une signature a été téléchargée
            if ($signature) {
                $directory_signature = $this->getParameter('image_signature_directory');
                $personne->setSignature($uploaderService->uploadFile($signature, $directory_signature));
            }

            if ($new) {
                $entityManager->persist($personne);
            }

            $entityManager->flush();

            $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success",$personne->getCivilite().' '.$personne->getNom().' de matricule '.$personne->getMatricule() .' ' . $message);
            return $this->redirectToRoute('personne.liste');
        }

        $repository = $entityManager->getRepository(Personne::class);
        $personnes = $repository->findAll();
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('personne/edit-agent.html.twig', [
            'form' => $form->createView(),
            'personnes' => $personnes,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('personne/edit/{id?0}', name: 'personne.edit')]
    public function editAgent(
        UserInterface $user,
        Personne $personne = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        NotificationRepository $notificationRepository
    ): Response {
        $new = false;
        if (!$personne) {
            $personne = new Personne();
            $new = true;
        }

        $form = $this->createForm(PersonneType::class, $personne);
        $form->remove('createdAt');
        $form->remove('updatedAt');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $photo = $form->get('photo')->getData();

            $signature = $form->get('signature')->getData();
            // Vérifie si une photo a été téléchargée
            if ($photo) {
                $directory = $this->getParameter('image_personne_directory');
                $personne->setImage($uploaderService->uploadFile($photo, $directory));
            }


            // Vérifie si une signature a été téléchargée
            if ($signature) {
                $directory_signature = $this->getParameter('image_signature_directory');
                $personne->setSignature($uploaderService->uploadFile($signature, $directory_signature));
            }

            // Vérifie si une photo a été téléchargée

            if ($new) {
                $entityManager->persist($personne);
            }

            $entityManager->flush();
            $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success",$personne->getCivilite().' '.$personne->getNom().' de matricule '.$personne->getMatricule() .' ' . $message);

            // return $this->redirectToRoute('personne.liste');
        }

        $repository = $entityManager->getRepository(Personne::class);
        $personnes = $repository->findAll();
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('personne/edit-profile.html.twig', [
            'form' => $form->createView(),
            'personnes' => $personnes,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('personne/add/{id?0}', name: 'personne.add')]
    public function add(
        Personne $personne = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        UserInterface $user, 
        NotificationRepository $notificationRepository
    ): Response {

        $new = false;
        if (!$personne) {
            $personne = new Personne();
            $new = true;
        }

        $form = $this->createForm(PersonneType::class, $personne);
        $form->remove('createdAt');
        $form->remove('updatedAt');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $photo = $form->get('photo')->getData();

            $signature = $form->get('signature')->getData();
            // Vérifie si une photo a été téléchargée
            if ($photo) {
                $directory = $this->getParameter('image_personne_directory');
                $personne->setImage($uploaderService->uploadFile($photo, $directory));
            }


            // Vérifie si une signature a été téléchargée
            if ($signature) {
                $directory_signature = $this->getParameter('image_signature_directory');
                $personne->setSignature($uploaderService->uploadFile($signature, $directory_signature));
            }


            $entityManager->persist($personne);
            $entityManager->flush();

            $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success",$personne->getCivilite().' '.$personne->getNom().' de matricule '.$personne->getMatricule() .' ' . $message);

            return $this->redirectToRoute('personne.liste');
        }
        $repository = $entityManager->getRepository(Personne::class);
        $personnes = $repository->findAll();
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('personne/edit-agent.html.twig', [
            'form' => $form->createView(),
            'personnes' => $personnes,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('/personne/{id<\d+>}', name: 'personne.detail')]
    public function detail(
        Personne $personne = null,
        UserInterface $user,
        NotificationRepository $notificationRepository
    )
    {
        if (!$personne) {
            $this->addFlash('error',"Agent inconnu");
            return $this->redirectToRoute('personne.liste');

        }
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('personne/show-information.html.twig',[
            'personne'=>$personne,
            'user'=>$user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);

    }
    
    #[Route('/personne/delete/{matricule}', name: 'personne.delete')]
    public function delete(string $matricule, EntityManagerInterface $entityManager): RedirectResponse
    {
        $personne = $entityManager->getRepository(Personne::class)->findOneBy(['matricule' => $matricule]);

        if ($personne) {
            $entityManager->remove($personne);
            $entityManager->flush();
            $this->addFlash('success', 'Agent supprimée avec succès');
        } else {
            $this->addFlash('error', 'Agent inexistante');
        }
        
        return $this->redirectToRoute('personne.liste');
    }


}