# Contenus du portail à valider

Dernier audit du CMS : 10 octobre 2026.

Le portail Astro reste statique : toute publication WordPress nécessite un nouveau build puis un redéploiement de `apps/portail/dist/`.

## Collections présentes

| Collection | Publiés | État |
|---|---:|---|
| Villages / zones `ck_zone` | 19 | noms disponibles ; détails à enrichir |
| Élus `ck_elu` | 56 | raccordés au portail ; relecture institutionnelle recommandée |
| Commissions `ck_commission` | 18 | raccordées ; responsables à compléter ou confirmer |
| Agents `ck_agent` | 37 | raccordés ; fonctions et coordonnées à valider |
| Services `ck_service` | 21 | raccordés ; plusieurs descriptions génériques |
| Projets `ck_projet` | 0 | contenus validés à fournir |
| Documents `ck_document` | 0 | documents communicables à fournir |
| Démarches `ck_demarche` | 0 | fiches pratiques à fournir |
| Sessions `ck_session` | 0 | dates, ordres du jour et comptes rendus à fournir |
| Marchés `ck_marche` | 0 | avis et attributions à fournir |
| Événements `ck_evenement` | 0 | agenda communal à alimenter |
| Alertes `ck_alerte` | 0 | alertes publiables selon besoin |

## Validation obligatoire avant publication

- orthographe des noms et fonctions ;
- coordonnées, horaires et contacts officiels ;
- statut et date des documents ;
- montants, titulaires et dates des marchés ;
- photos et données personnelles ;
- rattachement à une zone `ck_zone` existante ;
- responsable éditorial et date de mise à jour.

## Ordre de saisie recommandé

1. coordonnées et horaires de la mairie ;
2. démarches administratives prioritaires ;
3. documents publics et délibérations ;
4. sessions du conseil ;
5. projets municipaux ;
6. marchés publics ;
7. événements et alertes ;
8. enrichissement des villages et services.

Aucun village autonome ne doit être créé : les villages restent les termes de la taxonomie `ck_zone`.

## Guide de saisie du premier lot

Le détail opérationnel de saisie et de validation est documenté dans [`portail-lot-1-saisie-validation.md`](./portail-lot-1-saisie-validation.md).
