<?php

namespace App\Controller;

use App\Entity\Role;
use App\Form\RoleType;
// use App\Service\MailerService;
use App\Service\UploaderService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RoleController extends AbstractController
{
    #[Route('/admin', name: 'admin.page')]
    public function index(): Response
    {
        return $this->render('role/admin-template.html.twig', [
            'controller_name' => 'RoleController',
        ]);
    }

    #[Route('role/liste/{id?0}', name: 'role.liste')]
    public function indexAll(
    Role $role = null,
    UserInterface $user,
    EntityManagerInterface $entityManager,
    Request $request,
): Response {
    $new = false;
    if (!$role) {
        $role = new Role();
        $new = true;
    }

    $form = $this->createForm(RoleType::class, $role);
    $form->remove('createdAt');
    $form->remove('updatedAt');

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        if ($new) {
            $entityManager->persist($role);
        }

        $entityManager->flush();

        $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
        $this->addFlash("success", $role->getLibelle() . ' ' . $message);
        return $this->redirectToRoute('role.liste');
    }

    $repository = $entityManager->getRepository(Role::class);
    $roles = $repository->findAll();

    return $this->render('role/liste-role.html.twig', [
        'form' => $form->createView(),
        'roles' => $roles,
        'user' => $user
    ]);
}

    #[Route("/role/delete-multiple", name:"role.delete_multiple", methods:["POST"])]
    public function deleteMultiple(Request $request, EntityManagerInterface $entityManager): Response
    {
        $rolesIds = $request->request->get('roles');

        if (!empty($rolesIds)) {
            $repository = $entityManager->getRepository(Role::class);
            $roles = $repository->findBy(['id' => $rolesIds]);

            foreach ($roles as $role) {
                $entityManager->remove($role);
            }

            $entityManager->flush();

            return new JsonResponse(['message' => 'Suppression réussie'], Response::HTTP_OK);
        }

        return new JsonResponse(['message' => 'Aucun élément sélectionné'], Response::HTTP_BAD_REQUEST);
    }


    #[Route('role/edit/{id?0}', name: 'role.edit')]
    public function edit(
    Role $role = null,
    UserInterface $user,
    EntityManagerInterface $entityManager,
    Request $request,
): Response {
    $new = false;
    if (!$role) {
        $role = new Role();
        $new = true;
    }

    $form = $this->createForm(RoleType::class, $role);
    $form->remove('createdAt');
    $form->remove('updatedAt');

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        if ($new) {
            $entityManager->persist($role);
        }

        $entityManager->flush();

        $message = $new ? "a été ajouté avec succès" : "a été mis à jour avec succès";
        $this->addFlash("success", $role->getLibelle() . ' ' . $message);
        return $this->redirectToRoute('role.liste');
    }

    $repository = $entityManager->getRepository(Role::class);
    $roles = $repository->findAll();

    return $this->render('role/edit-role.html.twig', [
        'form' => $form->createView(),
        'roles' => $roles,
        'user' => $user
    ]);
}
    #[Route('/role/{id<\d+>}', name: 'role.detail')]
    public function detail(Role $role = null,UserInterface $user){    
        if (!$role) {
            $this->addFlash('error',"Le role n'existe pas");
            return $this->redirectToRoute('role.liste');

        }
        return $this->render('role/show-information.html.twig',['role'=>$role,'user' => $user]);

    }

    #[Route('/role/delete/{id}', name: 'role.delete')]
    public function delete(Role $role = null,ManagerRegistry $doctrine):RedirectResponse {
        if($role){
            $manager = $doctrine->getManager();
            $manager->remove($role);
            $manager->flush();
            $this->addFlash('success','role supprimée avec succès');
        }else{
            $this->addFlash('error','role innexistante');
        }
        return $this->redirectToRoute('role.liste');

    }
    
    
}
