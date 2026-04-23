<?php
/**
 * Connexion PDO et requetes BDD utilisees par les endpoints materiels.
 */

function obtenir_connexion_bdd(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = 'mysql:host=127.0.0.1;dbname=orion;charset=utf8mb4';

    try {
        $pdo = new PDO($dsn, 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        journaliser('BDD', 'CONNEXION_FAIL', $e->getMessage());
        repondre(500, 'error', 'Erreur serveur interne');
    }

    return $pdo;
}

function chercher_utilisateur_autorise(string $sql, array $params): ?array
{
    $stmt = obtenir_connexion_bdd()->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetch() ?: null;
}

function verifier_acces_badge(string $rfid): ?array
{
    return chercher_utilisateur_autorise("
        SELECT u.id, u.nom, u.prenom, u.roles
        FROM badge b
        JOIN utilisateur u ON u.id = b.utilisateur_id
        WHERE b.rfid = :rfid
          AND b.actif = 1
          AND b.date_expiration > NOW()
          AND u.actif = 1
        LIMIT 1
    ", [':rfid' => $rfid]);
}

function verifier_acces_qrcode(string $token): ?array
{
    return chercher_utilisateur_autorise("
        SELECT u.id, u.nom, u.prenom, u.roles
        FROM qrcode q
        JOIN utilisateur u ON u.id = q.utilisateur_id
        WHERE q.code_qr = :token
          AND q.actif = 1
          AND q.date_expiration > NOW()
          AND u.actif = 1
        LIMIT 1
    ", [':token' => $token]);
}

function verifier_acces_pin(string $pin): ?array
{
    if (!ctype_digit($pin)) {
        return null;
    }

    return chercher_utilisateur_autorise("
        SELECT u.id, u.nom, u.prenom, u.roles
        FROM code c
        JOIN utilisateur u ON u.id = c.utilisateur_id
        WHERE c.code_pin = :pin
          AND c.actif = 1
          AND c.date_expiration > NOW()
          AND u.actif = 1
        LIMIT 1
    ", [':pin' => (int) $pin]);
}

function enregistrer_log_acces(string $type, int $idEntite, bool $autorise, string $ip): void
{
    $colonneFk = match ($type) {
        'badge' => 'badge_id',
        'qrcode' => 'qrcode_id',
        'pin' => 'code_id',
        default => null,
    };

    // La colonne FK est ajoutee uniquement quand une entite existe vraiment.
    $inclureFk = ($colonneFk !== null && $idEntite > 0);
    $fkSql = $inclureFk ? ", `{$colonneFk}`" : '';
    $fkVal = $inclureFk ? ', :fk_id' : '';

    $sql = "INSERT INTO log_access
                (type_identification, statut, date_heure, commentaire {$fkSql})
            VALUES (:type, :statut, NOW(), :commentaire {$fkVal})";

    $params = [
        ':type' => $type,
        ':statut' => (int) $autorise,
        ':commentaire' => "IP: {$ip} | " . ($autorise ? 'OK' : 'REFUSE'),
    ];

    if ($inclureFk) {
        $params[':fk_id'] = $idEntite;
    }

    obtenir_connexion_bdd()->prepare($sql)->execute($params);
}

function obtenir_id_entite(string $type, string|int $valeur): int
{
    [$table, $colonne] = match ($type) {
        'badge' => ['badge', 'rfid'],
        'qrcode' => ['qrcode', 'code_qr'],
        'pin' => ['code', 'code_pin'],
        default => throw new InvalidArgumentException("Type d'entite inconnu: {$type}"),
    };

    $stmt = obtenir_connexion_bdd()->prepare("SELECT id FROM {$table} WHERE {$colonne} = :v LIMIT 1");
    $stmt->execute([':v' => $valeur]);

    return (int) ($stmt->fetchColumn() ?: 0);
}

function obtenir_id_badge(string $rfid): int
{
    return obtenir_id_entite('badge', $rfid);
}

function obtenir_id_qrcode(string $token): int
{
    return obtenir_id_entite('qrcode', $token);
}

function obtenir_id_code(string $pin): int
{
    return obtenir_id_entite('pin', (int) $pin);
}
