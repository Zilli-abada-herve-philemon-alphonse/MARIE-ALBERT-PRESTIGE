# Mise à jour : même style sur toutes les pages

Aucune modification de la base de données. Remplacez le dossier `college` (gardez `config/config.php`), puis Ctrl + F5.

## Sur toutes les pages
- Chaque carte (formulaire ou tableau) d'une page a sa couleur : bord supérieur, titre, boutons et lignes survolées.
- Les tableaux gardent leurs en-têtes colorés et leurs lignes qui apparaissent une à une ; les cartes apparaissent en douceur.
- Titres de page avec un trait bleu et jaune, barres de filtres présentées dans un cadre.
- Les animations s'arrêtent si l'appareil demande de réduire les animations.

## Tuiles de chiffres et diagrammes ajoutés
- **Classes** : tuiles (classes, élèves inscrits, effectif moyen) + diagramme de l'effectif de chaque classe.
- **Élèves** : tuiles (inscrits, garçons, filles, sans classe).
- **Espace enseignant** : tuiles (classes, matières, élèves).
- **Espace élève** et **espace parent** : diagramme des moyennes de l'élève comparées à la classe (dernière période avec des notes).
- Déjà présents : tableau de bord, page Bulletins et bulletin d'un élève.

## Fichiers modifiés
`assets/style.css`, `assets/theme.js`, `includes/layout.php`, `includes/bulletin.php`, `admin/classes.php`,
`admin/eleves.php`, `enseignant/index.php`, `eleve/index.php`, `parent/index.php`
