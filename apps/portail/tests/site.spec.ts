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

test('la page des services affiche les fiches du CMS', async ({ page }) => {
  await page.goto('/services/');

  await expect(page.getByRole('heading', { name: /Services municipaux/i })).toBeVisible();
  await expect(page.getByText('Cabinet du maire', { exact: true })).toBeVisible();
  await expect(page.getByText(/21 services publiés/i)).toBeVisible();
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
