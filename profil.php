<?php
// Page « Mon profil » : chaque utilisateur consulte et met à jour ses propres informations.

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

$session = exiger_connexion();
$role    = $session['role'];

$stmt = db()->prepare('SELECT id, nom, prenom, identifiant, email, mot_de_passe, photo FROM utilisateurs WHERE id = :id');
$stmt->execute([':id' => $session['id']]);
$compte = $stmt->fetch();
if (!$compte) {
    deconnecter_utilisateur();
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$statuts = ['titulaire' => 'Titulaire', 'contractuel' => 'Contractuel', 'vacataire' => 'Vacataire'];

// Informations propres au type de compte
$extra = [];
if ($role === 'parent') {
    $s = db()->prepare('SELECT telephone, adresse, profession FROM parents WHERE utilisateur_id = :u');
    $s->execute([':u' => $compte['id']]);
    $extra = $s->fetch() ?: ['telephone' => '', 'adresse' => '', 'profession' => ''];
} elseif ($role === 'enseignant') {
    $s = db()->prepare('SELECT telephone, diplome, date_embauche, statut FROM enseignants WHERE utilisateur_id = :u');
    $s->execute([':u' => $compte['id']]);
    $extra = $s->fetch() ?: ['telephone' => '', 'diplome' => '', 'date_embauche' => '', 'statut' => ''];
} elseif ($role === 'eleve') {
    $s = db()->prepare(
        'SELECT e.photo, e.matricule, e.date_naissance, e.lieu_naissance, e.adresse, e.ancien_etablissement, c.nom AS classe
         FROM eleves e
         LEFT JOIN classes c ON c.id = e.classe_id
         WHERE e.utilisateur_id = :u'
    );
    $s->execute([':u' => $compte['id']]);
    $extra = $s->fetch() ?: [];
}

$erreur_profil = '';
$erreur_mdp    = '';
$erreur_photo = '';

// Photo actuelle : celle de la fiche élève pour un élève, celle du compte pour les autres.
$photo_actuelle = $role === 'eleve' ? ($extra['photo'] ?? null) : ($compte['photo'] ?? null);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verifier();
    $action = $_POST['action'] ?? '';

    if ($action === 'profil') {
        $email = trim($_POST['email'] ?? '');
        $nom   = trim($_POST['nom'] ?? $compte['nom']);
        $prenom = trim($_POST['prenom'] ?? $compte['prenom']);
        $tel   = mb_substr(trim($_POST['telephone'] ?? ''), 0, 30);
        $adr   = mb_substr(trim($_POST['adresse'] ?? ''), 0, 255);
        $prof  = mb_substr(trim($_POST['profession'] ?? ''), 0, 100);

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreur_profil = "L'adresse email est invalide.";
        } elseif ($role === 'admin' && ($nom === '' || $prenom === '')) {
            $erreur_profil = 'Le nom et le prénom sont obligatoires.';
        } else {
            $email_bd = $email === '' ? null : $email;
            $pdo = db();
            $pdo->beginTransaction();
            try {
                if ($role === 'admin') {
                    $pdo->prepare('UPDATE utilisateurs SET nom = :n, prenom = :p, email = :e WHERE id = :id')
                        ->execute([':n' => $nom, ':p' => $prenom, ':e' => $email_bd, ':id' => $compte['id']]);
                    $_SESSION['utilisateur']['nom']    = $nom;
                    $_SESSION['utilisateur']['prenom'] = $prenom;
                } else {
                    $pdo->prepare('UPDATE utilisateurs SET email = :e WHERE id = :id')
                        ->execute([':e' => $email_bd, ':id' => $compte['id']]);
                }

                if ($role === 'enseignant') {
                    $pdo->prepare('UPDATE enseignants SET telephone = :t, email = :e WHERE utilisateur_id = :u')
                        ->execute([':t' => $tel === '' ? null : $tel, ':e' => $email_bd, ':u' => $compte['id']]);
                } elseif ($role === 'parent') {
                    $pdo->prepare(
                        'INSERT INTO parents (utilisateur_id, telephone, adresse, profession)
                         VALUES (:u, :t, :a, :p)
                         ON DUPLICATE KEY UPDATE telephone = :t2, adresse = :a2, profession = :p2'
                    )->execute([
                        ':u'  => $compte['id'],
                        ':t'  => $tel === '' ? null : $tel,
                        ':a'  => $adr === '' ? null : $adr,
                        ':p'  => $prof === '' ? null : $prof,
                        ':t2' => $tel === '' ? null : $tel,
                        ':a2' => $adr === '' ? null : $adr,
                        ':p2' => $prof === '' ? null : $prof,
                    ]);
                }
                $pdo->commit();
            } catch (PDOException $ex) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $ex;
            }
            flash('success', 'Votre profil a été mis à jour.');
            rediriger('/profil.php');
        }
    } elseif ($action === 'photo' && $role !== 'eleve') {
        try {
            $nouvelle = enregistrer_photo($_FILES['photo'] ?? []);
            db()->prepare('UPDATE utilisateurs SET photo = :p WHERE id = :id')
                ->execute([':p' => $nouvelle, ':id' => $compte['id']]);
            supprimer_photo($photo_actuelle);
            flash('success', 'Votre photo a été mise à jour.');
            rediriger('/profil.php');
        } catch (RuntimeException $ex) {
            $erreur_photo = $ex->getMessage();
        }
    } elseif ($action === 'photo_supprimer' && $role !== 'eleve') {
        db()->prepare('UPDATE utilisateurs SET photo = NULL WHERE id = :id')->execute([':id' => $compte['id']]);
        supprimer_photo($photo_actuelle);
        flash('success', 'Votre photo a été retirée.');
        rediriger('/profil.php');
    } elseif ($action === 'mot_de_passe') {
        $actuel   = $_POST['actuel'] ?? '';
        $nouveau  = $_POST['nouveau'] ?? '';
        $confirme = $_POST['confirmation'] ?? '';

        if (!password_verify($actuel, $compte['mot_de_passe'])) {
            $erreur_mdp = 'Le mot de passe actuel est incorrect.';
        } elseif (strlen($nouveau) < 8) {
            $erreur_mdp = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
        } elseif ($nouveau !== $confirme) {
            $erreur_mdp = 'La confirmation ne correspond pas au nouveau mot de passe.';
        } else {
            $stmt = db()->prepare('UPDATE utilisateurs SET mot_de_passe = :m WHERE id = :id');
            $stmt->execute([':m' => password_hash($nouveau, PASSWORD_DEFAULT), ':id' => $compte['id']]);
            session_regenerate_id(true);
            flash('success', 'Votre mot de passe a été modifié.');
            rediriger('/profil.php');
        }
    }

    // En cas d'erreur, on réaffiche ce que la personne a saisi
    if ($erreur_profil !== '') {
        $compte['email'] = $_POST['email'] ?? '';
        $compte['nom']   = $_POST['nom'] ?? $compte['nom'];
        $compte['prenom'] = $_POST['prenom'] ?? $compte['prenom'];
        if ($role === 'parent' || $role === 'enseignant') {
            $extra['telephone'] = $_POST['telephone'] ?? '';
        }
        if ($role === 'parent') {
            $extra['adresse']    = $_POST['adresse'] ?? '';
            $extra['profession'] = $_POST['profession'] ?? '';
        }
    }
}

