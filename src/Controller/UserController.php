<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/admin/add-user', name: 'add.user', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function addUser(): Response
    {


        return $this->redirectToRoute('show.user');
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