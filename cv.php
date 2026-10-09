<?php
// Envoie le CV d'un enseignant : administration, ou l'enseignant lui-même.

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cv.php';

$u      = exiger_connexion();
$ens_id = (int) ($_GET['id'] ?? 0);

$autorise = $u['role'] === 'admin' || ($u['role'] === 'enseignant' && enseignant_id_courant() === $ens_id);
if (!$autorise) {
    http_response_code(403);
    exit('Accès refusé.');
}

$cv = cv_enseignant($ens_id);
if (!$cv || !preg_match('/^[a-f0-9]{32}\.pdf$/', $cv['fichier']) || !is_file(CV_DOSSIER . $cv['fichier'])) {
    http_response_code(404);
    exit('CV introuvable.');
}

$s = db()->prepare('SELECT nom, prenom FROM enseignants WHERE id = :id');
$s->execute([':id' => $ens_id]);
$ens = $s->fetch();

session_write_close();
header('Content-Type: application/pdf');
header('Content-Length: ' . filesize(CV_DOSSIER . $cv['fichier']));
header('Content-Disposition: inline; filename="CV_' . nom_telechargement(($ens['prenom'] ?? '') . ' ' . ($ens['nom'] ?? '')) . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=0');
readfile(CV_DOSSIER . $cv['fichier']);
