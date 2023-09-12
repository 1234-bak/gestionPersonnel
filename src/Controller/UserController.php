<?php 
namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\AdminEditUserType;
use App\Form\RegistrationFormType;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    
    private $passwordHasher;
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    #[Route('admin/userAdminEdit/{id}', name: 'edit_admin_user')]
    public function editAdminUser(
        User $user=null,
         Request $request,
          EntityManagerInterface $entityManager,
          UserInterface $utilisateur,
          NotificationRepository $notificationRepository
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Vous n\'avez pas la permission d\'accéder à cette page.');
        $new = false;
        if (!$user) {
            $user = new User();
            $new = true;
        }
        $form = $this->createForm(AdminEditUserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($new) {
                $plainPassword = $form->get('plainPassword')->getData();
                if (!empty($plainPassword)) {
                    $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
                }
                $entityManager->persist($user);
            }
            $entityManager->flush();
            
            $this->addFlash('success', 'Utilisateur de matricule '.$user->getMatricule().' mis à jour avec succès.');

            return $this->redirectToRoute('user_list');
        }
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('user/edit_admin_user.html.twig', [
            'registrationForm' => $form->createView(),
            'user' => $utilisateur,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }
    #[Route('/user/edit/{id}', name: 'user_edit')]
    public function edit(User $user=null, Request $request, EntityManagerInterface $entityManager,UserInterface $utilisateur, NotificationRepository $notificationRepository): Response
    {
        $roles = 'ROLE_USER';
        $this->denyAccessUnlessGranted($roles, null, 'Vous n\'avez pas la permission d\'accéder à cette page.');
        $new = false;
        if (!$user) {
            $user = new User();
            $new = true;
        }
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($new) {
                $plainPassword = $form->get('plainPassword')->getData();
                if (!empty($plainPassword)) {
                    $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
                }
                $entityManager->persist($user);
        }
        
        $entityManager->flush();

        $this->addFlash('success', 'User updated successfully.');

        return $this->redirectToRoute('user');
    }
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('user/edit.html.twig', [
            'registrationForm' => $form->createView(),
            'user' => $utilisateur,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('admin/user/delete/{id}', name: 'user_delete')]
    public function delete(User $user, EntityManagerInterface $entityManager): Response
    {
        $roles = 'ROLE_ADMIN';
        $this->denyAccessUnlessGranted($roles, null, 'Vous n\'avez pas la permission d\'accéder à cette page.');
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');

        return $this->redirectToRoute('user_list');
    }

    #[Route('/user/show/{id}', name: 'user_show')]
    public function show(User $user=null,UserInterface $utilisateur, NotificationRepository $notificationRepository): Response
    {
        $roles = 'ROLE_USER';
        $this->denyAccessUnlessGranted($roles, null, 'Vous n\'avez pas la permission d\'accéder à cette page.');
        if (!$user) {
            $this->addFlash('error',"L'utilisateur n'existe pas");
            return $this->redirectToRoute('user_list');

        }
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('user/show.html.twig', [
            'user' => $utilisateur,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route('/user/detail/{userId}', name: 'detail_user_show')]
    public function detailUser($userId, EntityManagerInterface $entityManager,NotificationRepository $notificationRepository): Response
    {
        $userRepository = $entityManager->getRepository(User::class);
        $user = $userRepository->find($userId);
    
        if (!$user) {
            $this->addFlash('error', "L'utilisateur n'existe pas");
            return $this->redirectToRoute('user_list');
        }
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('user/detail_user_show.html.twig', [
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }
    
    #[Route('admin/user/list/', name: 'user_list')]
    public function list(EntityManagerInterface $entityManager, Request $request, UserInterface $utilisateur, User $user = null,NotificationRepository $notificationRepository): Response
    {   
        $roles = 'ROLE_ADMIN';
        $this->denyAccessUnlessGranted($roles, null, 'Vous n\'avez pas la permission d\'accéder à cette page.');
        $new = false;
        if (!$user) {
            $user = new User();
            $new = true;
        }
         $form = $this->createForm(RegistrationFormType::class, $user);
         $form->handleRequest($request);
         if ($form->isSubmitted() && $form->isValid()) {
             $user->setPassword($form->get('plainPassword')->getData());
             if ($new) {
               $entityManager->persist($user);
           }
         $entityManager->flush();

         $this->addFlash('success', 'User updated successfully.');

         return $this->redirectToRoute('user_list');
     }
        $userRepository = $entityManager->getRepository(User::class);
        $nbUser = $userRepository->count([]);
        $adminUserCount = $userRepository->count(['roles' => 'ROLE_ADMIN']);
        $users = $userRepository->findAll();
        $notifications = $notificationRepository->findNotificationsForUser($utilisateur->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('user/list.html.twig', [
            'user' => $utilisateur,
            'users' => $users,
            'nbUser' => $nbUser,
            'adminUserCount' => $adminUserCount,
            'registrationForm' => $form,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }
    
    // #[Route('user/change-password', name: 'change_password', methods: ['POST'])]
    // public function changePassword(Request $request,User $user=null,EntityManagerInterface $entityManager): Response
    // {
    //     $user = $this->getUser(); // Récupérer l'utilisateur actuel (connecté)

    //     // Récupérer le nouveau mot de passe à partir de la requête (par exemple, depuis un formulaire)
    //     $newPassword = $request->request->get('new_password');

    //     // Utiliser UserPasswordHasherInterface pour hacher le nouveau mot de passe
    //     $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);

    //     // Définir le nouveau mot de passe haché sur l'utilisateur
    //     $user->setPassword($hashedPassword);

    //     // Enregistrer les modifications de l'utilisateur dans votre gestionnaire d'entités (EntityManager)
    //     $$entityManager->persist($user);
    //     $entityManager->flush();

    //     // Rediriger ou retourner une réponse appropriée
    //     return $this->redirectToRoute('user');
    // }

}
