<?php 
// src/Controller/NoteController.php

namespace App\Controller;

use App\Entity\Note;
use App\Form\NoteType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NoteController extends AbstractController
{

    #[Route("/note/edit", name: "note_edit", methods:["POST"])]
    public function edit(Request $request,ManagerRegistry $doctrine)
    {
        // Récupérer les données envoyées par la requête POST
        $elementId = $request->request->get('elementId');
        $newText = $request->request->get('newText');

        // Rechercher l'entité associée à l'élément modifié dans la base de données
        $entityManager = $doctrine->getManager();
        $note = $entityManager->getRepository(Note::class)->find($elementId);

        // Mettre à jour la propriété correspondante avec le nouveau texte
        $note->setContenu($newText);

        // Enregistrer les modifications dans la base de données
        $entityManager->flush();

        // Répondre avec une représentation JSON des données mises à jour
        return $this->json(['success' => true, 'message' => 'Données mises à jour avec succès']);
    }
}
