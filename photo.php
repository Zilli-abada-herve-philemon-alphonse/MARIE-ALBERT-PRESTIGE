<?php
// Envoie une photo de profil, uniquement aux personnes connectées.

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/photos.php';

exiger_connexion();
session_write_close(); // libère la session : plusieurs photos peuvent se charger en parallèle

$f = $_GET['f'] ?? '';
if (!is_string($f) || !preg_match('/^[a-f0-9]{32}\.jpg$/', $f) || !is_file(PHOTOS_DOSSIER . $f)) {
    http_response_code(404);
    exit;
}

header('Content-Type: image/jpeg');
header('Content-Length: ' . filesize(PHOTOS_DOSSIER . $f));
header('Cache-Control: private, max-age=86400');
header('X-Content-Type-Options: nosniff');
readfile(PHOTOS_DOSSIER . $f);
