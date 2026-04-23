<?php
/**
 * Logique de validation pour les badges RFID.
 */

function traiter_requete_badge(): void
{
    $endpoint = 'badge';

    appliquer_entetes_securite();
    verifier_methode('POST', $endpoint);
    verifier_rate_limit($endpoint);
    verifier_token($endpoint);

    $data = lire_body_json($endpoint);
    $rfid = valider_rfid($data, $endpoint);

    $utilisateur = verifier_acces_badge($rfid);
    $autorise = $utilisateur !== null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'inconnue';

    enregistrer_log_acces('badge', obtenir_id_badge($rfid), $autorise, $ip);
    journaliser($endpoint, $autorise ? 'ACCES_OK' : 'ACCES_REFUSE', "rfid={$rfid} ip={$ip}");

    if (!$autorise) {
        repondre(403, 'error', 'Acces refuse - badge inconnu ou expire');
    }

    repondre_acces_autorise($utilisateur);
}

function valider_rfid(array $data, string $endpoint): string
{
    $rfid = trim($data['rfid'] ?? '');

    if ($rfid === '') {
        journaliser($endpoint, 'VALIDATION_FAIL', 'champ rfid absent');
        repondre(400, 'error', 'Champ rfid manquant');
    }

    // Formats acceptes: ABCDEF12 ou AB:CD:EF:12.
    if (!preg_match('/^[A-Fa-f0-9]{2}(:[A-Fa-f0-9]{2}){3,9}$|^[A-Fa-f0-9]{8,20}$/', $rfid)) {
        journaliser($endpoint, 'VALIDATION_FAIL', "rfid invalide: {$rfid}");
        repondre(400, 'error', 'Format RFID invalide');
    }

    return strtoupper($rfid);
}
