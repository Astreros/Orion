<?php

namespace App\Controller;

use App\Entity\LogAccess;
use App\Repository\LogAccessRepository;
use App\Repository\QRCodeRepository;
use App\Repository\UtilisateurRepository;
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
    public function adminQrCode(UtilisateurRepository $utilisateurRepository, QRCodeRepository $QRCodeRepository, QrCodeGeneratorService $qrCodeGeneratorService): Response
    {
        $qrCodes = $QRCodeRepository->findAll();
        $users = $utilisateurRepository->findAll();

        // Générer les aperçus des QR codes
        foreach ($qrCodes as $qrCode) {
            $qrCode->qrCodeImage = $qrCodeGeneratorService->generateQrCode($qrCode->getCodeQR());
        }

        return $this->render('admin/qrcode.html.twig', [
            'qrCodes' => $qrCodes,
            'users' => $users,
        ]);
    }

    #[Route('/admin/code', name: 'show.code')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminCode(UtilisateurRepository $utilisateurRepository): Response
    {
        $users = $utilisateurRepository->findAll();

        return $this->render('admin/code.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/badge', name: 'show.badge')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminBadge(): Response
    {
        return $this->render('admin/badge.html.twig');
    }

    #[Route('/admin/utilisateur', name: 'show.user')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminUser(UtilisateurRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/user.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/logs', name: 'show.logs')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminLogs(LogAccessRepository $logsRepository): Response
    {
        $logs = $logsRepository->findAll();

        return $this->render('admin/logs.html.twig', [
            'logs' => $logs,
        ]);
    }
}
