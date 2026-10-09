# Mise à jour : logo du collège sur les bulletins

Aucune modification de la base de données : rien à exécuter dans phpMyAdmin.

## Installation
1. Remplacez votre dossier `college` par celui-ci (gardez votre `config/config.php` et vos dossiers `uploads/photos/` et `uploads/epreuves/`).
2. Vérifiez que l'extension `gd` est activée (par défaut sous XAMPP) et que PHP peut écrire dans `uploads/logo/`.

## Utilisation
- Administrateur > menu **Organisation > Logo du collège** : ajouter, remplacer ou supprimer le logo (PNG, JPG ou WebP, 2 Mo max).
- Le logo est réduit à 600 px au plus sans être déformé ; un PNG à fond transparent donne le meilleur rendu.
- Il s'affiche en haut de chaque bulletin (écran et impression), au-dessus du nom du collège,
  ainsi que sur la page de connexion et dans l'en-tête du site (en petit, devant le nom du collège).
  Sans logo, le bulletin reste comme avant.

## Fichiers nouveaux
`includes/logo.php`, `logo.php`, `admin/logo.php`, `uploads/logo/`

## Fichiers modifiés
`includes/bulletin.php`, `includes/layout.php`, `login.php`, `assets/style.css`
