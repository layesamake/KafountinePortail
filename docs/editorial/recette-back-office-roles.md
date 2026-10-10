# Recette du back-office communal

## Objet

Ce document décrit la recette manuelle à effectuer après installation de `CK Admin communal 2.1.0` sur `cms.communekafountine.com`.

La recette doit être faite avec trois comptes distincts :

- un administrateur technique ;
- un Gestionnaire communal (`ck_gestionnaire`) ;
- un Éditeur communal (`ck_editeur`).

Aucun compte ne doit être partagé entre ces profils.

## Préparation

1. Vérifier que l’extension `CK Admin communal` est active et affiche la version `2.1.0`.
2. Vérifier que les rôles sont attribués manuellement dans **Utilisateurs > Tous les utilisateurs**.
3. Préparer, sans publier, un contenu de test dans chacun des types suivants :
   - Démarche (`ck_demarche`) ;
   - Document (`ck_document`) ;
   - Projet (`ck_projet`).
4. Utiliser un titre clairement identifié comme contenu de recette, puis supprimer ces contenus après validation avec un administrateur technique.

## Scénario Gestionnaire communal

| Contrôle | Résultat attendu |
|---|---|
| Connexion | Redirection vers l’accueil communal |
| Tableau de bord | Compteurs et accès rapides visibles |
| Navigation | Menus métier visibles, menus techniques absents |
| Nouvelle démarche/document/projet | Formulaire accessible |
| Enregistrement | Le contenu peut être enregistré en brouillon |
| Soumission | Le contenu peut être envoyé pour relecture avec le statut `pending` |
| Publication directe | Impossible : le rôle ne possède pas `publish_posts` |
| Suppression | Impossible pour les contenus communaux |
| Médias | Téléversement autorisé |
| Portail public | Lien visible et ouvert dans un nouvel onglet |
| Réglages/extensions/thèmes | Inaccessibles |

## Scénario Éditeur communal

| Contrôle | Résultat attendu |
|---|---|
| Connexion | Redirection vers l’accueil communal |
| File « À relire » | Accessible depuis la navigation métier |
| Contenu `pending` | Visible dans la file de relecture |
| Ouverture d’une fiche | Bouton « Ouvrir la fiche » fonctionnel |
| Correction | Modification du contenu autorisée |
| Publication | Publication autorisée après relecture |
| Suppression | Suppression des contenus publiée interdite |
| Médias | Téléversement autorisé |
| Réglages/extensions/thèmes | Inaccessibles |

## Scénario administrateur technique

| Contrôle | Résultat attendu |
|---|---|
| Connexion | Accueil WordPress natif conservé |
| Interface | Outils techniques complets conservés |
| Rôles et utilisateurs | Administration complète |
| Extensions, thèmes, réglages | Accessibles |
| Contenus | Création, modification, publication et suppression selon les droits administrateur |

## Vérification des contenus existants

La vérification publique du CMS au 10 octobre 2026 donne les totaux suivants via l’API REST :

| Type | Total public |
|---|---:|
| Élus | 56 |
| Agents | 37 |
| Services | 21 |
| Commissions | 18 |
| Démarches | 0 |
| Sessions | 0 |
| Projets | 0 |
| Documents | 0 |
| Marchés | 0 |
| Alertes | 0 |
| Événements | 0 |

Ces totaux confirment que le prochain travail métier est la saisie et la validation des contenus éditoriaux manquants. Aucun contenu fictif ne doit être créé pour remplir les compteurs.

## Critères de validation

La recette est validée uniquement si :

- les trois profils ont des parcours distincts ;
- le Gestionnaire ne peut pas publier ;
- l’Éditeur peut traiter la file `pending` et publier ;
- les menus techniques sont absents des profils métier ;
- les URL directes restent protégées par les capacités WordPress ;
- le portail public s’ouvre correctement ;
- aucun contenu de recette ne reste publié.

## Limite de la validation automatisée

La validation locale couvre le build de l’archive et les contrats statiques du plugin. Une session WordPress authentifiée est nécessaire pour valider réellement les capacités, les redirections et les boutons dans `wp-admin`.
