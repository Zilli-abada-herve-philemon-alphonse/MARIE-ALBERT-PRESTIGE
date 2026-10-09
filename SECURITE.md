# Sécurité : ce qui est fait dans le code et ce qu'il reste à faire sur le serveur

## Déjà en place dans le code
- Requêtes SQL préparées partout, texte affiché toujours échappé, jeton CSRF sur tous les formulaires.
- Contrôle du rôle sur chaque page ; un enseignant ne voit que ses classes, un parent que ses enfants, un élève que son bulletin.
- Mots de passe stockés hachés ; photos, logo et épreuves hors d'accès direct, vérifiés avant envoi.

## Ajouté dans cette mise à jour
- **Limitation des essais de connexion en base** : 5 échecs sur un même identifiant depuis une même adresse, ou 30 échecs depuis une adresse,
  bloquent la connexion 15 minutes. Contournement par suppression des cookies impossible. (Exécuter `sql/migration_securite.sql`.)
- **Sessions** : déconnexion après 45 min d'inactivité ou 10 h au total ; un compte désactivé est coupé au bout d'une minute
  (avant, il gardait l'accès jusqu'à la fin de sa session) ; identifiants de session inventés refusés.
- **En-têtes** : interdiction d'afficher le site dans un cadre d'un autre site, `nosniff`, `Referrer-Policy`, `Permissions-Policy`,
  HSTS si le site est en HTTPS ; pages connectées non mises en cache (le bouton « retour » ne montre plus rien après déconnexion).
- Temps de réponse identique que l'identifiant existe ou non.
- `installer_admin.php` retiré du projet (il n'est utile qu'à la toute première installation).

## À faire sur le serveur (hors code)
1. **Supprimer** `installer_admin.php` du serveur s'il y est encore.
2. **HTTPS** obligatoire dès que le site est en ligne (certificat gratuit Let's Encrypt chez la plupart des hébergeurs).
3. **Utilisateur MySQL dédié** au lieu de `root` sans mot de passe (valeurs par défaut de XAMPP) :
   ```sql
   CREATE USER 'college_app'@'localhost' IDENTIFIED BY 'UN_MOT_DE_PASSE_LONG_ET_UNIQUE';
   GRANT SELECT, INSERT, UPDATE, DELETE ON gestion_college.* TO 'college_app'@'localhost';
   ```
   puis mettre ces identifiants dans `config/config.php`.
4. Dans `php.ini` : `display_errors = Off`, `log_errors = On`, `expose_php = Off`.
5. Vérifier que `https://votre-site/college/config/config.php` et `.../uploads/` répondent « Interdit » (403).
   Sinon, `AllowOverride All` n'est pas activé sur Apache (ou, sous Nginx, ajouter des règles `deny`).
6. **Sauvegardes** régulières de la base et du dossier `uploads/`, stockées ailleurs que sur le serveur.
7. Garder PHP et MySQL à jour. Mots de passe de l'administrateur : longs, uniques, jamais partagés.

## Idées pour la suite
- Journal des actions sensibles (connexions, modification de notes, suppressions).
- Obligation de changer le mot de passe à la première connexion.
- Héberger Bootstrap, Chart.js et les polices sur le serveur plutôt que sur des CDN.
