<?php
/**
 * qrcode.php — Point d'entrée QR Code
 * Ce fichier ne contient aucune logique.
 */

require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/functions/db_functions.php';
require_once __DIR__ . '/functions/qrcode_functions.php';

traiter_requete_qrcode();
