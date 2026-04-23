<?php
/**
 * api/supervision.php — Données réelles pour la supervision temps réel
 * Appelé toutes les 5s par la page supervision via fetch()
 */

require_once __DIR__ . '/config/security.php';

appliquer_cors();
header('Content-Type: application/json; charset=utf-8');

// Pas de token Bearer requis (lecture seule, appelé en interne)
// Mais on vérifie que la session web est active via le cookie
// Pour simplifier : on accepte les requêtes localhost
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($ip, ['::1', '127.0.0.1'], true)) {
    verifier_token('supervision');
}

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=orion;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // ─── Métriques du jour ────────────────────────────────────────────────────
    $aujourd_hui = date('Y-m-d');

    // Total accès autorisés aujourd'hui
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM log_access WHERE statut = 1 AND DATE(date_heure) = ?");
    $stmt->execute([$aujourd_hui]);
    $acces_ok = (int) $stmt->fetchColumn();

    // Total accès refusés aujourd'hui
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM log_access WHERE statut = 0 AND DATE(date_heure) = ?");
    $stmt->execute([$aujourd_hui]);
    $acces_refuse = (int) $stmt->fetchColumn();

    // Total accès de la dernière heure
    $stmt = $pdo->query("SELECT COUNT(*) FROM log_access WHERE date_heure >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $acces_heure = (int) $stmt->fetchColumn();

    // Badges actifs en base
    $stmt = $pdo->query("SELECT COUNT(*) FROM badge WHERE actif = 1 AND date_expiration > NOW()");
    $badges_actifs = (int) $stmt->fetchColumn();

    // QR codes actifs
    $stmt = $pdo->query("SELECT COUNT(*) FROM qrcode WHERE actif = 1 AND date_expiration > NOW()");
    $qr_actifs = (int) $stmt->fetchColumn();

    // Codes PIN actifs
    $stmt = $pdo->query("SELECT COUNT(*) FROM code WHERE actif = 1 AND date_expiration > NOW()");
    $pin_actifs = (int) $stmt->fetchColumn();

    // ─── Derniers événements réels ────────────────────────────────────────────
    $stmt = $pdo->query("
        SELECT
            DATE_FORMAT(la.date_heure, '%H:%i:%s') AS heure,
            la.type_identification                 AS type,
            la.statut,
            COALESCE(u.prenom, '')                 AS prenom,
            COALESCE(u.nom,    '')                 AS nom,
            CASE
                WHEN la.badge_id  IS NOT NULL THEN COALESCE(b.rfid, 'RFID')
                WHEN la.qrcode_id IS NOT NULL THEN COALESCE(q.code_qr, 'QR')
                WHEN la.code_id   IS NOT NULL THEN 'PIN'
                ELSE UPPER(la.type_identification)
            END AS identifiant
        FROM log_access la
        LEFT JOIN badge       b ON b.id = la.badge_id
        LEFT JOIN qrcode      q ON q.id = la.qrcode_id
        LEFT JOIN code        c ON c.id = la.code_id
        LEFT JOIN utilisateur u ON u.id = COALESCE(b.utilisateur_id, q.utilisateur_id, c.utilisateur_id)
        ORDER BY la.date_heure DESC
        LIMIT 8
    ");
    $evenements_bruts = $stmt->fetchAll();

    // Formater les événements pour la supervision
    $evenements = array_map(function ($ev) {
        $type_id  = strtolower($ev['type']);
        $autorise = (int) $ev['statut'] === 1;
        $personne = trim($ev['prenom'] . ' ' . $ev['nom']) ?: 'Inconnu';

        $methode = match(true) {
            str_contains($type_id, 'badge')  => '🔖 Badge RFID',
            str_contains($type_id, 'qrcode'), str_contains($type_id, 'qr') => '📷 QR Code',
            str_contains($type_id, 'pin'), str_contains($type_id, 'code')  => '🔢 PIN',
            default => strtoupper($type_id),
        };

        $msg = $autorise
            ? "$methode — {$personne} → Accès autorisé"
            : "$methode — {$personne} → Accès refusé";

        return [
            'heure'   => $ev['heure'],
            'type'    => $autorise ? 'success' : 'danger',
            'msg'     => $msg,
            'statut'  => $autorise,
            'personne'=> $personne,
        ];
    }, $evenements_bruts);

    // ─── Réponse JSON ─────────────────────────────────────────────────────────
    echo json_encode([
        'ok'            => true,
        'heure'         => date('H:i:s'),
        'metriques'     => [
            'acces_ok'      => $acces_ok,
            'acces_refuse'  => $acces_refuse,
            'acces_heure'   => $acces_heure,
            'total_acces'   => $acces_ok + $acces_refuse,
            'badges_actifs' => $badges_actifs,
            'qr_actifs'     => $qr_actifs,
            'pin_actifs'    => $pin_actifs,
            'identifiants'  => $badges_actifs + $qr_actifs + $pin_actifs,
        ],
        'evenements'    => $evenements,
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'erreur' => 'Erreur BDD']);
}
