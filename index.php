<?php
require_once __DIR__ . '/includes/auth.php';

$u = utilisateur_courant();
if ($u === null) {
    header('Location: ' . BASE_URL . '/login.php');
} else {
    header('Location: ' . url_accueil($u['role']));
}
exit;
