<?php 
namespace App\Controller;

use App\Entity\Direction;
use DateTime;
use App\Entity\Note;
use App\Entity\User;
use App\Form\NoteType;
use App\Entity\Service;
use App\Entity\Personne;
use App\Entity\Notification;
use App\Entity\SousDirection;
use App\Form\DestinataireType;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\NotificationRepository;
// use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NoteController extends AbstractController
{
    #[Route("/note/createbygrh/", name: "note.create")]
    public function createNotebygrh(
        UserInterface $user,
        Note $note =null,
        EntityManagerInterface $entityManager,
        Request $request,
        NotificationRepository $notificationRepository
    ): Response {
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB','ROLE_DIR', 'ROLE_SD'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            $this->addFlash('error','rôle innexistant');
        }

         // Récupérer l'ID de la personne à partir de l'utilisateur

        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
            
        }
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);

        $note = new Note();

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setStatut("en attente");
            $personne->addNote($note);
            $entityManager->persist($note);
            $entityManager->flush();

            $message ="a été ajouté avec succès";
            $this->addFlash("success", $note->getNumref() . ' ' . $message);
            // Redirection vers la route spécifique en fonction du rôle
            if ($this->isGranted('ROLE_GRH')) {
                return $this->redirectToRoute('grh-note.liste');
            } elseif ($this->isGranted('ROLE_DIRCAB')) {
                return $this->redirectToRoute('dircab-note.liste');
            }elseif ($this->isGranted('ROLE_DIR')) {
                return $this->redirectToRoute('dir-note.liste');
            }elseif ($this->isGranted('ROLE_SD')) {
                return $this->redirectToRoute('sd-note.liste');
            }
        }

        $repository = $entityManager->getRepository(Note::class);
        $notes = $repository->findAll();
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('note/create-note.html.twig', [
            'form' => $form->createView(),
            'notes' => $notes,
            'user' => $user,
            'note' => $note,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route("/note/createbydircab/", name: "dircab-note.create")]
    public function createNotebydircab(
        UserInterface $user,
        Note $note =null,
        EntityManagerInterface $entityManager,
        Request $request,
        NotificationRepository $notificationRepository
    ): Response {
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB','ROLE_DIR', 'ROLE_SD'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            $this->addFlash('error','rôle innexistant');
        }

         // Récupérer l'ID de la personne à partir de l'utilisateur

        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
            
        }
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);

        $note = new Note();

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setStatut("en attente");
            $personne->addNote($note);
            $entityManager->persist($note);
            $entityManager->flush();

            $message ="a été ajouté avec succès";
            $this->addFlash("success", $note->getNumref() . ' ' . $message);
            // Redirection vers la route spécifique en fonction du rôle
            if ($this->isGranted('ROLE_GRH')) {
                return $this->redirectToRoute('grh-note.liste');
            } elseif ($this->isGranted('ROLE_DIRCAB')) {
                return $this->redirectToRoute('dircab-note.liste');
            }elseif ($this->isGranted('ROLE_DIR')) {
                return $this->redirectToRoute('dir-note.liste');
            }elseif ($this->isGranted('ROLE_SD')) {
                return $this->redirectToRoute('sd-note.liste');
            }
        }

        $repository = $entityManager->getRepository(Note::class);
        $notes = $repository->findAll();
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('note/dircab-create-note.html.twig', [
            'form' => $form->createView(),
            'notes' => $notes,
            'user' => $user,
            'note' => $note,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route("/note/createbydir/", name: "dir-note.create")]
    public function createNotebydir(
        UserInterface $user,
        Note $note =null,
        EntityManagerInterface $entityManager,
        Request $request,
        NotificationRepository $notificationRepository
    ): Response {
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB','ROLE_DIR', 'ROLE_SD'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            $this->addFlash('error','rôle innexistant');
        }

         // Récupérer l'ID de la personne à partir de l'utilisateur

        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
            
        }
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);

        $note = new Note();

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setStatut("en attente");
            $personne->addNote($note);
            $entityManager->persist($note);
            $entityManager->flush();

            $message ="a été ajouté avec succès";
            $this->addFlash("success", $note->getNumref() . ' ' . $message);
            // Redirection vers la route spécifique en fonction du rôle
            if ($this->isGranted('ROLE_GRH')) {
                return $this->redirectToRoute('grh-note.liste');
            } elseif ($this->isGranted('ROLE_DIRCAB')) {
                return $this->redirectToRoute('dircab-note.liste');
            }elseif ($this->isGranted('ROLE_DIR')) {
                return $this->redirectToRoute('dir-note.liste');
            }elseif ($this->isGranted('ROLE_SD')) {
                return $this->redirectToRoute('sd-note.liste');
            }
        }

        $repository = $entityManager->getRepository(Note::class);
        $notes = $repository->findAll();
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('note/dir-create-note.html.twig', [
            'form' => $form->createView(),
            'notes' => $notes,
            'user' => $user,
            'note' => $note,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route("/note/createbysd/", name: "sd-note.create")]
    public function createNotebysd(
        UserInterface $user,
        Note $note =null,
        EntityManagerInterface $entityManager,
        Request $request,
        NotificationRepository $notificationRepository
    ): Response {
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB','ROLE_DIR', 'ROLE_SD'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            $this->addFlash('error','rôle innexistant');
        }

         // Récupérer l'ID de la personne à partir de l'utilisateur

        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
            
        }
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);

        $note = new Note();

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setStatut("en attente");
            $personne->addNote($note);
            $entityManager->persist($note);
            $entityManager->flush();

            $message ="a été ajouté avec succès";
            $this->addFlash("success", $note->getNumref() . ' ' . $message);
            // Redirection vers la route spécifique en fonction du rôle
            if ($this->isGranted('ROLE_GRH')) {
                return $this->redirectToRoute('grh-note.liste');
            } elseif ($this->isGranted('ROLE_DIRCAB')) {
                return $this->redirectToRoute('dircab-note.liste');
            }elseif ($this->isGranted('ROLE_DIR')) {
                return $this->redirectToRoute('dir-note.liste');
            }elseif ($this->isGranted('ROLE_SD')) {
                return $this->redirectToRoute('sd-note.liste');
            }
        }

        $repository = $entityManager->getRepository(Note::class);
        $notes = $repository->findAll();
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);

        return $this->render('note/sd-create-note.html.twig', [
            'form' => $form->createView(),
            'notes' => $notes,
            'user' => $user,
            'note' => $note,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }

    #[Route("/note/editbygrh/{id?0}", name: "note.edit")]
    public function editNotebygrh(
        UserInterface $user,
        Note $note = null,
        EntityManagerInterface $entityManager,
        Request $request,
        NotificationRepository $notificationRepository
    ): Response {
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB','ROLE_DIR', 'ROLE_SD'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            $this->addFlash('error',"rôle innexistant");
        }
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
            $this->addFlash('error','accès réfusé');
        }
         // Récupérer l'ID de la personne à partir de l'utilisateur

        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
            
        }
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);

        if ($note->getStatut() !== Note::STATUT_EN_ATTENTE) {
            $this->addFlash('error',"Impossible de modifier la note de service !");
        }

        $new = false;
        if (!$note) {
            $note = new Note();
            $new = true;
        }

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setStatut("en attente");
            if ($new) {
                $personne->addNote($note);
                $entityManager->persist($note);
            }

            $entityManager->flush();

            $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success", $note->getNumref() . ' ' . $message);
            // Redirection vers la route spécifique en fonction du rôle
            if ($this->isGranted('ROLE_GRH')) {
                return $this->redirectToRoute('grh-note.liste');
            } elseif ($this->isGranted('ROLE_DIRCAB')) {
                return $this->redirectToRoute('dircab-note.liste');
            }elseif ($this->isGranted('ROLE_DIR')) {
                return $this->redirectToRoute('dir-note.liste');
            }elseif ($this->isGranted('ROLE_SD')) {
                return $this->redirectToRoute('sd-note.liste');
            }
        }

        $repository = $entityManager->getRepository(Note::class);
        $notes = $repository->findAll();
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('note/edit-note.html.twig', [
            'form' => $form->createView(),
            'notes' => $notes,
            'user' => $user,
            'note' => $note,  
            'notifications' => $notifications,
            'nbNotif' => $nbNotif         
        ]);
    }

    #[Route("/note/editbydircab/{id?0}", name: "dircab-note.edit")]
    public function editNotebydircab(
        UserInterface $user,
        Note $note = null,
        EntityManagerInterface $entityManager,
        Request $request,
        NotificationRepository $notificationRepository
    ): Response {
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB','ROLE_DIR', 'ROLE_SD'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            $this->addFlash('error',"rôle innexistant");
        }
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
            $this->addFlash('error','accès réfusé');
        }
         // Récupérer l'ID de la personne à partir de l'utilisateur

        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
            
        }
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);

        if ($note->getStatut() !== Note::STATUT_EN_ATTENTE) {
            $this->addFlash('error',"Impossible de modifier la note de service !");
        }

        $new = false;
        if (!$note) {
            $note = new Note();
            $new = true;
        }

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setStatut("en attente");
            if ($new) {
                $personne->addNote($note);
                $entityManager->persist($note);
            }

            $entityManager->flush();

            $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success", $note->getNumref() . ' ' . $message);
            // Redirection vers la route spécifique en fonction du rôle
            if ($this->isGranted('ROLE_GRH')) {
                return $this->redirectToRoute('grh-note.liste');
            } elseif ($this->isGranted('ROLE_DIRCAB')) {
                return $this->redirectToRoute('dircab-note.liste');
            }elseif ($this->isGranted('ROLE_DIR')) {
                return $this->redirectToRoute('dir-note.liste');
            }elseif ($this->isGranted('ROLE_SD')) {
                return $this->redirectToRoute('sd-note.liste');
            }
        }

        $repository = $entityManager->getRepository(Note::class);
        $notes = $repository->findAll();
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('note/dircab-edit-note.html.twig', [
            'form' => $form->createView(),
            'notes' => $notes,
            'user' => $user,
            'note' => $note,  
            'notifications' => $notifications,
            'nbNotif' => $nbNotif         
        ]);
    }

    #[Route("/note/editbydir/{id?0}", name: "dir-note.edit")]
    public function editNotebydir(
        UserInterface $user,
        Note $note = null,
        EntityManagerInterface $entityManager,
        Request $request,
        NotificationRepository $notificationRepository
    ): Response {
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB','ROLE_DIR', 'ROLE_SD'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            $this->addFlash('error',"rôle innexistant");
        }
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
            $this->addFlash('error','accès réfusé');
        }
         // Récupérer l'ID de la personne à partir de l'utilisateur

        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
            
        }
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);

        if ($note->getStatut() !== Note::STATUT_EN_ATTENTE) {
            $this->addFlash('error',"Impossible de modifier la note de service !");
        }

        $new = false;
        if (!$note) {
            $note = new Note();
            $new = true;
        }

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setStatut("en attente");
            if ($new) {
                $personne->addNote($note);
                $entityManager->persist($note);
            }

            $entityManager->flush();

            $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success", $note->getNumref() . ' ' . $message);
            // Redirection vers la route spécifique en fonction du rôle
            if ($this->isGranted('ROLE_GRH')) {
                return $this->redirectToRoute('grh-note.liste');
            } elseif ($this->isGranted('ROLE_DIRCAB')) {
                return $this->redirectToRoute('dircab-note.liste');
            }elseif ($this->isGranted('ROLE_DIR')) {
                return $this->redirectToRoute('dir-note.liste');
            }elseif ($this->isGranted('ROLE_SD')) {
                return $this->redirectToRoute('sd-note.liste');
            }
        }

        $repository = $entityManager->getRepository(Note::class);
        $notes = $repository->findAll();
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('note/dir-edit-note.html.twig', [
            'form' => $form->createView(),
            'notes' => $notes,
            'user' => $user,
            'note' => $note,  
            'notifications' => $notifications,
            'nbNotif' => $nbNotif         
        ]);
    }

    #[Route("/note/editbysd/{id?0}", name: "sd-note.edit")]
    public function editNotebysd(
        UserInterface $user,
        Note $note = null,
        EntityManagerInterface $entityManager,
        Request $request,
        NotificationRepository $notificationRepository
    ): Response {
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB','ROLE_DIR', 'ROLE_SD'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            $this->addFlash('error',"rôle innexistant");
        }
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
            $this->addFlash('error','accès réfusé');
        }
         // Récupérer l'ID de la personne à partir de l'utilisateur

        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
            
        }
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);

        if ($note->getStatut() !== Note::STATUT_EN_ATTENTE) {
            $this->addFlash('error',"Impossible de modifier la note de service !");
        }

        $new = false;
        if (!$note) {
            $note = new Note();
            $new = true;
        }

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setStatut("en attente");
            if ($new) {
                $personne->addNote($note);
                $entityManager->persist($note);
            }

            $entityManager->flush();

            $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
            $this->addFlash("success", $note->getNumref() . ' ' . $message);
            // Redirection vers la route spécifique en fonction du rôle
            if ($this->isGranted('ROLE_GRH')) {
                return $this->redirectToRoute('grh-note.liste');
            } elseif ($this->isGranted('ROLE_DIRCAB')) {
                return $this->redirectToRoute('dircab-note.liste');
            }elseif ($this->isGranted('ROLE_DIR')) {
                return $this->redirectToRoute('dir-note.liste');
            }elseif ($this->isGranted('ROLE_SD')) {
                return $this->redirectToRoute('sd-note.liste');
            }
        }

        $repository = $entityManager->getRepository(Note::class);
        $notes = $repository->findAll();
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        return $this->render('note/sd-edit-note.html.twig', [
            'form' => $form->createView(),
            'notes' => $notes,
            'user' => $user,
            'note' => $note,  
            'notifications' => $notifications,
            'nbNotif' => $nbNotif         
        ]);
    }

    #[Route("/note/diffusion/{id?0}", name: "note.diffusion")]
    public function noteDiffusion(Note $note, Request $request, EntityManagerInterface $entityManager, NotifierInterface $notifier)
    {
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB','ROLE_DIR', 'ROLE_SD'];
        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            $this->addFlash('error','rôle innexistant');
        }
        $form = $this->createFormBuilder()
            ->add('destinataires', EntityType::class, [
                'class' => Personne::class, // Remplacez par la classe de votre entité Personne
                // 'choices' => $note->getDestinataire()->toArray(),
                'multiple' => true,
                'choice_label' => 'matricule', // Remplacez par la propriété que vous voulez afficher pour chaque destinataire
                'expanded' => true,
                'by_reference' => false,
            ])
            ->add('diffuser', SubmitType::class, ['label' => 'Diffuser'])
            ->getForm();
        

        $form->handleRequest($request);

        $note->setStatut(Note::STATUT_DIFFUSEE);
        if ($form->isSubmitted() && $form->isValid()) {

        }

        $message = "Note diffusée avec succès";
        $this->addFlash("success", $note->getNumref() . ' ' . $message);
        // Redirection vers la route spécifique en fonction du rôle
        if ($this->isGranted('ROLE_GRH')) {
            return $this->redirectToRoute('grh-note.liste');
        } elseif ($this->isGranted('ROLE_DIRCAB')) {
            return $this->redirectToRoute('dircab-note.liste');
        }elseif ($this->isGranted('ROLE_DIR')) {
            return $this->redirectToRoute('dir-note.liste');
        }elseif ($this->isGranted('ROLE_SD')) {
            return $this->redirectToRoute('sd-note.liste');
        }
        
        return $this->render('note/diffusion.html.twig', [
            'note' => $note,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/note/document/{id}", name: "note.doc")]
    public function template(Request $request,ManagerRegistry $doctrine,UserInterface $user,Note $note,$id)
    {
        $selectedDestinataires = $note->getDestinataire();
        $note = $doctrine->getRepository(Note::class)->find($id);
        return $this->render('note/doc-note.html.twig',[
            'selectedDestinataires' => $selectedDestinataires,
            'user' =>$user,
            'note' => $note
        ]);
    }

    #[Route("/note/consulter/{id}", name: "note.consulter")]
    public function consulterNote(Request $request, ManagerRegistry $doctrine, UserInterface $user, Note $note, $id)
    {
        // Vérifiez si l'utilisateur actuel est un destinataire sélectionné
        $selectedDestinataires = $note->getDestinataire();

        if (!$selectedDestinataires->contains($note->getDestinataire())) {
            $this->addFlash('error',"Cette Note ne vous ai pas destinée !");
            // return $this->redirectToRoute('note.afficher');
        }

        // L'utilisateur actuel est un destinataire sélectionné, vous pouvez afficher la note.
        return $this->render('note/consulter-note.html.twig', [
            'destinataires' => $selectedDestinataires,
            'user' => $user,
            'note' => $note,
        ]);
    }

    #[Route('note/mesNotes/{id?0}', name: 'note.afficher')]
    public function afficherNotes(
        EntityManagerInterface $entityManager,
        Request $request,
        UserInterface $user,
        NotificationRepository $notificationRepository,
        int $id
    ): Response {
        if (!$user) {
            $this->addFlash('error', "Accès réfusé ! Vous devez avoir un compte");
        }

        // Récupérer l'ID de la personne à partir de l'utilisateur

        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
        }

        $personne = $entityManager->getRepository(Personne::class)->find($personneId);

        if (!$personne) {
            $this->addFlash('error', "Agent introuvable !");
        }

        $repository = $entityManager->getRepository(Note::class);
        $notes = $personne->getNote();
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        $note = $entityManager->getRepository(Note::class)->find($id);
        // $selectedDestinataires = $note->getDestinataire(); // Utilisez cette ligne pour obtenir les destinataires de la note

        // if (!$selectedDestinataires->contains($personne)) {
        //     $this->addFlash('error', "Cette Note ne vous ai pas destinée !");
        //     return $this->redirectToRoute('note.afficher'); // Redirigez l'utilisateur où vous le souhaitez en cas d'erreur.
        // }

        return $this->render('note/mes-notes.html.twig', [
            // 'destinataires' => $selectedDestinataires,
            'personne' => $personne,
            'notes' => $notes,
            'user' => $user,
            'notifications' => $notifications,
            'nbNotif' => $nbNotif
        ]);
    }


    #[Route('grh/note/liste/{id?0}', name: 'grh-note.liste')]
    public function GrhNotes(
        $id,
        EntityManagerInterface $entityManager,
        Request $request,
        UserInterface $user,
        NotificationRepository $notificationRepository,
    ): Response {
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB', 'ROLE_MIN', 'ROLE_DIR', 'ROLE_CS', 'ROLE_SD'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            $this->addFlash('error','Vous n\'avez pas la permission d\'accéder à cette page.');
        }
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
            $this->addFlash('error','Accès refusé.');
         }
    
         // Récupérer l'ID de la personne à partir de l'utilisateur
        
        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
        }
         
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);  

        $repository = $entityManager->getRepository(Note::class);
        $nbNote = $repository->count([]);
        $notes = $personne->getNote();
        
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        
        $nbNotif = count($notifications);

        $services = $entityManager->getRepository(Personne::class)->createQueryBuilder('p')
        ->select('DISTINCT s')
        ->from(Service::class, 's')
        ->getQuery()
        ->getResult();

        $sousDirections = $entityManager->getRepository(Personne::class)->createQueryBuilder('p')
        ->select('DISTINCT sd')
        ->from(SousDirection::class, 'sd')
        ->getQuery()
        ->getResult();

        $directions = $entityManager->getRepository(Personne::class)->createQueryBuilder('p')
        ->select('DISTINCT d')
        ->from(Direction::class, 'd')
        ->getQuery()
        ->getResult();


        $form = $this->createFormBuilder()
        ->add('destinataires', ChoiceType::class, [
            'attr' => ['class' => 'choice'],
            'choices' => [
                'service' => $services, 
                'sousdirection' => $sousDirections, 
                'direction' => $directions
            ],
            'multiple' => true, 
            'expanded' => false, 
            'choice_label' => function($value, $key, $index) {
                return $value->getDesignation(); 
            },
            'by_reference' => false,
        ])
        
        ->add('diffuser', SubmitType::class, ['label' => 'Diffuser'])
        ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $selectedDestinataires = $form->get('destinataires')->getData();
            $agents = [];
    
            foreach ($selectedDestinataires as $destinataire) {
                if ($destinataire instanceof Service) {
                    // Récupére les agents du service
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                } elseif ($destinataire instanceof SousDirection) {
                    // Récupére les agents de la sous-direction
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                } elseif ($destinataire instanceof Direction) {
                    // Récupére les agents de la direction
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                }
            }
    
            // fonction de comparaison personnalisée pour trier les objets par ID
            $compareById = function($a, $b) {
                return $a->getId() - $b->getId();
            };

            // Trier les agents en utilisant la fonction de comparaison personnalisée
            usort($agents, $compareById);

            // Supprimer les doublons en comparant les agents par ID
            $uniqueAgents = [];
            $prevAgent = null;
            foreach ($agents as $agent) {
                if ($prevAgent === null || $compareById($agent, $prevAgent) !== 0) {
                    $uniqueAgents[] = $agent;
                }
                $prevAgent = $agent;
            }

            // Envoyer des notifications à tous les agents sélectionnés
            foreach ($uniqueAgents as $agent) {
                $notification = new Notification();
                $notification->setMessage('Une nouvelle note est disponible pour vous.');
                $notification->addDestinataire($agent);
                $notification->setDateenvoi(new \DateTime());
                $entityManager->persist($notification);
            }
            // foreach ($selectedDestinataires as $destinataire) {
            //     $notes->addDestinataire($destinataire);
            //     $entityManager->persist($notes);
            // }
            $entityManager->flush();

        }
    
        
        
        return $this->render('note/liste-grh.html.twig', [
            'notes' => $notes,
            'nbNote' => $nbNote,
            'user' => $user,
            'personne' => $personne,
            'form' => $form->createView(),
            'notifications' => $notifications,
            'nbNotif' => $nbNotif

        ]);
    }

    #[Route('notedircab/liste/{id?0}', name: 'dircab-note.liste')]
    public function DirCABNotes(
        Note $note = null,
        EntityManagerInterface $entityManager,
        Request $request,
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
            $this->addFlash('error','Vous n\'avez pas la permission d\'accéder à cette page.');
        }
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
            $this->addFlash('error','Accès refusé.');
         }
    
         // Récupérer l'ID de la personne à partir de l'utilisateur
        
        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
        }
         
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);  
        if (!$personne) {
            throw $this->createNotFoundException('Personne non trouvée.');
        }
        $repository = $entityManager->getRepository(Note::class);
        $nbNote = $repository->count([]);
        $notes = $personne->getNote();
        
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        $form = $this->createFormBuilder()
        ->add('destinataires', EntityType::class, [
            'class' => Personne::class, 
            // 'choices' => $note->getDestinataire()->toArray(),
            'multiple' => true,
            'choice_label' => 'matricule',
            'expanded' => true,
            'by_reference' => false,
        ])
        ->add('diffuser', SubmitType::class, ['label' => 'Diffuser'])
        ->getForm();
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $selectedDestinataires = $form->get('destinataires')->getData();
            $agents = [];
    
            foreach ($selectedDestinataires as $destinataire) {
                if ($destinataire instanceof Service) {
                    // Récupére les agents du service
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                } elseif ($destinataire instanceof SousDirection) {
                    // Récupére les agents de la sous-direction
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                } elseif ($destinataire instanceof Direction) {
                    // Récupére les agents de la direction
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                }
            }
    
            // fonction de comparaison personnalisée pour trier les objets par ID
            $compareById = function($a, $b) {
                return $a->getId() - $b->getId();
            };

            // Trier les agents en utilisant la fonction de comparaison personnalisée
            usort($agents, $compareById);

            // Supprimer les doublons en comparant les agents par ID
            $uniqueAgents = [];
            $prevAgent = null;
            foreach ($agents as $agent) {
                if ($prevAgent === null || $compareById($agent, $prevAgent) !== 0) {
                    $uniqueAgents[] = $agent;
                }
                $prevAgent = $agent;
            }

            // Envoyer des notifications à tous les agents sélectionnés
            foreach ($uniqueAgents as $agent) {
                $notification = new Notification();
                $notification->setMessage('Une nouvelle note est disponible pour vous.');
                $notification->addDestinataire($agent);
                $notification->setDateenvoi(new \DateTime());
                $entityManager->persist($notification);
            }

    
            $entityManager->flush();

        }
    
        
        
        return $this->render('note/liste-dircab.html.twig', [
            'notes' => $notes,
            'nbNote' => $nbNote,
            'user' => $user,
            'personne' => $personne,
            'form' => $form->createView(),
            'notifications' => $notifications,
            'nbNotif' => $nbNotif

        ]);
    }

    #[Route('notedir/liste/{id?0}', name: 'dir-note.liste')]
    public function DirNotes(
        Note $note = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UserInterface $user,
        NotificationRepository $notificationRepository
    ): Response {
    
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB', 'ROLE_MIN', 'ROLE_DIR', 'ROLE_CS', 'ROLE_SD'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            $this->addFlash('error','Vous n\'avez pas la permission d\'accéder à cette page.');
        }
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
            $this->addFlash('error','Accès refusé.');
         }
    
         // Récupérer l'ID de la personne à partir de l'utilisateur
        
        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
        }
         
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);  
        if (!$personne) {
            throw $this->createNotFoundException('Personne non trouvée.');
        }
        $repository = $entityManager->getRepository(Note::class);
        $nbNote = $repository->count([]);
        $notes = $personne->getNote();
        
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        $form = $this->createFormBuilder()
        ->add('destinataires', EntityType::class, [
            'class' => Personne::class, 
            // 'choices' => $note->getDestinataire()->toArray(),
            'multiple' => true,
            'choice_label' => 'matricule',
            'expanded' => true,
            'by_reference' => false,
        ])
        ->add('diffuser', SubmitType::class, ['label' => 'Diffuser'])
        ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $selectedDestinataires = $form->get('destinataires')->getData();
            $agents = [];
    
            foreach ($selectedDestinataires as $destinataire) {
                if ($destinataire instanceof Service) {
                    // Récupére les agents du service
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                } elseif ($destinataire instanceof SousDirection) {
                    // Récupére les agents de la sous-direction
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                } elseif ($destinataire instanceof Direction) {
                    // Récupére les agents de la direction
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                }
            }
    
            // fonction de comparaison personnalisée pour trier les objets par ID
            $compareById = function($a, $b) {
                return $a->getId() - $b->getId();
            };

            // Trier les agents en utilisant la fonction de comparaison personnalisée
            usort($agents, $compareById);

            // Supprimer les doublons en comparant les agents par ID
            $uniqueAgents = [];
            $prevAgent = null;
            foreach ($agents as $agent) {
                if ($prevAgent === null || $compareById($agent, $prevAgent) !== 0) {
                    $uniqueAgents[] = $agent;
                }
                $prevAgent = $agent;
            }

            // Envoyer des notifications à tous les agents sélectionnés
            foreach ($uniqueAgents as $agent) {
                $notification = new Notification();
                $notification->setMessage('Une nouvelle note est disponible pour vous.');
                $notification->addDestinataire($agent);
                $notification->setDateenvoi(new \DateTime());
                $entityManager->persist($notification);
            }

    
            $entityManager->flush();

        }
    
        
        
        return $this->render('note/liste-dir.html.twig', [
            'notes' => $notes,
            'nbNote' => $nbNote,
            'user' => $user,
            'personne' => $personne,
            'form' => $form->createView(),
            'notifications' => $notifications,
            'nbNotif' => $nbNotif

        ]);
    }

    #[Route('notesd/liste/{id?0}', name: 'sd-note.liste')]
    public function SdNotes(
        Note $note = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UserInterface $user,
        NotificationRepository $notificationRepository
    ): Response {
    
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB', 'ROLE_MIN', 'ROLE_DIR', 'ROLE_CS', 'ROLE_SD'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            $this->addFlash('error','Vous n\'avez pas la permission d\'accéder à cette page.');
        }
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
            $this->addFlash('error','Accès refusé.');
         }
    
         // Récupérer l'ID de la personne à partir de l'utilisateur
        
        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
        }
         
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);  
        if (!$personne) {
            throw $this->createNotFoundException('Personne non trouvée.');
        }
        $repository = $entityManager->getRepository(Note::class);
        $nbNote = $repository->count([]);
        $notes = $personne->getNote();
        
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        $nbNotif = count($notifications);
        $form = $this->createFormBuilder()
        ->add('destinataires', EntityType::class, [
            'class' => Personne::class, 
            // 'choices' => $note->getDestinataire()->toArray(),
            'multiple' => true,
            'choice_label' => 'matricule',
            'expanded' => true,
            'by_reference' => false,
        ])
        ->add('diffuser', SubmitType::class, ['label' => 'Diffuser'])
        ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $selectedDestinataires = $form->get('destinataires')->getData();
            $agents = [];
    
            foreach ($selectedDestinataires as $destinataire) {
                if ($destinataire instanceof Service) {
                    // Récupére les agents du service
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                } elseif ($destinataire instanceof SousDirection) {
                    // Récupére les agents de la sous-direction
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                } elseif ($destinataire instanceof Direction) {
                    // Récupére les agents de la direction
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                }
            }
    
            // fonction de comparaison personnalisée pour trier les objets par ID
            $compareById = function($a, $b) {
                return $a->getId() - $b->getId();
            };

            // Trier les agents en utilisant la fonction de comparaison personnalisée
            usort($agents, $compareById);

            // Supprimer les doublons en comparant les agents par ID
            $uniqueAgents = [];
            $prevAgent = null;
            foreach ($agents as $agent) {
                if ($prevAgent === null || $compareById($agent, $prevAgent) !== 0) {
                    $uniqueAgents[] = $agent;
                }
                $prevAgent = $agent;
            }

            // Envoyer des notifications à tous les agents sélectionnés
            foreach ($uniqueAgents as $agent) {
                $notification = new Notification();
                $notification->setMessage('Une nouvelle note est disponible pour vous.');
                $notification->addDestinataire($agent);
                $notification->setDateenvoi(new \DateTime());
                $entityManager->persist($notification);
            }

    
            $entityManager->flush();

        }
    
        
        
        return $this->render('note/liste-sd.html.twig', [
            'notes' => $notes,
            'nbNote' => $nbNote,
            'user' => $user,
            'personne' => $personne,
            'form' => $form->createView(),
            'notifications' => $notifications,
            'nbNotif' => $nbNotif

        ]);
    }

    #[Route('notenondiffuseegrh/liste/{id?0}', name: 'grh-note2.liste')]
    public function GrhNotesNonDiffusee(
        $id,
        Note $note,
        EntityManagerInterface $entityManager,
        Request $request,
        UserInterface $user,
        NotificationRepository $notificationRepository,
    ): Response {
    
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB', 'ROLE_MIN', 'ROLE_DIR', 'ROLE_CS', 'ROLE_SD'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            $this->addFlash('error','Vous n\'avez pas la permission d\'accéder à cette page.');
        }
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
            $this->addFlash('error','Accès refusé.');
         }
    
         // Récupérer l'ID de la personne à partir de l'utilisateur
        
        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
        }
        
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);  
        if (!$personne) {
            throw $this->createNotFoundException('Personne non trouvée.');
        }
        $repository = $entityManager->getRepository(Note::class);
        $nbNote = $repository->count([]);
        $notes = $personne->getNote()->filter(function ($note) {
            return $note->getStatut() === 'en attente';
        });
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        
        $nbNotif = count($notifications);
        $form = $this->createFormBuilder()
        ->add('destinataires', EntityType::class, [
            'class' => Personne::class, 
            // 'choices' => $note->getDestinataire()->toArray(),
            'multiple' => true,
            'choice_label' => 'matricule',
            'expanded' => true,
            'by_reference' => false,
        ])
        ->add('diffuser', SubmitType::class, ['label' => 'Diffuser'])
        ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $selectedDestinataires = $form->get('destinataires')->getData();
            $agents = [];
    
            foreach ($selectedDestinataires as $destinataire) {
                if ($destinataire instanceof Service) {
                    // Récupére les agents du service
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                } elseif ($destinataire instanceof SousDirection) {
                    // Récupére les agents de la sous-direction
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                } elseif ($destinataire instanceof Direction) {
                    // Récupére les agents de la direction
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                }
            }
    
            // fonction de comparaison personnalisée pour trier les objets par ID
            $compareById = function($a, $b) {
                return $a->getId() - $b->getId();
            };

            // Trier les agents en utilisant la fonction de comparaison personnalisée
            usort($agents, $compareById);

            // Supprimer les doublons en comparant les agents par ID
            $uniqueAgents = [];
            $prevAgent = null;
            foreach ($agents as $agent) {
                if ($prevAgent === null || $compareById($agent, $prevAgent) !== 0) {
                    $uniqueAgents[] = $agent;
                }
                $prevAgent = $agent;
            }

            // Envoyer des notifications à tous les agents sélectionnés
            foreach ($uniqueAgents as $agent) {
                $notification = new Notification();
                $notification->setMessage('Une nouvelle note est disponible pour vous.');
                $notification->addDestinataire($agent);
                $notification->setDateenvoi(new \DateTime());
                $entityManager->persist($notification);
            }

            
            $entityManager->flush();

        }

        return $this->render('note/liste2-grh.html.twig', [
            'notes' => $notes,
            'nbNote' => $nbNote,
            'user' => $user,
            'personne' => $personne,
            'form' => $form->createView(),
            'notifications' => $notifications,
            'nbNotif' => $nbNotif

        ]);
    }

    #[Route('notenondiffuseedir/liste/{id?0}', name: 'dir-note2.liste')]
    public function DirNotesNonDiffusee(
        $id,
        EntityManagerInterface $entityManager,
        Request $request,
        UserInterface $user,
        NotificationRepository $notificationRepository,
    ): Response {
    
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB', 'ROLE_MIN', 'ROLE_DIR', 'ROLE_CS', 'ROLE_SD'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            $this->addFlash('error','Vous n\'avez pas la permission d\'accéder à cette page.');
        }
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
            $this->addFlash('error','Accès refusé.');
         }
    
         // Récupérer l'ID de la personne à partir de l'utilisateur
        
        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
        }
        
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);  
        if (!$personne) {
            throw $this->createNotFoundException('Personne non trouvée.');
        }
        $repository = $entityManager->getRepository(Note::class);
        $nbNote = $repository->count([]);
        $notes = $personne->getNote()->filter(function ($note) {
            return $note->getStatut() === 'en attente';
        });
        
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        
        $nbNotif = count($notifications);
        $form = $this->createFormBuilder()
        ->add('destinataires', EntityType::class, [
            'class' => Personne::class, 
            // 'choices' => $note->getDestinataire()->toArray(),
            'multiple' => true,
            'choice_label' => 'matricule',
            'expanded' => true,
            'by_reference' => false,
        ])
        ->add('diffuser', SubmitType::class, ['label' => 'Diffuser'])
        ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $selectedDestinataires = $form->get('destinataires')->getData();
            $agents = [];
    
            foreach ($selectedDestinataires as $destinataire) {
                if ($destinataire instanceof Service) {
                    // Récupére les agents du service
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                } elseif ($destinataire instanceof SousDirection) {
                    // Récupére les agents de la sous-direction
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                } elseif ($destinataire instanceof Direction) {
                    // Récupére les agents de la direction
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                }
            }
    
            // fonction de comparaison personnalisée pour trier les objets par ID
            $compareById = function($a, $b) {
                return $a->getId() - $b->getId();
            };

            // Trier les agents en utilisant la fonction de comparaison personnalisée
            usort($agents, $compareById);

            // Supprimer les doublons en comparant les agents par ID
            $uniqueAgents = [];
            $prevAgent = null;
            foreach ($agents as $agent) {
                if ($prevAgent === null || $compareById($agent, $prevAgent) !== 0) {
                    $uniqueAgents[] = $agent;
                }
                $prevAgent = $agent;
            }

            // Envoyer des notifications à tous les agents sélectionnés
            foreach ($uniqueAgents as $agent) {
                $notification = new Notification();
                $notification->setMessage('Une nouvelle note est disponible pour vous.');
                $notification->addDestinataire($agent);
                $notification->setDateenvoi(new \DateTime());
                $entityManager->persist($notification);
            }

            
            $entityManager->flush();

        }

        return $this->render('note/liste2-dir.html.twig', [
            'notes' => $notes,
            'nbNote' => $nbNote,
            'user' => $user,
            'personne' => $personne,
            'form' => $form->createView(),
            'notifications' => $notifications,
            'nbNotif' => $nbNotif

        ]);
    }

    #[Route('notenondiffuseedircab/liste/{id?0}', name: 'dircab-note2.liste')]
    public function DircabNotesNonDiffusee(
        $id,
        EntityManagerInterface $entityManager,
        Request $request,
        UserInterface $user,
        NotificationRepository $notificationRepository,
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
            $this->addFlash('error','Vous n\'avez pas la permission d\'accéder à cette page.');
        }
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
            $this->addFlash('error','Accès refusé.');
         }
    
         // Récupérer l'ID de la personne à partir de l'utilisateur
        
        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
        }
        
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);  
        if (!$personne) {
            throw $this->createNotFoundException('Personne non trouvée.');
        }
        $repository = $entityManager->getRepository(Note::class);
        $nbNote = $repository->count([]);
        $notes = $personne->getNote()->filter(function ($note) {
            return $note->getStatut() === 'en attente';
        });
        
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        
        $nbNotif = count($notifications);
        $form = $this->createFormBuilder()
        ->add('destinataires', EntityType::class, [
            'class' => Personne::class, 
            // 'choices' => $note->getDestinataire()->toArray(),
            'multiple' => true,
            'choice_label' => 'matricule',
            'expanded' => true,
            'by_reference' => false,
        ])
        ->add('diffuser', SubmitType::class, ['label' => 'Diffuser'])
        ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $selectedDestinataires = $form->get('destinataires')->getData();
            $agents = [];
    
            foreach ($selectedDestinataires as $destinataire) {
                if ($destinataire instanceof Service) {
                    // Récupére les agents du service
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                } elseif ($destinataire instanceof SousDirection) {
                    // Récupére les agents de la sous-direction
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                } elseif ($destinataire instanceof Direction) {
                    // Récupére les agents de la direction
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                }
            }
    
            // fonction de comparaison personnalisée pour trier les objets par ID
            $compareById = function($a, $b) {
                return $a->getId() - $b->getId();
            };

            // Trier les agents en utilisant la fonction de comparaison personnalisée
            usort($agents, $compareById);

            // Supprimer les doublons en comparant les agents par ID
            $uniqueAgents = [];
            $prevAgent = null;
            foreach ($agents as $agent) {
                if ($prevAgent === null || $compareById($agent, $prevAgent) !== 0) {
                    $uniqueAgents[] = $agent;
                }
                $prevAgent = $agent;
            }

            // Envoyer des notifications à tous les agents sélectionnés
            foreach ($uniqueAgents as $agent) {
                $notification = new Notification();
                $notification->setMessage('Une nouvelle note est disponible pour vous.');
                $notification->addDestinataire($agent);
                $notification->setDateenvoi(new \DateTime());
                $entityManager->persist($notification);
            }

            
            $entityManager->flush();

        }

        return $this->render('note/liste2-dircab.html.twig', [
            'notes' => $notes,
            'nbNote' => $nbNote,
            'user' => $user,
            'personne' => $personne,
            'form' => $form->createView(),
            'notifications' => $notifications,
            'nbNotif' => $nbNotif

        ]);
    }

    #[Route('notenondiffuseesd/liste/{id?0}', name: 'sd-note2.liste')]
    public function SdNotesNonDiffusee(
        $id,
        EntityManagerInterface $entityManager,
        Request $request,
        UserInterface $user,
        NotificationRepository $notificationRepository,
    ): Response {
    
        $roles = ['ROLE_GRH', 'ROLE_DIRCAB', 'ROLE_MIN', 'ROLE_DIR', 'ROLE_CS', 'ROLE_SD'];

        $granted = false;
        foreach ($roles as $role) {
            if ($this->isGranted($role)) {
                $granted = true;
                break;
            }
        }

        if (!$granted) {
            $this->addFlash('error','Vous n\'avez pas la permission d\'accéder à cette page.');
        }
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
            $this->addFlash('error','Accès refusé.');
         }
    
         // Récupérer l'ID de la personne à partir de l'utilisateur
        
        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
        }
        
        $personne = $entityManager->getRepository(Personne::class)->find($personneId);  
        if (!$personne) {
            throw $this->createNotFoundException('Personne non trouvée.');
        }
        $repository = $entityManager->getRepository(Note::class);
        $nbNote = $repository->count([]);
        $notes = $personne->getNote()->filter(function ($note) {
            return $note->getStatut() === 'en attente';
        });
        
        $notifications = $notificationRepository->findNotificationsForUser($user->getPersonne());
        
        $nbNotif = count($notifications);
        $form = $this->createFormBuilder()
        ->add('destinataires', EntityType::class, [
            'class' => Personne::class, 
            // 'choices' => $note->getDestinataire()->toArray(),
            'multiple' => true,
            'choice_label' => 'matricule',
            'expanded' => true,
            'by_reference' => false,
        ])
        ->add('diffuser', SubmitType::class, ['label' => 'Diffuser'])
        ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $selectedDestinataires = $form->get('destinataires')->getData();
            $agents = [];
    
            foreach ($selectedDestinataires as $destinataire) {
                if ($destinataire instanceof Service) {
                    // Récupére les agents du service
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                } elseif ($destinataire instanceof SousDirection) {
                    // Récupére les agents de la sous-direction
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                } elseif ($destinataire instanceof Direction) {
                    // Récupére les agents de la direction
                    $agents = array_merge($agents, $destinataire->getPersonnes()->toArray());
                }
            }
    
            // fonction de comparaison personnalisée pour trier les objets par ID
            $compareById = function($a, $b) {
                return $a->getId() - $b->getId();
            };

            // Trier les agents en utilisant la fonction de comparaison personnalisée
            usort($agents, $compareById);

            // Supprimer les doublons en comparant les agents par ID
            $uniqueAgents = [];
            $prevAgent = null;
            foreach ($agents as $agent) {
                if ($prevAgent === null || $compareById($agent, $prevAgent) !== 0) {
                    $uniqueAgents[] = $agent;
                }
                $prevAgent = $agent;
            }

            // Envoyer des notifications à tous les agents sélectionnés
            foreach ($uniqueAgents as $agent) {
                $notification = new Notification();
                $notification->setMessage('Une nouvelle note est disponible pour vous.');
                $notification->addDestinataire($agent);
                $notification->setDateenvoi(new \DateTime());
                $entityManager->persist($notification);
            }

            
            $entityManager->flush();

        }

        return $this->render('note/liste2-sd.html.twig', [
            'notes' => $notes,
            'nbNote' => $nbNote,
            'user' => $user,
            'personne' => $personne,
            'form' => $form->createView(),
            'notifications' => $notifications,
            'nbNotif' => $nbNotif

        ]);
    }

    #[Route('/note/supprimer/{personneId}/{noteId}', name: 'note.supprimer')]
    public function supprimerNote(
        ManagerRegistry $doctrine,
        Request $request,
        $personneId,
        $noteId,
        UserInterface $user
    ): Response {
        $roles = 'ROLE_USER';
        $this->denyAccessUnlessGranted($roles, null, 'Vous n\'avez pas la permission d\'accéder à cette page.');
        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }
       
        if ($user && $user->getPersonne()) {
            $personneId = $user->getPersonne()->getId();
        }
        $personne = $doctrine->getRepository(Personne::class)->find($personneId);

        if (!$personne) {
            throw $this->createNotFoundException('Personne non trouvée.');
        }

        $note = $doctrine->getRepository(Note::class)->find($noteId);

        if (!$note) {
            throw $this->createNotFoundException('Déclaration non trouvée.');
        }

        if ($note->getPersonne() !== $personne) {
            throw $this->createNotFoundException('La déclaration ne correspond pas à la personne spécifiée.');
        }

            $personne->removeNote($note);

            $entityManager = $doctrine->getManager();
            $entityManager->remove($note);
            $entityManager->flush();
            $message ="a été supprimée avec succès";
            $this->addFlash("success",'La Note de service de référence '. $note->getNumref() . ' ' . $message);
            // Redirection vers la route spécifique en fonction du rôle
            if ($this->isGranted('ROLE_GRH')) {
                return $this->redirectToRoute('grh-note.liste');
            } elseif ($this->isGranted('ROLE_DIRCAB')) {
                return $this->redirectToRoute('dircab-note.liste');
            }elseif ($this->isGranted('ROLE_DIR')) {
                return $this->redirectToRoute('dir-note.liste');
            }elseif ($this->isGranted('ROLE_SD')) {
                return $this->redirectToRoute('sd-note.liste');
            }
    }

}

