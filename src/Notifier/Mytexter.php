<?php 
// src/Notifier/MyTexter.php

namespace App\Notifier;

use Symfony\Component\Notifier\Texter\TexterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Recipient\Recipient;
use App\Entity\Personne;
use Doctrine\ORM\EntityManagerInterface;

class MyTexter implements TexterInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function send(Recipient $recipient, ChatMessage $message): void
    {
        if ($recipient instanceof PersonRecipient) {
            $personId = $recipient->getPersonId();
            $person = $this->entityManager->getRepository(Personne::class)->find($personId);
            // Implement the logic to send the notification to the simulated recipient
            // You can use any notification service or API you prefer
            // For example:
            // $notificationService->sendNotification($person->getPhoneNumber(), $message->getSubject(), $message->getOptions());
        }
    }
}
