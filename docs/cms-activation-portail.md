# Activation du CMS du portail citoyen

## État vérifié

Le CMS `https://cms.communekafountine.com` répond avec l’API REST WordPress, mais les structures spécifiques du portail doivent être activées avant l’intégration des pages.

Contrôle local :

```bash
cd apps/portail
npm run check:cms
```

Le contrôle doit trouver :

- 11 types de contenus `ck_*` ;
- 5 taxonomies `ck_*`.

## Ordre d’installation WordPress

Dans le CMS, avec ACF Pro actif :

1. Importer `exports/acf-portail-institutionnel-types.json`.
2. Importer `exports/acf-portail-institutionnel-taxonomies-options.json`.
3. Importer `exports/acf-portail-institutionnel-options-field-groups.json`.
4. Vérifier les groupes de champs ACF et leurs clés.
5. Importer les 19 villages depuis `exports/wordpress-villages-kafountine.xml`.
6. Installer ou mettre à jour `exports/ck-portail-importateur.zip` si l’import des fiches est nécessaire.
7. Publier uniquement les contenus validés comme publics.

Les données nominatives, photos de conseillers et documents administratifs sources restent hors du dépôt public et ne doivent pas être importés automatiquement sans validation éditoriale.

## Vérification REST attendue

Après activation, ces routes doivent répondre avec des données ou une collection vide HTTP 200 :

```text
/wp-json/wp/v2/ck_elu
/wp-json/wp/v2/ck_agent
/wp-json/wp/v2/ck_service
/wp-json/wp/v2/ck_demarche
/wp-json/wp/v2/ck_commission
/wp-json/wp/v2/ck_session
/wp-json/wp/v2/ck_projet
/wp-json/wp/v2/ck_document
/wp-json/wp/v2/ck_marche
/wp-json/wp/v2/ck_alerte
/wp-json/wp/v2/ck_evenement
```

La connexion des pages Astro aux contenus réels commencera après ce contrôle.
