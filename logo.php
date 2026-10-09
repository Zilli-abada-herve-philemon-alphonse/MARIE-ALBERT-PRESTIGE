<?php
// Envoie le logo du collège (public : il apparaît aussi sur la page de connexion).

require_once __DIR__ . '/includes/logo.php';

if (!logo_existe()) {
    http_response_code(404);
    exit;
}

header('Content-Type: image/png');
header('Content-Length: ' . filesize(LOGO_FICHIER));
header('Cache-Control: public, max-age=86400');
header('X-Content-Type-Options: nosniff');
readfile(LOGO_FICHIER);
