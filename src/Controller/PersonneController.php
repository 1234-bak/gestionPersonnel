<?php

namespace App\Controller;

use App\Entity\Personne;
use App\Form\PersonneType;
// use App\Service\MailerService;
use App\Service\UploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PersonneController extends AbstractController
{

    #[Route('/index', name: 'personne')]
    public function index(): Response
    {
        return $this->render('page.html.twig');
    }
    #[Route('/personne/liste', name: 'personne.liste')]
    public function indexAll(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Personne::class);
        $personnes = $repository->findAll();
        return $this->render('personne/liste-agent.html.twig', [
            'personnes' => $personnes
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

    #[Route('personne/edit/{id?0}', name: 'personne.edit')]
    public function edit(
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
    $personnes = $repository->findAll();

    return $this->render('personne/edit-agent.html.twig', [
        'form' => $form->createView(),
        'personnes' => $personnes,
    ]);
}



    #[Route('/personne/{id<\d+>}', name: 'personne.detail')]
    public function detail(Personne $personne = null){     
        if (!$personne) {
            $this->addFlash('error',"La personne n'existe pas");
            return $this->redirectToRoute('personne.liste');

        }
        return $this->render('personne/show-information.html.twig',['personne'=>$personne]);

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