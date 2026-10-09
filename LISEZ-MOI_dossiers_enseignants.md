# Mise à jour : dossier élève, enseignants par classe (CV), présence et paie des enseignants

## Installation (dans cet ordre)
1. phpMyAdmin > onglet SQL : `sql/enseignants_dossiers.sql` (crée 5 tables, sans danger si répété).
   `sql/role_econome.sql` doit avoir été exécuté : il autorise aussi le nouveau rôle « surveillant ».
2. Remplacez le dossier `college` (gardez `config/config.php`), puis Ctrl + F5.
3. Administrateur > Personnes > **Surveillants** : créez le compte du surveillant.
4. Administrateur > **Paie des enseignants** : fixez le prix de l'heure (pour tous, puis enseignant par enseignant si besoin).

## Dossiers élèves (Personnes > Dossiers élèves) — administration seulement (données de santé)
- Liste filtrable (classe, nouveau / ancien / dossier à compléter, recherche) avec diagramme nouveaux/anciens.
- Dossier d'un élève : identité, parents liés (téléphone, e-mail, profession), nouveau ou ancien, date d'inscription,
  santé (groupe sanguin, allergies, maladies, traitement, handicap, médecin), personne à prévenir en urgence, observations. Impression possible.

## Enseignants d'une classe (Classes > bouton « Enseignants »)
- Pour la classe choisie : enseignants affectés, matières, contact, diplôme et **CV** (PDF) à consulter.
- L'administration dépose ou remplace le CV de chaque enseignant sur cette page ; l'enseignant peut aussi déposer le sien
  (bouton « Mon CV » sur sa page d'accueil).

## Présence des enseignants (Suivi > Présence des enseignants)
- **Le surveillant** choisit le cours (classe, matière, enseignant), la date, l'heure de début et l'heure de fin ;
  la durée est calculée. Refus si la fin n'est pas après le début, si la séance dépasse 12 h ou chevauche une autre séance
  du même enseignant. Il ne peut supprimer que ses propres saisies. Il ne voit rien d'autre dans le site.
- **L'administration** consulte (filtre par mois et enseignant, heures par enseignant en diagramme) et peut supprimer une séance erronée.

## Paie des enseignants (Suivi > Paie des enseignants) — administrateur
- L'administrateur définit le **prix de l'heure** (pour tous, ou par enseignant).
- Pour chaque mois : heures données × prix de l'heure = montant. Détail par enseignant avec fiche de paie imprimable,
  enregistrement du paiement (date, mode, référence). Le montant est figé au paiement ; si les heures ou le prix changent ensuite,
  un avertissement s'affiche et le paiement peut être annulé puis refait.
- Chaque enseignant voit ses heures et sa paie (lecture seule) avec « Mes heures et ma paie ».

## Fichiers nouveaux
`sql/enseignants_dossiers.sql`, `includes/rh.php`, `includes/cv.php`, `cv.php`, `admin/dossiers.php`, `admin/dossier_eleve.php`,
`admin/classe_enseignants.php`, `admin/presence_enseignants.php`, `admin/surveillants.php`, `admin/paie_enseignants.php`,
`admin/paie_enseignant.php`, `enseignant/cv.php`, `enseignant/mes_heures.php`, `uploads/cv/`
## Fichiers modifiés
`includes/layout.php`, `includes/auth.php`, `admin/classes.php`, `enseignant/index.php`
