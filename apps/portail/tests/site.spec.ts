import { test, expect } from '@playwright/test';

test('accueil institutionnel expose les actions prioritaires', async ({ page }) => {
  await page.goto('/');

  await expect(page).toHaveTitle(/Commune de Kafountine/);
  await expect(page.getByRole('heading', { name: /Votre commune, vos services/i })).toBeVisible();
  await expect(page.getByRole('img', { name: /Commune de Kafountine — portail citoyen officiel/i })).toBeVisible();
  await expect(page.locator('.welcome-card dd').filter({ hasText: '19' })).toBeVisible();
  await expect(page.getByRole('link', { name: /Démarches administratives/i })).toBeVisible();
  await expect(page.getByRole('link', { name: 'Conseil municipal', exact: true })).toBeVisible();
});

test('le menu mobile s’ouvre et expose la navigation principale', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/');

  const menu = page.locator('.menu-button');
  await expect(menu).toBeVisible();
  await expect(page.getByRole('navigation', { name: 'Navigation principale' })).not.toBeVisible();

  await menu.click();

  await expect(menu).toHaveAttribute('aria-expanded', 'true');
  await expect(page.getByRole('navigation', { name: 'Navigation principale' })).toBeVisible();
  await expect(page.getByRole('link', { name: 'La mairie', exact: true })).toBeVisible();

  await page.getByRole('button', { name: 'Fermer le menu' }).click();
  await expect(menu).toHaveAttribute('aria-expanded', 'false');
});

test('la page mairie présente les informations institutionnelles', async ({ page }) => {
  await page.goto('/mairie/');

  await expect(page.getByRole('heading', { name: /La mairie de Kafountine/i })).toBeVisible();
  await expect(page.getByRole('heading', { name: /Coordonnées et horaires/i })).toBeVisible();
});

test('la page du conseil affiche les élus et commissions du CMS', async ({ page }) => {
  await page.goto('/conseil/');

  await expect(page.getByRole('heading', { name: /Le conseil municipal/i })).toBeVisible();
  await expect(page.getByText(/56 élus publiés/i)).toBeVisible();
  await expect(page.getByText(/18 commissions publiées/i)).toBeVisible();
  await expect(page.getByText('Aminata Soumare DIATTA', { exact: true })).toBeVisible();
  await expect(page.getByText('Sécurité', { exact: true })).toBeVisible();
});

test('le conseil affiche aussi les sessions publiées du CMS', async ({ page }) => {
  await page.goto('/conseil/');

  await expect(page.getByRole('heading', { name: 'Sessions du conseil municipal', exact: true })).toBeVisible();
  await expect(page.getByText(/0 sessions publiées/i)).toBeVisible();
});

test('la page des services affiche les fiches du CMS', async ({ page }) => {
  await page.goto('/services/');

  await expect(page.getByRole('heading', { name: /Services municipaux/i })).toBeVisible();
  await expect(page.getByText('Cabinet du maire', { exact: true })).toBeVisible();
  await expect(page.getByText(/21 services publiés/i)).toBeVisible();
});

test('la page des services expose aussi le catalogue des démarches', async ({ page }) => {
  await page.goto('/services/');

  await expect(page.getByRole('heading', { name: 'Démarches administratives', exact: true })).toBeVisible();
  await expect(page.getByText(/0 démarches publiées/i)).toBeVisible();
  await expect(page.getByLabel(/Filtrer par famille de démarche/i)).toBeVisible();
});

test('la page participation expose l’agenda et les alertes du CMS', async ({ page }) => {
  await page.goto('/participer/');

  await expect(page.getByRole('heading', { name: 'Agenda communal', exact: true })).toBeVisible();
  await expect(page.getByText(/0 événements publiés/i)).toBeVisible();
  await expect(page.getByText(/0 alertes actives/i)).toBeVisible();
  await expect(page.getByLabel(/Filtrer les événements par village ou zone/i)).toBeVisible();
});

test('la page des villages liste les zones du CMS', async ({ page }) => {
  await page.goto('/villages/');

  await expect(page.getByRole('heading', { name: /Les villages de Kafountine/i })).toBeVisible();
  await expect(page.getByText('Abéné', { exact: true })).toBeVisible();
  await expect(page.getByText('Niomoune', { exact: true })).toBeVisible();
});

test('la page des projets est reliée au CMS et prépare le filtre par zone', async ({ page }) => {
  await page.goto('/projets/');

  await expect(page.getByRole('heading', { name: /Les projets de la commune/i })).toBeVisible();
  await expect(page.getByText(/0 projets publiés/i)).toBeVisible();
  await expect(page.getByLabel(/Filtrer par village ou zone/i)).toBeVisible();
});

test('la page des documents publics est reliée au CMS', async ({ page }) => {
  await page.goto('/transparence/documents/');

  await expect(page.getByRole('heading', { name: /Documents publics/i })).toBeVisible();
  await expect(page.getByText(/0 documents publiés/i)).toBeVisible();
  await expect(page.getByLabel(/Filtrer par type de document/i)).toBeVisible();
  await expect(page.getByLabel(/Filtrer par village ou zone/i)).toBeVisible();
});

test('la recherche globale filtre les contenus publiés', async ({ page }) => {
  await page.goto('/recherche/');

  await expect(page.getByRole('heading', { name: /Trouvez rapidement la bonne information/i })).toBeVisible();
  const input = page.getByRole('searchbox', { name: /Que recherchez-vous/i });
  await input.fill('Abéné');
  await page.getByRole('button', { name: 'Rechercher' }).click();
  await expect(page.getByText(/résultat/i)).toBeVisible();
  await expect(page.getByRole('link', { name: /Village ou zone Abéné/i })).toBeVisible();
});
