<?php

namespace App\Controller;

use App\Entity\Personne;
use App\Entity\Signature;
// use App\Service\MailerService;
use App\Form\SignatureType;
use App\Service\UploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SignatureController extends AbstractController
{
    public function __construct(private Security $security)
    {
    }

    #[Route('/signature/liste', name: 'signature.liste')]
    public function indexAll(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Signature::class);
        $signatures = $repository->findAll();
        $form = $this->createForm(SignatureType::class);
        return $this->render('signature/liste-agent.html.twig', [
            'signatures' => $signatures
        ]);
    }

    #[Route('signature/edit/{id?0}', name: 'signature.edit')]
    public function edit(
    UserInterface $user,
    Signature $signature = null,
    EntityManagerInterface $entityManager,
    Request $request,
    UploaderService $uploaderService,
    // MailerService $mailer
): Response {
    $new = false;
    if ($user && $user->getPersonne()) {
        $personneId = $user->getPersonne()->getId();
    }
    $personne = $entityManager->getRepository(Personne::class)->find($personneId);
    if (!$signature) {
        $signature = new Signature();
        $new = true;
    }

    $form = $this->createForm(SignatureType::class, $signature);
    $form->remove('createdAt');
    $form->remove('updatedAt');

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $photo = $form->get('photo')->getData();

        // Vérifie si une photo a été téléchargée
        if ($photo) {
            $directory = $this->getParameter('image_signature_directory');
            $signature->setPath($uploaderService->uploadFile($photo, $directory));
        }

        if ($new) {
            $personne->addSignature($signature);
        }

        $entityManager->persist($signature);
        $entityManager->flush();

        $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
        $this->addFlash("success", $signature->getPath() . ' ' . $message);

    }

    // $repository = $entityManager->getRepository(Signature::class);
    // $signatures = $repository->findAll();

    return $this->render('signature/edit-signature.html.twig', [
        'form' => $form->createView(),
        'signature' => $signature,
        'personne' => $personne
    ]);
}

}