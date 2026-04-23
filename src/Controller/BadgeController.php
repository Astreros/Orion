<?php

namespace App\Controller;

use App\Entity\Badge;
use App\Entity\Utilisateur;
use DateInterval;
use DateMalformedStringException;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BadgeController extends AbstractController
{
    /**
     * @throws DateMalformedStringException
     */
    #[Route('/admin/add-badge/{userId}/{UID}', name: 'add.badge', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function addBadge(int $userId, string $UID, EntityManagerInterface $entityManager): Response
    {
        // Récupère l'utilisateur
        $user = $entityManager->getRepository(Utilisateur::class)->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Vérifie s'il existe déjà un badge pour cet utilisateur et le supprime si oui
        $existeBadge = $entityManager->getRepository(Badge::class)
            ->findOneBy(['utilisateur' => $user]);

        if ($existeBadge) {
            $entityManager->remove($existeBadge);
            $entityManager->flush();
        }

        $date = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $dateExpiration = (clone $date)->add(new DateInterval('P1Y'));

        // Créer et sauvegarder
        $badge = new Badge();
        $badge->setUtilisateur($user);
        $badge->setRfid($UID);
        $badge->setDateCreation($date);
        $badge->setDateExpiration($dateExpiration);
        $badge->setActif(true);

        $entityManager->persist($badge);
        $entityManager->flush();

        // Redirige vers l'espace d'administration
        $this->addFlash('success', 'Badge ajouté avec succès.');
        return $this->redirectToRoute('show.badge');
    }

    #[Route('/admin/disable-badge/{id}', name: 'disable.badge', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function disableBadge(Badge $badge, EntityManagerInterface $entityManager): Response
    {
        $badge->setActif(false);
        $entityManager->flush();

        $this->addFlash('success', 'Badge désactivé avec succès.');
        return $this->redirectToRoute('show.badge');
    }

    #[Route('/admin/enable-badge/{id}', name: 'enable.badge', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function enableBadge(Badge $badge, EntityManagerInterface $entityManager): Response
    {
        $badge->setActif(true);
        $entityManager->flush();

        $this->addFlash('success', 'Badge activé avec succès.');
        return $this->redirectToRoute('show.badge');
    }

    #[Route('/admin/delete-badge/{id}', name: 'delete.badge', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteBadge(Badge $badge, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($badge);
        $entityManager->flush();

        $this->addFlash('success', 'Badge supprimé avec succès.');
        return $this->redirectToRoute('show.badge');
    }
}