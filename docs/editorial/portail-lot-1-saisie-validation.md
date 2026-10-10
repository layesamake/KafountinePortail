# Lot éditorial 1 — saisie et validation CMS

## Objectif

Alimenter les premières collections encore vides du portail citoyen sans publier de donnée inventée :

1. coordonnées et horaires de la mairie ;
2. démarches administratives prioritaires ;
3. documents publics communicables ;
4. projets municipaux validés.

Le portail Astro étant statique, chaque publication ou correction WordPress devra être suivie de :

```text
npm run check
npm run check:cms
npm run build
npm test -- --project=chromium
npm audit --audit-level=moderate
```

Puis une nouvelle archive du contenu de `apps/portail/dist/` devra être générée pour le déploiement manuel Hostinger.

## Règle de publication

Une fiche ne passe en `Publié` qu’après validation communale de son contenu, de ses dates, de ses pièces jointes, de ses coordonnées et de ses rattachements taxonomiques. Les éléments non confirmés restent en brouillon ou en attente de validation.

## 1. Coordonnées et horaires

À renseigner dans la page WordPress **Coordonnées et horaires** :

- adresse officielle complète ;
- téléphone principal ;
- adresse e-mail institutionnelle ;
- horaires d’accueil par jour ;
- fermetures exceptionnelles connues ;
- modalités d’accueil ;
- contacts utiles, uniquement s’ils sont validés.

Contrôle avant publication :

- [ ] l’adresse a été confirmée par la mairie ;
- [ ] le téléphone est joignable ;
- [ ] l’e-mail appartient à la commune ;
- [ ] les horaires correspondent à la décision ou note officielle ;
- [ ] aucun contact personnel non autorisé n’est affiché.

## 2. Démarches administratives

Créer d’abord les familles nécessaires dans `ck_famille_demarche`, puis les fiches `ck_demarche`.

Fiche minimale recommandée :

- titre officiel de la démarche ;
- résumé court ;
- conditions d’accès ;
- pièces à fournir ;
- étapes ;
- coût ou mention « gratuit », uniquement si confirmé ;
- délai indicatif ou mention « à confirmer » ;
- lieu et service compétent ;
- contact officiel ;
- source ou référence administrative ;
- date de dernière vérification.

Priorité recommandée :

1. état civil ;
2. légalisation ou certification de documents ;
3. autorisations et certificats relevant de la commune ;
4. démarches liées aux marchés, occupations ou activités locales ;
5. autres démarches après validation du premier lot.

Contrôle avant publication :

- [ ] le titre correspond à l’intitulé utilisé par la mairie ;
- [ ] les pièces demandées sont confirmées ;
- [ ] les montants et délais sont confirmés ou explicitement absents ;
- [ ] le service compétent est rattaché à un service `ck_service` existant ;
- [ ] la fiche ne promet pas une procédure que la commune ne délivre pas ;
- [ ] la date de vérification est renseignée.

## 3. Documents publics

Créer d’abord les termes `ck_type_document` nécessaires. Chaque `ck_document` doit contenir :

- titre officiel ;
- date du document ;
- type de document ;
- résumé public ;
- fichier ou URL publique ;
- zone `ck_zone` si le document concerne une zone précise ;
- statut de publication ;
- référence officielle ;
- date de mise en ligne.

Documents prioritaires possibles, uniquement s’ils sont disponibles et communicables :

- délibérations ;
- arrêtés ;
- budgets ou comptes administratifs ;
- rapports publics ;
- règlements ;
- avis officiels.

Contrôle avant publication :

- [ ] le document est la version validée ;
- [ ] le fichier ne contient pas de donnée personnelle non nécessaire ;
- [ ] le lien ou fichier est accessible sans compte ;
- [ ] le titre et la date concordent avec le document ;
- [ ] le type et la zone sont corrects ;
- [ ] la taille et le format du fichier sont acceptables.

## 4. Projets municipaux

Chaque `ck_projet` doit préciser :

- nom officiel du projet ;
- objectif public ;
- état d’avancement ;
- zone concernée, avec un terme `ck_zone` existant ;
- calendrier connu ;
- financement ou montant, seulement si public et validé ;
- partenaire ou maître d’ouvrage, si publiable ;
- responsable éditorial ;
- date de mise à jour.

Contrôle avant publication :

- [ ] le projet a été confirmé par la commune ;
- [ ] son état n’est pas présenté comme acquis si la décision n’est pas définitive ;
- [ ] les montants sont sourcés ou omis ;
- [ ] le rattachement à une zone existante est exact ;
- [ ] les photos et logos sont autorisés.

## Procédure de validation

Pour chaque fiche :

1. préparer le contenu dans WordPress en brouillon ;
2. compléter les taxonomies et la référence source ;
3. faire relire par le référent communal ;
4. corriger les observations ;
5. publier uniquement après accord ;
6. noter la date et le responsable de validation ;
7. demander une nouvelle génération de l’archive statique.

## État initial constaté le 10 octobre 2026

| Collection | Publiés |
|---|---:|
| `ck_projet` | 0 |
| `ck_document` | 0 |
| `ck_demarche` | 0 |
| `ck_session` | 0 |
| `ck_marche` | 0 |
| `ck_evenement` | 0 |
| `ck_alerte` | 0 |

Ce document prépare la saisie ; il ne publie aucun contenu et ne remplace pas la validation officielle de la commune.
