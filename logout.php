<?php
require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verifier();
    deconnecter_utilisateur();
}

header('Location: ' . BASE_URL . '/login.php');
exit;
