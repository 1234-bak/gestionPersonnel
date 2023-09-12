<?php

namespace App\Controller;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NotificationController extends AbstractController
{

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

#[Route('/marquer-notification-lue/{id}', name: 'marquer_lue')]
public function marquerLue(Notification $notification, EntityManagerInterface $entityManager, Request $request): Response
{
    // Marquer la notification comme lue
    $notification->setLu(true);
    $entityManager->flush();

    if ($request->isXmlHttpRequest()) {
        return new JsonResponse(['message' => 'Notification marquée comme lue.']);
    }

    // Vous pouvez rediriger vers la page que vous souhaitez ici

    return new JsonResponse(['message' => 'Notification marquée comme lue.']);
}

#[Route('/note/delete/{id}', name: 'notification.delete')]
public function delete($id, EntityManagerInterface $entityManager): RedirectResponse
{
    $roles = ['ROLE_GRH', 'ROLE_DIRCAB','ROLE_DIR', 'ROLE_SD','ROLE_CS','ROLE_USER'];

    $granted = false;
    foreach ($roles as $role) {
        if ($this->isGranted($role)) {
            $granted = true;
            break;
        }
    }
    $notification = $entityManager->getRepository(Notification::class)->findOneBy(['id' => $id]);

    if ($notification) {
        $entityManager->remove($notification);
        $entityManager->flush();
    } else {
        $this->addFlash('error', 'Notification inexistante');
    }

    if ($this->isGranted('ROLE_GRH')) {
        return $this->redirectToRoute('grh');
        
    }
    if ($this->isGranted('ROLE_DIRCAB')) {
        return $this->redirectToRoute('dircab');
        
    }
    if ($this->isGranted('ROLE_DIR')) {
        return $this->redirectToRoute('dir');
        
    }
    if ($this->isGranted('ROLE_SD')) {
        return $this->redirectToRoute('sd');
        
    }
    if ($this->isGranted('ROLE_CS')) {
        return $this->redirectToRoute('cs');
        
    }
    if ($this->isGranted('ROLE_USER')) {
        return $this->redirectToRoute('user');
        
    }
}




}
