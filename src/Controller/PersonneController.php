<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Personne;
// use App\Service\MailerService;
use App\Form\PersonneType;
use App\Service\UploaderService;
use App\Repository\NoteRepository;
use App\Repository\UserRepository;
use App\Repository\PersonneRepository;
use App\Repository\PermissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DeclarationRepository;
use App\Repository\NotificationRepository;
use Doctrine\Persistence\ManagerRegistry;
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
        int $id = 0
    ): Response {
        $agent = $personneRepository->find($id);
        $currentUser = $this->getUser();
        $personne = $currentUser->getPersonne();
        $nbUser = $userRepository->count([]);
        $adminUserCount = $userRepository->count(['roles' => ['ROLE_ADMIN']]);
        // $adminUserCount = $userRepository->countAdminAndUser();
        $nbPersonne = $personneRepository->count([]);
        $userRepository = $entityManager->getRepository(User::class);
        $users = $userRepository->findAll();
        $personneRepository = $entityManager->getRepository(Personne::class);
        $personnes = $personneRepository->findBy([], ['nom' => 'ASC','prenom' => 'ASC']);
        return $this->render('admin-template.html.twig',['utilisateur'=>$user,'users'=>$users,'personnes'=>$personnes,'personne'=>$personne,'nbPersonne'=>$nbPersonne,'nbUser'=>$nbUser,'adminUserCount'=>$adminUserCount]);
    }
    #[Route('/cs/index/{id?0}', name: 'cs')]
    public function cs(PermissionRepository $permissionRepository,
        UserInterface $user,
        PersonneRepository $personneRepository,
        int $id = 0
    ): Response {
        $agent = $personneRepository->find($id);
        $currentUser = $this->getUser();
        $personne = $currentUser->getPersonne();
        $nbPermission = $permissionRepository->count([]);
        return $this->render('cs-template.html.twig',['utilisateur'=>$user,'personne'=>$personne,'nbPermission'=>$nbPermission]);
    }
    #[Route('/sd/index/{id}', name: 'sd')]
    public function sd(PermissionRepository $permissionRepository,
        NoteRepository $noteRepository,
        UserInterface $user,
        PersonneRepository $personneRepository,
        int $id = 0
    ): Response {
        $agent = $personneRepository->find($id);
        $currentUser = $this->getUser();
        $personne = $currentUser->getPersonne();
        $nbPermission = $permissionRepository->count([]);
        $nbNote = $permissionRepository->count([]);
        return $this->render('sd-template.html.twig',['utilisateur'=>$user,'personne'=>$personne,'nbPermission'=>$nbPermission,'nbNote'=>$nbNote]);
    }
    #[Route('/dir/index/{id}', name: 'dir')]
    public function dir(PermissionRepository $permissionRepository,
        NoteRepository $noteRepository,
        UserInterface $user,
        PersonneRepository $personneRepository,
        int $id = 0
    ): Response {
        $agent = $personneRepository->find($id);
        $currentUser = $this->getUser();
        $personne = $currentUser->getPersonne();
        $nbPermission = $permissionRepository->count([]);
        $nbNote = $permissionRepository->count([]);
        return $this->render('dir-template.html.twig',['utilisateur'=>$user,'personne'=>$personne,'nbPermission'=>$nbPermission,'nbNote'=>$nbNote]);
    }
    #[Route('/dircab/index', name: 'dircab')]
    public function dircab(
        NoteRepository $noteRepository,
        PermissionRepository $permissionRepository,
        UserInterface $user,
        PersonneRepository $personneRepository,
        int $id = 0
    ): Response {
        $agent = $personneRepository->find($id);
        $currentUser = $this->getUser();
        $personne = $currentUser->getPersonne();
        $nbPermission = $permissionRepository->count([]);
        $nbNote = $permissionRepository->count([]);
        return $this->render('dircab-template.html.twig',['utilisateur'=>$user,'personne'=>$personne,'nbDeclaration' => $nbNote,'nbPermission'=>$nbPermission]);
    }
    // #[Route('/ministre/index', name: 'ministre')]
    // public function ministre(PermissionRepository $permissionRepository,UserInterface $user): Response
    // {
    //     $nbPermission = $permissionRepository->count([]);
    //     return $this->render('ministre-template.html.twig',['utilisateur'=>$user,'nbPermission' => $nbPermission]);
    // }
    #[Route('/grh/index/{id?0}', name: 'grh')]
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
        $nbNote = $noteRepository->count([]);
        $nbPermission = $permissionRepository->count([]);
        $nbDeclaration = $declarationRepository->count([]);
        
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        // dd($notifications);
        // Marquer les notifications comme lues
        foreach ($notifications as $notification) {
            $notification->setLu(true);
        }
        $entityManager->flush();
        return $this->render('grh-template.html.twig',[
            'utilisateur'=>$user,
            'personne'=>$personne,
            'nbPermission'=>$nbPermission,
            'nbDeclaration'=>$nbDeclaration,
            'nbNote'=>$nbNote,
            'notifications' => $notifications,
        ]);
    }
    #[Route('/user/index/{id?0}', name: 'user')]
    public function user(UserRepository $userRepository,
        PermissionRepository $permissionRepository,
        DeclarationRepository $declarationRepository,
        UserInterface $user,
        PersonneRepository $personneRepository,
        int $id = 0
    ): Response {
        $agent = $personneRepository->find($id);
        $currentUser = $this->getUser();
        $personne = $currentUser->getPersonne();
        $nbMesPermission = $permissionRepository->count(['personne' => $personne]);
        $nbMesDeclaration = $declarationRepository->count(['personne' => $personne]);
        
        return $this->render('user-template.html.twig',['utilisateur'=>$user,'personne'=>$personne,'nbMesPermission'=>$nbMesPermission,'nbMesDeclaration'=>$nbMesDeclaration]);
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
    // MailerService $mailer
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

        return $this->render('personne/liste-agent.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'nbPersonne' => $nbPersonne,
            'personnes' => $personnes,
        ]);
    }
    
    #[Route('personneEditbyAdmin/edit/{id?0}', name: 'admin_personne.edit')]
    public function editbyAdmin(
        UserInterface $user,
        Personne $personne = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        // MailerService $mailer
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
            $this->addFlash("success", $personne->getNom() . ' ' .$personne->getPrenom().' '.'de matricule '.$personne->getMatricule().' '. $message);
            return $this->redirectToRoute('personne.liste');
        }

        $repository = $entityManager->getRepository(Personne::class);
        $personnes = $repository->findAll();

        return $this->render('personne/edit-agent.html.twig', [
            'form' => $form->createView(),
            'personnes' => $personnes,
            'user' => $user
        ]);
    }

    #[Route('personne/edit/{id?0}', name: 'personne.edit')]
    public function editAgent(
        UserInterface $user,
        Personne $personne = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UploaderService $uploaderService,
        // MailerService $mailer
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

            // Vérifier si une photo a été téléchargée
            if ($photo instanceof UploadedFile) {
                $directory = $this->getParameter('image_signature_directory');
                $personne->setImage($uploaderService->uploadFile($photo, $directory));
            }

            // Vérifier si une signature a été téléchargé
            if ($signature instanceof UploadedFile) {
                $directory = $this->getParameter('image_signature_directory');
                $personne->setSignature($uploaderService->uploadFile($signature, $directory));
            }

            // Vérifie si une photo a été téléchargée

            if ($new) {
                $entityManager->persist($personne);
            }

            $entityManager->flush();

            $this->addFlash("success",'Vos informations ont été mis à jour avec succès ');
            // return $this->redirectToRoute('personne.liste');
        }

        $repository = $entityManager->getRepository(Personne::class);
        $personnes = $repository->findAll();

        return $this->render('personne/edit-profile.html.twig', [
            'form' => $form->createView(),
            'personnes' => $personnes,
            'user' => $user
        ]);
    }


    #[Route('/personne/{id<\d+>}', name: 'personne.detail')]
    public function detail(Personne $personne = null,UserInterface $user){     
        if (!$personne) {
            $this->addFlash('error',"Agent inconnu");
            return $this->redirectToRoute('personne.liste');

        }
        return $this->render('personne/show-information.html.twig',['personne'=>$personne,'user'=>$user]);

    }
    
    #[Route('/personne/delete/{matricule}', name: 'personne.delete')]
    public function delete(string $matricule, EntityManagerInterface $entityManager): RedirectResponse
    {
        $personne = $entityManager->getRepository(Personne::class)->findOneBy(['matricule' => $matricule]);

        if ($personne) {
            $entityManager->remove($personne);
            $entityManager->flush();
            $this->addFlash('success', 'Personne supprimée avec succès');
        } else {
            $this->addFlash('error', 'Personne inexistante');
        }
        
        return $this->redirectToRoute('personne.liste');
    }


}