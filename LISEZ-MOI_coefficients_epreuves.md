# Mise à jour : coefficients par classe + épreuves PDF

## Installation (dans cet ordre)
1. Dans phpMyAdmin, base `gestion_college`, onglet SQL : exécutez UNE SEULE FOIS `sql/migration_coefficients_epreuves.sql`.
   - Les affectations existantes reprennent le coefficient actuel de leur matière : les bulletins ne changent pas.
2. Remplacez votre dossier `college` par celui-ci (gardez votre `config/config.php` si vous l'avez modifié, et le dossier `uploads/photos/`).
3. Vérifiez que PHP peut écrire dans `uploads/epreuves/` (automatique sous XAMPP/WAMP/Laragon).
4. Taille des PDF : 10 Mo maximum, ou moins si `upload_max_filesize` / `post_max_size` de php.ini sont plus bas
   (la limite réelle est affichée dans le formulaire d'envoi).

## 1. Coefficients par classe
- Le coefficient est maintenant dans **Affectations** : chaque matière a son coefficient dans chaque classe.
- Page Affectations : colonne « Coefficient » modifiable (bouton OK), et champ coefficient à l'ajout
  (prérempli avec le coefficient par défaut de la matière).
- Page Matières : le coefficient devient un simple « coefficient par défaut » proposé lors des affectations.
  Le modifier ne change pas les coefficients déjà fixés dans les classes.
- Les moyennes générales et les bulletins utilisent le coefficient de la classe de l'élève.
- Un coefficient 0 est accepté : la matière apparaît mais ne compte pas dans la moyenne générale.

## 2. Épreuves
- **Enseignant** : bouton « Mes épreuves » sur sa page d'accueil. Il choisit une de ses classes/matières
  de l'année en cours, un titre et un PDF. Il peut télécharger ou supprimer ses épreuves.
- **Élève** : bouton « Épreuves à télécharger » sur sa page d'accueil. Il voit les épreuves de son niveau
  (ex. 6ème), de toutes les années, avec filtres par année et par matière.
- **Administrateur** : peut télécharger n'importe quelle épreuve (via son lien), sans page dédiée pour l'instant.
- Sécurité : seuls les vrais PDF sont acceptés (contenu vérifié), noms de fichiers aléatoires, dossier
  `uploads/` fermé au public, téléchargement uniquement par `epreuve.php` après contrôle des droits, protection CSRF.

## Fichiers nouveaux
`sql/migration_coefficients_epreuves.sql`, `includes/epreuves.php`, `epreuve.php`,
`enseignant/epreuves.php`, `eleve/epreuves.php`, `uploads/epreuves/`

## Fichiers modifiés
`admin/affectations.php`, `admin/matieres.php`, `admin/index.php`, `includes/bulletin.php`,
`enseignant/index.php`, `eleve/index.php`
