CK Admin communal — Lot 1

Extension WordPress isolée pour l’identité et l’ergonomie du back-office de la Commune de Kafountine.

Périmètre
- personnalisation de wp-login.php avec le logo officiel et un lien vers le portail ;
- identité visuelle de wp-admin (palette communale, nom, logo, pied de page) ;
- retrait des références WordPress dans l’interface des profils métier ;
- navigation technique retirée du menu des profils métier au niveau de l’API WordPress, sans masquer CSS-only ;
- structure d’accès « Accueil » avec les espaces communaux disponibles selon les capacités et les types de contenus présents ;
- navigation métier regroupée sous « Accueil » pour les profils éditoriaux, avec les services, le territoire, les élus, les commissions, les agents, les démarches, les documents, les projets et la médiathèque ;
- guide contextuel détaillé pour les démarches, documents et projets, avec sources, contacts, pièces et dates de vérification ;
- file dédiée « À relire » regroupant les démarches, documents et projets en statut `pending` ;
- liens séparés vers le site WordPress et le portail public depuis la barre d’administration ;
- aucune modification des contenus, champs ACF, API REST ou données CMS.

Installation
1. Dans WordPress, ouvrir Extensions > Ajouter > Téléverser une extension.
2. Téléverser `ck-admin-communal.zip`, installer puis activer.
3. Tester d’abord sur une préproduction ou une copie locale du CMS.

Désinstallation
La désactivation puis la suppression de l’extension ne supprime aucun contenu, terme, média, compte utilisateur ou attribution de rôle. Le marqueur technique de synchronisation est supprimé à la désinstallation. Le rôle `Gestionnaire communal` reste volontairement disponible afin de ne pas retirer brutalement les droits des comptes qui lui sont affectés ; il peut être supprimé manuellement après réaffectation des utilisateurs.

Rôle Gestionnaire communal
Le rôle peut préparer les contenus, modifier les contenus existants, téléverser des médias et gérer les termes éditoriaux. Il ne peut ni publier ni supprimer les contenus. La publication doit être effectuée par un Éditeur communal ou un administrateur technique.

Rôle Éditeur communal
Le rôle peut relire, corriger et publier les contenus communaux. Il ne peut pas supprimer les contenus publiés ni accéder aux réglages techniques, extensions, thèmes, utilisateurs ou mises à jour.

Règles d’accès
Les comptes disposant de `manage_options` ou `manage_network` conservent l’interface complète. Les autres profils ne voient pas les écrans techniques (extensions, thèmes, réglages, outils, utilisateurs, mises à jour et commentaires). Les contrôles natifs de capacités WordPress restent applicables aux URL directes.

Configuration
Le lien vers le portail est `https://communekafountine.com/`. Il peut être remplacé par un filtre WordPress :

    add_filter( 'ck_admin_communal_portal_url', function () {
        return 'https://dev.communekafountine.com/';
    } );

Build reproductible
Depuis la racine du dépôt :

    python exports/build-ck-admin-communal.py

Le script trie les fichiers, fixe les horodatages ZIP et produit `exports/ck-admin-communal.zip`. Aucun CDN, secret ou fichier de configuration local n’est inclus.

Limites du lot 1
La validation visuelle de wp-login.php et wp-admin nécessite une instance WordPress locale ou de préproduction. Cette extension ne crée aucun compte et ne se connecte pas au CMS de production.
