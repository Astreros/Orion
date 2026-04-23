<?php

namespace App\Controller;

use App\Entity\Code;
use App\Entity\Utilisateur;
use App\Service\EncryptionService;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CodeController extends AbstractController
{
    /**
     * @throws \DateMalformedStringException
     * @throws RandomException
     */
    #[Route('/admin/add-code/{userId}', name: 'add.code', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function addCode(int $userId, EncryptionService $encryptionService, EntityManagerInterface $entityManager): Response
    {
        // Récupère l'utilisateur
        $user = $entityManager->getRepository(Utilisateur::class)->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Vérifie s'il existe déjà un code pour cet utilisateur et le supprime si oui
        $existeCode = $entityManager->getRepository(Code::class)
            ->findOneBy(['utilisateur' => $user]);

        if ($existeCode) {
            $entityManager->remove($existeCode);
            $entityManager->flush();
        }

        $date = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $dateExpiration = (clone $date)->add(new \DateInterval('P1Y'));

        $codePIN = random_int(100000, 999999);
        $hashedCodePIN = password_hash((string)$codePIN, PASSWORD_BCRYPT);

        // Créer et sauvegarder
        $code = new Code();
        $code->setUtilisateur($user);
        $code->setCodePIN($hashedCodePIN);
        $code->setDateCreation($date);
        $code->setDateExpiration($dateExpiration);
        $code->setActif(true);

        $entityManager->persist($code);
        $entityManager->flush();

        $this->addFlash('success', sprintf('Code ajouté avec succès. Le code PIN est : %s', $codePIN));
        return $this->redirectToRoute('show.code');
    }

    #[Route('/admin/disable-code/{id}', name: 'disable.code', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function disableCode(Code $code, EntityManagerInterface $entityManager): Response
    {
        $code->setActif(false);
        $entityManager->flush();

        $this->addFlash('success', 'Code désactivé avec succès.');
        return $this->redirectToRoute('show.code');
    }

    #[Route('/admin/enable-code/{id}', name: 'enable.code', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function enableCode(Code $code, EntityManagerInterface $entityManager): Response
    {
        $code->setActif(true);
        $entityManager->flush();

        $this->addFlash('success', 'Code activé avec succès.');
        return $this->redirectToRoute('show.code');
    }

    #[Route('/admin/delete-code/{id}', name: 'delete.code', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteCode(Code $code, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($code);
        $entityManager->flush();

        $this->addFlash('success', 'Code supprimé avec succès.');
        return $this->redirectToRoute('show.code');
    }
}