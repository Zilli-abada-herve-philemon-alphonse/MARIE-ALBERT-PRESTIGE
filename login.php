<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/connexion.php';

// Déjà connecté : on renvoie vers son espace.
if (utilisateur_courant() !== null) {
    header('Location: ' . url_accueil(utilisateur_courant()['role']));
    exit;
}

const MAX_ECHECS = 5;
const BLOCAGE_SECONDES = 300;

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verifier();

    $echecs  = $_SESSION['echecs'] ?? 0;
    $dernier = $_SESSION['dernier_echec'] ?? 0;

    $identifiant_saisi = trim($_POST['identifiant'] ?? '');

    if (connexion_bloquee($identifiant_saisi)
        || ($echecs >= MAX_ECHECS && (time() - $dernier) < BLOCAGE_SECONDES)) {
        $erreur = 'Trop de tentatives. Réessayez dans quelques minutes.';
    } else {
        if ($echecs >= MAX_ECHECS) {
            $echecs = 0;
        }

        $identifiant  = trim($_POST['identifiant'] ?? '');
        $mot_de_passe = $_POST['mot_de_passe'] ?? '';

        $stmt = db()->prepare(
            'SELECT id, nom, prenom, mot_de_passe, role
             FROM utilisateurs
             WHERE identifiant = :identifiant AND actif = 1'
        );
        $stmt->execute([':identifiant' => $identifiant]);
        $u = $stmt->fetch();

        if (!$u) {
            // Même durée de calcul qu'avec un vrai compte : on ne révèle pas si l'identifiant existe.
            password_verify($mot_de_passe, '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ01234');
        }

        if ($u && password_verify($mot_de_passe, $u['mot_de_passe'])) {
            // Met à jour le hash si l'algorithme par défaut a évolué.
            if (password_needs_rehash($u['mot_de_passe'], PASSWORD_DEFAULT)) {
                $maj = db()->prepare('UPDATE utilisateurs SET mot_de_passe = :h WHERE id = :id');
                $maj->execute([':h' => password_hash($mot_de_passe, PASSWORD_DEFAULT), ':id' => $u['id']]);
            }
            unset($_SESSION['echecs'], $_SESSION['dernier_echec']);
            effacer_echecs($identifiant);
            connecter_utilisateur($u);
            header('Location: ' . url_accueil($u['role']));
            exit;
        }

        enregistrer_echec($identifiant);
        $_SESSION['echecs'] = $echecs + 1;
        $_SESSION['dernier_echec'] = time();
        $erreur = 'Identifiant ou mot de passe incorrect.';
    }
}

entete('Connexion');
?>
<div class="row justify-content-center mt-5">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <?php if (($logo_url = url_logo()) !== null): ?>
                    <img class="login-logo" src="<?= e($logo_url) ?>" alt="Logo de <?= e(NOM_ETABLISSEMENT) ?>">
                <?php endif; ?>
                <div class="marque-login"><?= e(NOM_ETABLISSEMENT) ?></div>
                <h1 class="h4 mb-4 text-center">Connexion</h1>

                <?php if ($erreur !== ''): ?>
                    <div class="alert alert-danger"><?= e($erreur) ?></div>
                <?php endif; ?>

                <form method="post" autocomplete="off">
                    <?= csrf_champ() ?>
                    <div class="mb-3">
                        <label for="identifiant" class="form-label">Identifiant</label>
                        <input type="text" class="form-control" id="identifiant" name="identifiant" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="mot_de_passe" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php pied(); ?>
