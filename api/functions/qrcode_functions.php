<?php
/**
 * Logique de validation pour les QR Codes.
 */

define('QR_LONGUEUR_MIN', 6);
define('QR_LONGUEUR_MAX', 255);

function traiter_requete_qrcode(): void
{
    $endpoint = 'qrcode';

    appliquer_entetes_securite();
    verifier_methode('POST', $endpoint);
    verifier_rate_limit($endpoint);
    verifier_token($endpoint);

    $data = lire_body_json($endpoint);
    $token = valider_token_qr($data, $endpoint);

    $utilisateur = verifier_acces_qrcode($token);
    $autorise = $utilisateur !== null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'inconnue';

    enregistrer_log_acces('qrcode', obtenir_id_qrcode($token), $autorise, $ip);
    journaliser($endpoint, $autorise ? 'ACCES_OK' : 'ACCES_REFUSE', "ip={$ip}");

    if (!$autorise) {
        repondre(403, 'error', 'QR Code invalide ou expire');
    }

    repondre_acces_autorise($utilisateur);
}

function valider_token_qr(array $data, string $endpoint): string
{
    $token = trim($data['token'] ?? '');

    if ($token === '') {
        journaliser($endpoint, 'VALIDATION_FAIL', 'champ token absent');
        repondre(400, 'error', 'Champ token manquant');
    }

    $longueur = strlen($token);
    if ($longueur < QR_LONGUEUR_MIN || $longueur > QR_LONGUEUR_MAX) {
        journaliser($endpoint, 'VALIDATION_FAIL', "longueur token: {$longueur}");
        repondre(400, 'error', 'Format token QR invalide');
    }

    if (!preg_match('/^[A-Za-z0-9\-_]+$/', $token)) {
        journaliser($endpoint, 'VALIDATION_FAIL', 'caracteres non autorises');
        repondre(400, 'error', 'Format token QR invalide');
    }

    return $token;
}
