# Mise à jour : économe, liste des élèves non soldés, blâmes par les enseignants

## Installation (dans cet ordre)
1. phpMyAdmin > onglet SQL : exécutez `sql/role_econome.sql` UNE SEULE FOIS (autorise le rôle « économe »).
   (Les étapes 1 et 2 — `coefficient_affectations.sql` et `mise_a_jour_complete.sql` — doivent déjà avoir été faites.)
2. Remplacez le dossier `college` (gardez `config/config.php`), puis Ctrl + F5.
3. Administrateur > Personnes > **Économes** : créez le compte de l'économe.

## Qui fait quoi
- **Économe** : se connecte, arrive sur la page Scolarité ; enregistre et annule les paiements, imprime les reçus,
  consulte et imprime la liste des élèves non soldés. Il ne voit rien d'autre dans le site.
- **Administration** : fixe le tarif des 3 tranches de chaque classe, consulte la scolarité et les paiements (lecture seule),
  consulte et imprime la liste des élèves non soldés, gère les comptes économes. Elle n'enregistre plus de paiements.
- **Enseignants** : bouton « Blâmes » sur leur page d'accueil ; ils choisissent un élève de leurs classes, la date et le motif,
  et ne peuvent supprimer que leurs propres blâmes. L'administration voit tous les blâmes avec leur auteur.
- **Parents** : voient les blâmes et les absences de leur enfant (inchangé).

## Élèves non soldés (menu « Élèves non soldés »)
- Filtres : classe ; état (tous les non soldés, en retard, n'ont rien payé, paiement partiel).
- Pour chaque élève : reste à payer par tranche, total payé, reste, état ; total à recouvrer en bas.
- **Imprimer la liste** : mise en page A4 paysage avec logo, nom du collège, filtre choisi et date d'édition.
