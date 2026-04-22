<?php

namespace App\Controller;

use App\Repository\QRCodeRepository;
use App\Service\QrCodeGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'show.admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/admin/qrcode', name: 'show.qrcode')]
    public function inde(QRCodeRepository $QRCodeRepository, QrCodeGeneratorService $qrCodeGeneratorService): Response
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
}