entete('Mon profil');
?>
<div class="d-flex align-items-center gap-3 mb-4">
    <?= avatar($photo_actuelle, $compte['prenom'], $compte['nom'], 'lg') ?>
    <div>
        <h1 class="h3 mb-1">Mon profil</h1>
        <p class="text-muted mb-0"><?= icone_role($role) ?> <?= e(libelle_role($role)) ?> — identifiant de connexion : <strong><?= e($compte['identifiant']) ?></strong></p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body p-4">
                <h2 class="h5 mb-3"><?= icone('person-vcard') ?> Mes informations</h2>

                <?php if ($erreur_profil !== ''): ?>
                    <div class="alert alert-danger"><?= e($erreur_profil) ?></div>
                <?php endif; ?>

                <form method="post" autocomplete="off">
                    <?= csrf_champ() ?>
                    <input type="hidden" name="action" value="profil">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="prenom">Prénom</label>
                            <input class="form-control" id="prenom" name="prenom" value="<?= e($compte['prenom']) ?>"
                                   <?= $role === 'admin' ? 'required' : 'readonly' ?>>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="nom">Nom</label>
                            <input class="form-control" id="nom" name="nom" value="<?= e($compte['nom']) ?>"
                                   <?= $role === 'admin' ? 'required' : 'readonly' ?>>
                        </div>
                    </div>
                    <?php if ($role !== 'admin'): ?>
                        <p class="text-muted small mt-n2">Le nom et le prénom sont modifiables par l'administration.</p>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= e($compte['email'] ?? '') ?>">
                    </div>

                    <?php if ($role === 'parent' || $role === 'enseignant'): ?>
                        <div class="mb-3">
                            <label class="form-label" for="telephone">Téléphone</label>
                            <input class="form-control" id="telephone" name="telephone" maxlength="30"
                                   value="<?= e($extra['telephone'] ?? '') ?>">
                        </div>
                    <?php endif; ?>

                    <?php if ($role === 'parent'): ?>
                        <div class="mb-3">
                            <label class="form-label" for="profession">Profession</label>
                            <input class="form-control" id="profession" name="profession" maxlength="100"
                                   value="<?= e($extra['profession'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="adresse">Adresse / quartier</label>
                            <input class="form-control" id="adresse" name="adresse" maxlength="255"
                                   value="<?= e($extra['adresse'] ?? '') ?>">
                        </div>
                    <?php endif; ?>

                    <button class="btn btn-primary">Enregistrer</button>
                </form>
            </div>
        </div>

        <?php if ($role === 'enseignant'): ?>
            <div class="card mt-4">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3"><?= icone('briefcase') ?> Informations professionnelles</h2>
                    <p class="text-muted small">Ces informations sont gérées par l'administration.</p>
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Diplôme / spécialité</dt>
                        <dd class="col-sm-7"><?= e($extra['diplome'] ?? '') ?: '—' ?></dd>
                        <dt class="col-sm-5">Date d'embauche</dt>
                        <dd class="col-sm-7"><?= e(date_fr($extra['date_embauche'] ?? null)) ?: '—' ?></dd>
                        <dt class="col-sm-5">Statut</dt>
                        <dd class="col-sm-7"><?= e($statuts[$extra['statut'] ?? ''] ?? '—') ?></dd>
                    </dl>
                </div>
            </div>
        <?php elseif ($role === 'eleve' && $extra): ?>
            <div class="card mt-4">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3"><?= icone('card-list') ?> Ma fiche d'élève</h2>
                    <p class="text-muted small">Ces informations sont gérées par l'administration.</p>
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Matricule</dt>
                        <dd class="col-sm-7"><?= e($extra['matricule']) ?></dd>
                        <dt class="col-sm-5">Classe</dt>
                        <dd class="col-sm-7"><?= e($extra['classe'] ?? '') ?: '—' ?></dd>
                        <dt class="col-sm-5">Date de naissance</dt>
                        <dd class="col-sm-7"><?= e(date_fr($extra['date_naissance'])) ?></dd>
                        <dt class="col-sm-5">Lieu de naissance</dt>
                        <dd class="col-sm-7"><?= e($extra['lieu_naissance'] ?? '') ?: '—' ?></dd>
                        <dt class="col-sm-5">Adresse / quartier</dt>
                        <dd class="col-sm-7"><?= e($extra['adresse'] ?? '') ?: '—' ?></dd>
                        <dt class="col-sm-5">Ancien établissement</dt>
                        <dd class="col-sm-7"><?= e($extra['ancien_etablissement'] ?? '') ?: '—' ?></dd>
                    </dl>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-body p-4">
                <h2 class="h5 mb-3"><?= icone('camera') ?> Ma photo</h2>

                <?php if ($erreur_photo !== ''): ?>
                    <div class="alert alert-danger"><?= e($erreur_photo) ?></div>
                <?php endif; ?>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <span id="apercu-photo" data-taille="xl"><?= avatar($photo_actuelle, $compte['prenom'], $compte['nom'], 'xl') ?></span>
                    <?php if ($role === 'eleve'): ?>
                        <p class="text-muted small mb-0">Votre photo est gérée par l'administration.</p>
                    <?php else: ?>
                        <p class="text-muted small mb-0">JPG, PNG ou WebP, 2 Mo maximum. La photo est recadrée en carré.</p>
                    <?php endif; ?>
                </div>

                <?php if ($role !== 'eleve'): ?>
                    <form method="post" enctype="multipart/form-data">
                        <?= csrf_champ() ?>
                        <input type="hidden" name="action" value="photo">
                        <div class="mb-3">
                            <label class="form-label" for="photo">Choisir une photo</label>
                            <input type="file" class="form-control" id="photo" name="photo" required
                                   accept="image/jpeg,image/png,image/webp" data-apercu="apercu-photo">
                        </div>
                        <button class="btn btn-primary"><?= icone('upload') ?>Enregistrer la photo</button>
                    </form>
                    <?php if ($photo_actuelle): ?>
                        <form method="post" class="mt-2" onsubmit="return confirm('Retirer votre photo ?');">
                            <?= csrf_champ() ?>
                            <input type="hidden" name="action" value="photo_supprimer">
                            <button class="btn btn-outline-danger btn-sm"><?= icone('trash') ?>Retirer ma photo</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body p-4">
                <h2 class="h5 mb-3"><?= icone('key') ?> Changer mon mot de passe</h2>

                <?php if ($erreur_mdp !== ''): ?>
                    <div class="alert alert-danger"><?= e($erreur_mdp) ?></div>
                <?php endif; ?>

                <form method="post" autocomplete="off">
                    <?= csrf_champ() ?>
                    <input type="hidden" name="action" value="mot_de_passe">
                    <div class="mb-3">
                        <label class="form-label" for="actuel">Mot de passe actuel</label>
                        <input type="password" class="form-control" id="actuel" name="actuel" required autocomplete="current-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="nouveau">Nouveau mot de passe (8 caractères minimum)</label>
                        <input type="password" class="form-control" id="nouveau" name="nouveau" required autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="confirmation">Confirmer le nouveau mot de passe</label>
                        <input type="password" class="form-control" id="confirmation" name="confirmation" required autocomplete="new-password">
                    </div>
                    <button class="btn btn-primary">Changer le mot de passe</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php pied(); ?>
