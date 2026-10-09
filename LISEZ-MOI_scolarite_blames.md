# Mise à jour : scolarité en 3 tranches + absences et blâmes sur la page du parent

## Installation (dans cet ordre)
1. phpMyAdmin, base `gestion_college`, onglet SQL : exécutez UNE SEULE FOIS `sql/migration_scolarite_blames.sql`
   (crée les tables `scolarite_classes`, `paiements` et `blames`).
2. Remplacez le dossier `college` (gardez `config/config.php`). Ctrl + F5 dans le navigateur.

## Scolarité (administrateur > Suivi > Scolarité)
- **Vue d'ensemble** : total attendu, encaissé, reste à recouvrer, élèves soldés, taux de recouvrement par classe (diagramme).
- **Tarif d'une classe** : montant (FCFA) et date limite des 3 tranches ; case pour appliquer le même tarif à tout le niveau.
- **Liste des élèves de la classe** : payé / dû pour chaque tranche, reste, statut (Soldé, Partiel, En retard, Impayé).
- **Encaisser** : choix de la tranche, montant (jamais plus que le reste de la tranche), date, mode (Espèces, Mobile Money,
  Virement, Chèque) et référence. Plusieurs versements possibles pour une même tranche. Un paiement peut être annulé.
- **Reçu imprimable** pour chaque paiement (logo, numéro R-000001, reste à payer).
- Un élève est « En retard » si une tranche dont la date limite est passée n'est pas entièrement payée.

## Blâmes (administrateur > Suivi > Blâmes)
- Enregistrer un blâme (élève, date, motif), filtrer par classe, supprimer.

## Page du parent
Pour chaque enfant : carte **Scolarité** (3 tranches, barre de progression, statuts, boutons des reçus) et
carte **Absences et blâmes** (année en cours : nombre d'absences, dont non justifiées, nombre de blâmes, détail des 15 derniers).
Un parent ne voit que ses propres enfants ; un reçu n'est ouvert qu'à l'administrateur, au parent de l'élève ou à l'élève.

## Fichiers nouveaux
`sql/migration_scolarite_blames.sql`, `includes/scolarite.php`, `includes/vie_scolaire.php`, `admin/scolarite.php`,
`admin/paiement.php`, `admin/blames.php`, `recu.php`
## Fichiers modifiés
`includes/layout.php` (menu), `parent/index.php`
