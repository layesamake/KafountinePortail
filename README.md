# Portail citoyen de la Commune de Kafountine

Frontend institutionnel officiel, séparé de Kafountine 360°.

## Structure

- `apps/portail/` : frontend Astro du portail communal
- `docs/` : cahier des charges et sources éditoriales
- `exports/` : structures ACF, données préparées et imports CMS

L’ancien prototype Next.js présent localement dans `apps/web/` n’est pas inclus dans le premier dépôt du portail. Il est conservé localement comme référence de transition.

## Développement du frontend

```bash
cd apps/portail
npm install
npm run dev
```

## Vérifications

```bash
cd apps/portail
npm run check
npm run build
npm test
```

## Environnements prévus

- Frontend : `https://dev.communekafountine.com`
- CMS : `https://cms.communekafountine.com`

Ce dépôt ne contient aucun fichier du projet Kafountine 360°.
