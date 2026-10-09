# Inventaire des données importables — Portail institutionnel

Vérification effectuée dans `docs/`. Cet inventaire couvre uniquement le portail institutionnel, conformément au périmètre retenu.

## Données directement exploitables

| Donnée | Source | Volume constaté | Destination ACF | État |
|---|---|---:|---|---|
| Conseillers municipaux | Liste des 56 membres | 56 personnes | `ck_elu` | Importable après normalisation des noms et villages |
| Bureau municipal | Organigramme du bureau | 1 maire + 4 adjoints | `ck_elu` | Importable ; fonctions et ordre disponibles |
| Coordonnées publiques des élus | Registre du conseil | 26 numéros renseignés environ | `ck_elu.telephone` | Importable sous réserve de validation et consentement de publication |
| Commissions techniques | Répertoire des commissions | 18 commissions | `ck_commission` | Importable ; 3 rapporteurs restent à renseigner |
| Composition des commissions | Répertoire des commissions | Présidents, vice-présidents, rapporteurs et membres | `ck_commission.composition` | Importable avec relations vers `ck_elu` |
| Personnel administratif | Fiche nominative de l’organigramme | 25 lignes/unités recensées | `ck_agent` et `ck_service` | Partiellement importable ; plusieurs intitulés et rattachements doivent être confirmés |
| Photos des conseillers | Dossier `Photo Conseillers` | 26 fichiers JPEG | Médias + `ck_elu` | Importable ; correspondance à contrôler avec le registre |
| Logo de la commune | `Logo-Kafountine.avif` | 1 fichier | Page d’option Identité | Importable après conversion/validation du format AVIF |
| Villages de la commune | Liste publique déjà vérifiée | 19 termes | Taxonomie `ck_zone` | Fichier XML prêt à importer |

## Données disponibles comme référentiels

Le document « Répertoire des documents municipaux » contient 17 grandes catégories et un catalogue détaillé de documents administratifs : actes du maire, conseil et commissions, courrier, ressources humaines, finances, recettes, patrimoine, marchés publics, foncier, travaux, état civil, juridique, projets, affaires sociales, environnement, communication et police/domaine public.

Ce document décrit les types, responsables et usages des documents. Il ne contient pas les fichiers PDF ou les actes eux-mêmes. Il peut donc alimenter la taxonomie `ck_type_document` et servir de référentiel éditorial pour `ck_document`, mais aucune fiche de document public ne doit être créée comme publiée sans le fichier officiel et ses métadonnées.

## Données qui ne sont pas encore importables

- Les dates de mandat, délégations, biographies et contacts institutionnels détaillés des élus ne sont pas complets.
- Les rapporteurs des commissions 9, 17 et 18 sont explicitement à renseigner.
- Le nom du maire est absent de la fiche nominative du personnel, même s’il figure dans le document du bureau.
- Plusieurs rattachements du personnel sont incertains ou comportent plusieurs personnes dans une même cellule.
- Les fichiers officiels du répertoire municipal (délibérations, budgets, arrêtés, rapports, marchés, etc.) ne figurent pas dans le dossier `docs`.
- Les sessions du conseil, les dates, ordres du jour et procès-verbaux réels ne sont pas fournis.
- Les services et démarches disposent d’une structure prévue dans le cahier des charges, mais pas encore de fiches validées à importer.

## Points de contrôle avant import

1. Ne pas convertir automatiquement les villages apparaissant dans la liste des conseillers en termes `ck_zone` : certains noms sont extérieurs à la liste officielle des 19 villages (par exemple Ziguinchor, Dakar, Bignona ou Thionck-Essyl).
2. Faire valider l’orthographe des personnes et des villages : les documents présentent des variantes telles que `Dienaba/Diénéba`, `Kailo/Kaïlo`, `Kouba/Couba` et `Clarice/Clarisse`.
3. Publier les photos et numéros de téléphone uniquement après validation de leur caractère public.
4. Importer les structures ACF avant les données : types de contenus, taxonomies, pages d’options, puis fiches et médias.

## Fichiers de configuration déjà préparés

- `acf-portail-institutionnel-types.json` — 11 types de contenus.
- `acf-portail-institutionnel-taxonomies-options.json` — 5 taxonomies et 3 pages d’options.
- `wordpress-villages-kafountine.xml` — 19 termes de la taxonomie `ck_zone`.
