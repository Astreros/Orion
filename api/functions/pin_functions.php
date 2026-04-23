<?php
/**
 * Logique de validation pour les codes PIN.
 */

define('PIN_LONGUEUR_MIN', 4);
define('PIN_LONGUEUR_MAX', 8);
define('PIN_RATE_MAX', 5);
define('PIN_RATE_FENETRE', 300);

function traiter_requete_pin(): void
{
    $endpoint = 'pin';

    appliquer_entetes_securite();
    verifier_methode('POST', $endpoint);
    verifier_rate_limit_pin();
    verifier_token($endpoint);

    $data = lire_body_json($endpoint);
    $pin = valider_pin($data, $endpoint);

    $utilisateur = verifier_acces_pin($pin);
    $autorise = $utilisateur !== null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'inconnue';

    enregistrer_log_acces('pin', obtenir_id_code($pin), $autorise, $ip);
    journaliser($endpoint, $autorise ? 'ACCES_OK' : 'ACCES_REFUSE', "ip={$ip}");

    if (!$autorise) {
        usleep(500_000); // Delai anti-brute-force volontaire.
        repondre(403, 'error', 'Code PIN incorrect');
    }

    repondre_acces_autorise($utilisateur);
}

function valider_pin(array $data, string $endpoint): string
{
    $pin = trim($data['pin'] ?? '');

    if ($pin === '') {
        journaliser($endpoint, 'VALIDATION_FAIL', 'champ pin absent');
        repondre(400, 'error', 'Champ pin manquant');
    }

    if (!ctype_digit($pin)) {
        journaliser($endpoint, 'VALIDATION_FAIL', 'pin non numerique');
        repondre(400, 'error', 'Le PIN doit contenir uniquement des chiffres');
    }

    $longueur = strlen($pin);
    if ($longueur < PIN_LONGUEUR_MIN || $longueur > PIN_LONGUEUR_MAX) {
        journaliser($endpoint, 'VALIDATION_FAIL', "longueur: {$longueur}");
        repondre(400, 'error', 'Le PIN doit contenir entre ' . PIN_LONGUEUR_MIN . ' et ' . PIN_LONGUEUR_MAX . ' chiffres');
    }

    return $pin;
}

function verifier_rate_limit_pin(): void
{
    verifier_rate_limit_personnalise(
        'pin',
        PIN_RATE_MAX,
        PIN_RATE_FENETRE,
        'pin_strict',
        'Trop de tentatives PIN. Reessayez dans quelques minutes.',
    );
}
