<?php

namespace App\Controller;

use App\Repository\QRCodeRepository;
use App\Service\QrCodeGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'show.admin')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/admin/qrcode', name: 'show.qrcode')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminQrCode(QRCodeRepository $QRCodeRepository, QrCodeGeneratorService $qrCodeGeneratorService): Response
    {
        $qrCodes = $QRCodeRepository->findAll();

        // Générer les aperçus des QR codes
        foreach ($qrCodes as $qrCode) {
            $qrCode->qrCodeImage = $qrCodeGeneratorService->generateQrCode($qrCode->getCodeQR());
        }

        return $this->render('admin/qrcode.html.twig', [
            'qrCodes' => $qrCodes,
        ]);
    }

    #[Route('/admin/code', name: 'show.code')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminCode(): Response
    {
        return $this->render('admin/code.html.twig');
    }

    #[Route('/admin/badge', name: 'show.badge')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminBadge(): Response
    {
        return $this->render('admin/badge.html.twig');
    }

    #[Route('/admin/utilisateur', name: 'show.user')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminUser(): Response
    {
        return $this->render('admin/user.html.twig');
    }
}
