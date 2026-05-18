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
    public function updateUser(): Response
    {


        return $this->redirectToRoute('show.user');
    }

    #[Route('/admin/delete-user/{userId}', name: 'delete.user', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(): Response
    {


        return $this->redirectToRoute('show.user');
    }
}