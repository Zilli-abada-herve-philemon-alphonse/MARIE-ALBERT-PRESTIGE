<?php
// Reçu de paiement imprimable : administrateur, parent de l'élève ou élève concerné.

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/scolarite.php';

$u = exiger_connexion();

$stmt = db()->prepare(
    'SELECT p.id, p.eleve_id, p.tranche, p.montant, p.date_paiement, p.mode, p.reference,
            e.nom, e.prenom, e.matricule, e.classe_id, e.utilisateur_id, c.nom AS classe
     FROM paiements p
     JOIN eleves e ON e.id = p.eleve_id
     LEFT JOIN classes c ON c.id = e.classe_id
     WHERE p.id = :id'
);
$stmt->execute([':id' => (int) ($_GET['id'] ?? 0)]);
$p = $stmt->fetch();
if (!$p) {
    http_response_code(404);
    exit('Reçu introuvable.');
}

$autorise = false;
if ($u['role'] === 'admin' || $u['role'] === 'econome') {
    $autorise = true;
} elseif ($u['role'] === 'parent') {
    $s = db()->prepare('SELECT COUNT(*) FROM parents_eleves WHERE parent_id = :u AND eleve_id = :e');
    $s->execute([':u' => $u['id'], ':e' => $p['eleve_id']]);
    $autorise = (int) $s->fetchColumn() > 0;
} elseif ($u['role'] === 'eleve') {
    $autorise = (int) $p['utilisateur_id'] === (int) $u['id'];
}
if (!$autorise) {
    http_response_code(403);
    exit("Accès refusé : ce reçu ne concerne pas votre compte.");
}

$fiche = $p['classe_id'] ? (situation_eleves((int) $p['classe_id'], (int) $p['eleve_id'])['eleves'][(int) $p['eleve_id']] ?? null) : null;

entete('Reçu ' . numero_recu((int) $p['id']));
?>
<div class="no-print mb-3 d-flex gap-2">
    <button class="btn btn-primary" onclick="window.print()"><?= icone('printer') ?>Imprimer</button>
</div>
<div class="card shadow-sm mx-auto" style="max-width: 640px;">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <?php if (($logo_url = url_logo()) !== null): ?>
                <img class="bulletin-logo" src="<?= e($logo_url) ?>" alt="Logo de <?= e(NOM_ETABLISSEMENT) ?>">
            <?php endif; ?>
            <div class="fw-bold fs-5"><?= e(NOM_ETABLISSEMENT) ?></div>
            <div class="fs-5 mt-2">REÇU DE PAIEMENT — <?= e(numero_recu((int) $p['id'])) ?></div>
        </div>
        <table class="table table-sm table-borderless mb-4">
            <tr><th style="width:40%">Élève</th><td><?= e($p['prenom'] . ' ' . $p['nom']) ?> (matricule <?= e($p['matricule']) ?>)</td></tr>
            <tr><th>Classe</th><td><?= e($p['classe'] ?? '—') ?></td></tr>
            <tr><th>Objet</th><td>Scolarité — tranche <?= (int) $p['tranche'] ?> sur 3</td></tr>
            <tr><th>Date du paiement</th><td><?= e(date_fr($p['date_paiement'])) ?></td></tr>
            <tr><th>Mode de paiement</th><td><?= e($p['mode']) ?><?= $p['reference'] ? ' — réf. ' . e($p['reference']) : '' ?></td></tr>
            <tr><th>Montant reçu</th><td class="fs-5 fw-bold"><?= e(format_fcfa((int) $p['montant'])) ?></td></tr>
            <?php if ($fiche): ?>
                <tr><th>Reste à payer (total)</th><td><?= e(format_fcfa($fiche['reste'])) ?></td></tr>
            <?php endif; ?>
        </table>
        <div class="d-flex justify-content-between mt-5 pt-3 small text-muted">
            <span>Émis le <?= e(date_fr(date('Y-m-d'))) ?></span>
            <span>Le caissier (signature et cachet)</span>
        </div>
    </div>
</div>
<?php pied(); ?>
