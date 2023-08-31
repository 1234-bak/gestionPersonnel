<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NotificationController extends AbstractController
{
    #[Route('/notification', name: 'app_notification')]
    public function index(): Response
    {
        return $this->render('notification/index.html.twig', [
            'controller_name' => 'NotificationController',
        ]);
    }

    #[Route('/mes-notifications', name: 'mes_notifications')]
public function mesNotifications(NotificationRepository $notificationRepository, UserInterface $user,EntityManagerInterface $entityManager): Response
{
    $notifications = $notificationRepository->findNotificationsForUser($user);
    
    // Marquer les notifications comme lues
    foreach ($notifications as $notification) {
        $notification->setLu(true);
    }
    // $note = $entityManager->getRepository(Note::class)->find($id);
    $entityManager->flush();
    
    return $this->render('notification/mes_notifications.html.twig', [
        'notifications' => $notifications,
    ]);
}

}
