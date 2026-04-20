<?php

namespace App\Service;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QrCodeGeneratorService
{
    public function generateQrCode(string $token): string
    {
        // Créer l'objet QrCode avec le token
        $qrCode = new QrCode($token);

        // Générer l'image
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Récupère et retourne l'image en base 64
        return 'data:image/png;base64,' . base64_encode($result->getString());
    }
}
