# Mise à jour : icônes et photos de profil

## Installation
1. Remplace ton dossier `college` par celui-ci (ou copie les fichiers modifiés listés plus bas).
2. Dans phpMyAdmin, ouvre la base `gestion_college`, onglet SQL, puis exécute une seule fois le contenu de `sql/migration_photos.sql`.
3. Vérifie que PHP peut écrire dans `uploads/photos/`. Sous XAMPP/WAMP/Laragon c'est automatique.
4. Vérifie que l'extension `gd` est activée dans php.ini (`extension=gd`). Elle l'est par défaut sous XAMPP.
5. Connecte-toi : le menu a des icônes, et la page « Mon profil » permet d'envoyer une photo.

## Ce qui a changé
- **Icônes** (Bootstrap Icons) : menu de l'administrateur, boutons, titres du profil.
- **Photo de profil** : l'administrateur, les enseignants et les parents envoient leur photo depuis « Mon profil ».
- **Photo des élèves** : ajoutée par l'administrateur dans la fiche élève (Élèves > Ajouter / Modifier).
  L'élève la voit sur son profil, mais ne peut pas la changer.
- **Affichage** : en haut à droite à côté du nom, dans la liste des élèves, dans l'espace parent
  (photo de chaque enfant), dans l'espace élève et sur le bulletin (format photo d'identité).
  Sans photo, un cercle avec les initiales s'affiche à la place.

## Sécurité
- Les photos ne sont pas accessibles directement : le dossier `uploads/` est fermé (`.htaccess`)
  et les images passent par `photo.php`, réservé aux personnes connectées.
- Taille maximale 2 Mo, type d'image vérifié, image recadrée en carré 400 x 400 et réencodée en JPEG
  (cela retire aussi les données cachées), nom de fichier aléatoire, protection CSRF.

## Fichiers nouveaux
`includes/photos.php`, `photo.php`, `assets/photo.js`, `sql/migration_photos.sql`, `uploads/`

## Fichiers modifiés
`includes/layout.php`, `includes/bulletin.php`, `profil.php`, `admin/eleve_form.php`, `admin/eleves.php`,
`parent/index.php`, `eleve/index.php`, `assets/style.css`
