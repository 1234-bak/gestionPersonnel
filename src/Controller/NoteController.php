<?php 
namespace App\Controller;

use DateTime;
use App\Entity\Note;
use App\Entity\User;
use App\Form\NoteType;
use App\Entity\Personne;
use App\Entity\Notification;
use App\Form\DestinataireType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
// use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NoteController extends AbstractController
{
    #[Route("/note/create/", name: "note.create")]
    public function createNote(
        UserInterface $user,
        Note $note =null,
        EntityManagerInterface $entityManager,
        Request $request,
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
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
            die('accès réfusé');
        }
         // Récupérer l'ID de la personne à partir de l'utilisateur

            if ($user && $user->getPersonne()) {
                $personneId = $user->getPersonne()->getId();
                
            }
            $personne = $entityManager->getRepository(Personne::class)->find($personneId);
            // dd($personne);

        if (!$personne) {
            throw $this->createNotFoundException('Personne non trouvée.');
        }

        $note = new Note();

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

        return $this->render('note/create-note.html.twig', [
            'form' => $form->createView(),
            'notes' => $notes,
            'user' => $user,
            'note' => $note,
        ]);
    }

    #[Route("/note/edit/{id?0}", name: "note.edit")]
    public function editNote(
        UserInterface $user,
        Note $note = null,
        EntityManagerInterface $entityManager,
        Request $request,
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
            // dd($personne);

        if (!$personne) {
            throw $this->createNotFoundException('Personne non trouvée.');
        }
        $new = false;
        if (!$note) {
            $note = new Note();
            $new = true;
        }

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

        return $this->render('note/edit-note.html.twig', [
            'form' => $form->createView(),
            'notes' => $notes,
            'user' => $user,
            'note' => $note,           
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
                'choices' => $note->getDestinataire()->toArray(),
                'multiple' => true,
                'choice_label' => 'matricule', // Remplacez par la propriété que vous voulez afficher pour chaque destinataire
                'expanded' => true,
                'by_reference' => false,
            ])
            ->add('diffuser', SubmitType::class, ['label' => 'Diffuser'])
            ->getForm();
        

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedDestinataires = $form->get('destinataires')->getData();

            foreach ($selectedDestinataires as $destinataire) {
                $notification = (new ChatMessage())
                    ->subject('Nouvelle note disponible')
                    ->text('Une nouvelle note est disponible pour vous.');
                
                $notifier->send($destinataire, $notification);
            }

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
        $note = $doctrine->getRepository(Note::class)->find($id);
        return $this->render('note/doc-note.html.twig',[
            'user' =>$user,
            'note' => $note
        ]);
    }

    #[Route('grh/note/liste/{id?0}', name: 'grh-note.liste')]
    public function GrhNotes(
        $id,
        EntityManagerInterface $entityManager,
        Request $request,
        UserInterface $user, 
        NotifierInterface $notifier,
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
        $note = $entityManager->getRepository(Note::class)->find($id);

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
    
            foreach ($selectedDestinataires as $destinataire) {
            $notification = new Notification();
            $notification->setMessage('Une nouvelle note est disponible pour vous.');
            $notification->addDestinataire($destinataire);
            $notification->setDateenvoi(new \DateTime());
            $notification->setStatut('diffusée');
            $entityManager->persist($notification);
        }
        $entityManager->flush();
        // dd($notification);

        }
    
        
        
        return $this->render('note/liste-grh.html.twig', [
            'notes' => $notes,
            'nbNote' => $nbNote,
            'user' => $user,
            'personne' => $personne,
            'form' => $form->createView()

        ]);
    }

    #[Route('dircab/note/liste/{id?0}', name: 'dircab-note.liste')]
    public function DirCABNotes(
        Note $note = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UserInterface $user, // Ajout de UserInterface comme paramètre
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
            throw $this->createAccessDeniedException('Vous n\'avez pas la permission d\'accéder à cette page.');
        }
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
             throw $this->createAccessDeniedException('Accès refusé.');
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

        return $this->render('note/liste-dircab.html.twig', [
            'notes' => $notes,
            'nbNote' => $nbNote,
            'user' => $user,
            'personne' => $personne

        ]);
    }

    #[Route('dir/note/liste/{id?0}', name: 'dir-note.liste')]
    public function DirNotes(
        Note $note = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UserInterface $user, // Ajout de UserInterface comme paramètre
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
            throw $this->createAccessDeniedException('Vous n\'avez pas la permission d\'accéder à cette page.');
        }
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
             throw $this->createAccessDeniedException('Accès refusé.');
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

        return $this->render('note/liste-dir.html.twig', [
            'notes' => $notes,
            'nbNote' => $nbNote,
            'user' => $user,
            'personne' => $personne

        ]);
    }

    #[Route('sd/note/liste/{id?0}', name: 'sd-note.liste')]
    public function SdNotes(
        Note $note = null,
        EntityManagerInterface $entityManager,
        Request $request,
        UserInterface $user, // Ajout de UserInterface comme paramètre
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
            throw $this->createAccessDeniedException('Vous n\'avez pas la permission d\'accéder à cette page.');
        }
         // Vérifier si l'utilisateur est authentifié
         if (!$user) {
             throw $this->createAccessDeniedException('Accès refusé.');
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

        return $this->render('note/liste-sd.html.twig', [
            'notes' => $notes,
            'nbNote' => $nbNote,
            'user' => $user,
            'personne' => $personne

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
        return $this->redirectToRoute('note.liste', ['personneId' => $personneId,'noteId' => $noteId]);
    }
}

