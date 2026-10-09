# Portail citoyen de la commune de Kafountine

Frontend institutionnel séparé de Kafountine 360°.

## Stack

- Astro en rendu statique
- TypeScript
- WordPress headless sur `cms.communekafountine.com`
- Tests Playwright
- Déploiement prévu sur `dev.communekafountine.com`

## Développement

```bash
npm install
npm run dev
```

## Vérifications

```bash
npm run check
npm run build
npm test
```

Le dossier `apps/web` est conservé séparément comme ancien prototype. Ce frontend portail ne doit pas importer son code ni ses variables Kafountine 360°.
