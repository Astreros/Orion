<?php

namespace App\Controller;

use App\Service\QrCodeGeneratorService;
use DateMalformedIntervalStringException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\TokenGeneratorService;
use App\Entity\QRCode;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'show.admin')]
    public function index(QrCodeGeneratorService $qrCodeGeneratorService ,TokenGeneratorService $tokenGeneratorService, EntityManagerInterface $entityManager): Response
    {
//        $date = new \DateTime();
//        $dateExpiration = $date->add(new \DateInterval('PT3600S'));
        $user = $this->getUser();
        $idUser = $user->getId();

        $QRCode = $qrCodeGeneratorService->generateQrCode('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NzY2ODM1NDQsImV4cCI6MTc3NjY4NzE0NCwiZGF0YSI6eyJpZF91c2VyIjoyfX0.oNO2xbkjv7NYJMB1157t3KkwjdACfdQzQUPzDFcZLW0');

//
//        $data = [
//            'id_user' => $user->getId(),
//        ];
//
//        $token = $tokenGeneratorService->generateToken($data, 3600);
//
//        $QRCode = new QRCode();
//        $QRCode->setUtilisateur($user);
//        $QRCode->setCodeQR($token);
//        $QRCode->setActif(true);
//        $QRCode->setDateCreation($date);
//        $QRCode->setDateExpiration($dateExpiration);
//
//        $entityManager->persist($QRCode);
//        $entityManager->flush();

        return $this->render('admin/index.html.twig', [
            'QRCode' => $QRCode,
        ]);
    }
}
