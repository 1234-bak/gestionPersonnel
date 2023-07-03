<?php
namespace App\Service;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class MailerService{
    public function __construct(private MailerInterface $mailer){}
    public function sendEmail(
        $to = "ibrahimbakayoko2991@gmail.com",
        $content = "<p>See Twig integration for better HTML integration!</p>');"
    ): void
    {
        $email = (new Email())
            ->from('ibrahimbakayoko177@gmail.com')
            ->to($to)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html($content);

        $this->mailer->send($email);

        // ...
    }
}



?>