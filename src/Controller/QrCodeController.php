<?php

namespace App\Controller;

use App\Entity\QRCode;
use App\Entity\Utilisateur;
use App\Service\TokenGeneratorService;
use DateMalformedStringException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class QrCodeController extends AbstractController
{
    // Création d'un QR code pour un utilisateur spécifique
    /**
     * @throws DateMalformedStringException
     */
    #[Route('/admin/add-qrcode/{userId}', name: 'add.qrcode', methods: ['GET', 'POST'])]
    public function addQRCode(int $userId, TokenGeneratorService $tokenGeneratorService, EntityManagerInterface $entityManager): Response
    {
        // Récupère l'utilisateur
        $user = $entityManager->getRepository(Utilisateur::class)->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Vérifie s'il existe déjà un QR code pour cet utilisateur et le supprime si oui
        $existeQRCode = $entityManager->getRepository(QRCode::class)
            ->findOneBy(['utilisateur' => $user]);

        if ($existeQRCode) {
            $entityManager->remove($existeQRCode);
            $entityManager->flush();
        }

        // Générer un token JWT valide 1 heure
        $data = ['user_id' => $user->getId()];
        $token = $tokenGeneratorService->generateToken($data, 3600);

        $date = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $dateExpiration = (clone $date)->add(new \DateInterval('PT3600S'));

        // Créer et sauvegarder le QR code
        $qrCode = new QRCode();
        $qrCode->setUtilisateur($user);
        $qrCode->setCodeQR($token);
        $qrCode->setActif(true);
        $qrCode->setDateCreation($date);
        $qrCode->setDateExpiration($dateExpiration);

        $entityManager->persist($qrCode);
        $entityManager->flush();

        // Redirige vers l'espace d'administration
        $this->addFlash('success', 'QR Code généré avec succès.');
        return $this->redirectToRoute('show.qrcode');
    }

    // Désactive un QR code
    #[Route('/admin/disable-qrcode/{id}', name: 'disable.qrcode', methods: ['GET', 'POST'])]
    public function disableQRCode(QRCode $qrCode, EntityManagerInterface $entityManager): Response
    {
        $qrCode->setActif(false);
        $entityManager->flush();

        $this->addFlash('success', 'QR Code désactivé avec succès.');
        return $this->redirectToRoute('show.qrcode');
    }

    // Active un QR code
    #[Route('/admin/enable-qrcode/{id}', name: 'enable.qrcode', methods: ['GET', 'POST'])]
    public function enableQRCode(QRCode $qrCode, EntityManagerInterface $entityManager): Response
    {
        $qrCode->setActif(true);
        $entityManager->flush();

        $this->addFlash('success', 'QR Code activé avec succès.');
        return $this->redirectToRoute('show.qrcode');
    }

    // Supprime un QR code
    #[Route('/admin/delete-qrcode/{id}', name: 'delete.qrcode', methods: ['GET', 'POST'])]
    public function deleteQRCode(QRCode $qrCode, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($qrCode);
        $entityManager->flush();

        $this->addFlash('success', 'QR Code supprimé avec succès.');
        return $this->redirectToRoute('show.qrcode');
    }
}