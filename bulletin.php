<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/bulletin.php';

$u    = exiger_connexion();
$role = $u['role'];
if (!in_array($role, ['admin', 'parent', 'eleve'], true)) {
    http_response_code(403);
    exit("Accès refusé.");
}

$periode_id = (int) ($_GET['periode'] ?? 0);
$eleve_id   = (int) ($_GET['eleve'] ?? 0);
$classe_id  = (int) ($_GET['classe'] ?? 0);

// Période
$stmt = db()->prepare(
    'SELECT p.id, p.libelle, p.date_debut, p.date_fin, p.annee_scolaire_id, a.libelle AS annee
     FROM periodes p
     JOIN annees_scolaires a ON a.id = p.annee_scolaire_id
     WHERE p.id = :id'
);
$stmt->execute([':id' => $periode_id]);
$periode = $stmt->fetch();
if (!$periode) {
    http_response_code(404);
    exit("Période introuvable.");
}

$mode_classe = false;

if ($classe_id > 0 && $role === 'admin') {
    // Tous les bulletins d'une classe (impression groupée)
    $mode_classe = true;
} elseif ($eleve_id > 0) {
    $stmt = db()->prepare('SELECT id, classe_id FROM eleves WHERE id = :id');
    $stmt->execute([':id' => $eleve_id]);
    $eleve_base = $stmt->fetch();
    if (!$eleve_base) {
        http_response_code(404);
        exit("Élève introuvable.");
    }

    // Contrôle d'accès : un parent ne voit que ses enfants, un élève que son propre bulletin.
    if ($role === 'parent') {
        $stmt = db()->prepare('SELECT 1 FROM parents_eleves WHERE parent_id = :p AND eleve_id = :e');
        $stmt->execute([':p' => $u['id'], ':e' => $eleve_id]);
        if (!$stmt->fetchColumn()) {
            http_response_code(403);
            exit("Accès refusé.");
        }
    } elseif ($role === 'eleve') {
        $stmt = db()->prepare('SELECT 1 FROM eleves WHERE id = :e AND utilisateur_id = :u');
        $stmt->execute([':e' => $eleve_id, ':u' => $u['id']]);
        if (!$stmt->fetchColumn()) {
            http_response_code(403);
            exit("Accès refusé.");
        }
    }

    $classe_id = (int) $eleve_base['classe_id'];
    if ($classe_id <= 0) {
        exit("Cet élève n'est dans aucune classe.");
    }
} else {
    http_response_code(404);
    exit("Bulletin introuvable.");
}

// Classe
$stmt = db()->prepare('SELECT id, nom, annee_scolaire_id FROM classes WHERE id = :id');
$stmt->execute([':id' => $classe_id]);
$classe = $stmt->fetch();
if (!$classe) {
    http_response_code(404);
    exit("Classe introuvable.");
}
if ((int) $classe['annee_scolaire_id'] !== (int) $periode['annee_scolaire_id']) {
    exit("Cette période ne correspond pas à l'année scolaire de la classe.");
}

$donnees = calculer_classe((int) $classe['id'], $periode);
$cibles  = $mode_classe ? $donnees['eleves'] : array_intersect_key($donnees['eleves'], [$eleve_id => true]);

// Lien de retour selon le rôle
if ($role === 'admin') {
    $retour = BASE_URL . '/admin/bulletins.php?' . http_build_query(['classe' => $classe['id'], 'periode' => $periode['id']]);
} else {
    $retour = BASE_URL . '/' . $role . '/';
}

entete('Bulletin');
?>
<style>
@media print {
    .navbar, .no-print { display: none !important; }
    body { background: #fff !important; }
    .bulletin { box-shadow: none !important; border: none !important; page-break-after: always; }
}
</style>

<div class="no-print d-flex gap-2 mb-3">
    <a class="btn btn-outline-secondary" href="<?= e($retour) ?>">Retour</a>
    <button type="button" class="btn btn-primary" onclick="window.print()">Imprimer / Enregistrer en PDF</button>
</div>

<?php foreach ($cibles as $eleve): ?>
    <?php afficher_bulletin($classe['nom'], $periode['annee'], $periode, $donnees, $eleve, !$mode_classe); ?>
<?php endforeach; ?>

<?php if (!$cibles): ?>
    <div class="alert alert-info">Aucun élève dans cette classe.</div>
<?php endif; ?>
<?php pied(); ?>
