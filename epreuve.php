<?php
// Envoie une épreuve (PDF) après vérification des droits :
// administrateur : toutes ; enseignant : les siennes ; élève : celles de son niveau (toutes années).

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/epreuves.php';

$u = exiger_connexion();

$stmt = db()->prepare('SELECT id, enseignant_id, niveau, titre, fichier FROM epreuves WHERE id = :id');
$stmt->execute([':id' => (int) ($_GET['id'] ?? 0)]);
$ep = $stmt->fetch();

if (!$ep || !preg_match('/^[a-f0-9]{32}\.pdf$/', $ep['fichier']) || !is_file(EPREUVES_DOSSIER . $ep['fichier'])) {
    http_response_code(404);
    exit('Épreuve introuvable.');
}

$autorise = false;
if ($u['role'] === 'admin') {
    $autorise = true;
} elseif ($u['role'] === 'enseignant') {
    $autorise = (int) $ep['enseignant_id'] === enseignant_id_courant();
} elseif ($u['role'] === 'eleve') {
    $s = db()->prepare(
        'SELECT COUNT(*) FROM eleves e JOIN classes c ON c.id = e.classe_id
         WHERE e.utilisateur_id = :u AND c.niveau = :n'
    );
    $s->execute([':u' => $u['id'], ':n' => $ep['niveau']]);
    $autorise = (int) $s->fetchColumn() > 0;
}

if (!$autorise) {
    http_response_code(403);
    exit("Accès refusé : vous n'avez pas le droit de télécharger cette épreuve.");
}

session_write_close();

$chemin = EPREUVES_DOSSIER . $ep['fichier'];
header('Content-Type: application/pdf');
header('Content-Length: ' . filesize($chemin));
header('Content-Disposition: attachment; filename="' . nom_telechargement($ep['titre']) . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=0');
readfile($chemin);
