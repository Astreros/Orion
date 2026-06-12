<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/admin/add-user', name: 'add.user', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function addUser(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new Utilisateur();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('show.user');
        }

        return $this->render('admin/add_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/update-user/{userId}', name: 'update.user', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateUser(int $userId, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $entityManager->getRepository(Utilisateur::class)->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer le mot de passe (uniquement s'il est fourni)
            $plainPassword = $form->get('password')->getData();
            if (!empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('show.user');
        }

        $entityManager->flush();

        return $this->render('admin/add_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/disable-user/{id}', name: 'disable.user', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function disableUser(Utilisateur $user, EntityManagerInterface $entityManager): Response
    {
        $user->setActif(false);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur désactivé avec succès.');
        return $this->redirectToRoute('show.user');
    }

    #[Route('/admin/enable-user/{id}', name: 'enable.user', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function enableUser(Utilisateur $user, EntityManagerInterface $entityManager): Response
    {
        $user->setActif(true);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur activé avec succès.');
        return $this->redirectToRoute('show.user');
    }

    #[Route('/admin/delete-user/{id}', name: 'delete.user', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(Utilisateur $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        return $this->redirectToRoute('show.user');
    }
}