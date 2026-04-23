<?php
/**
 * pin.php — Point d'entrée code PIN
 * Ce fichier ne contient aucune logique.
 */

require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/functions/db_functions.php';
require_once __DIR__ . '/functions/pin_functions.php';

traiter_requete_pin();
