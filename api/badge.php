<?php
/**
 * badge.php — Point d'entrée RFID
 * Ce fichier ne contient aucune logique.
 */

require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/functions/db_functions.php';
require_once __DIR__ . '/functions/badge_functions.php';

traiter_requete_badge();
