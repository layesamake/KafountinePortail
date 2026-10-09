import { test, expect } from '@playwright/test';

test('accueil institutionnel expose les actions prioritaires', async ({ page }) => {
  await page.goto('/');

  await expect(page).toHaveTitle(/Commune de Kafountine/);
  await expect(page.getByRole('heading', { name: /Votre commune, vos services/i })).toBeVisible();
  await expect(page.getByRole('img', { name: /Commune de Kafountine — portail citoyen officiel/i })).toBeVisible();
  await expect(page.getByRole('link', { name: /Démarches administratives/i })).toBeVisible();
  await expect(page.getByRole('link', { name: 'Conseil municipal', exact: true })).toBeVisible();
});

test('la page mairie présente les informations institutionnelles', async ({ page }) => {
  await page.goto('/mairie/');

  await expect(page.getByRole('heading', { name: /La mairie de Kafountine/i })).toBeVisible();
  await expect(page.getByRole('heading', { name: /Coordonnées et horaires/i })).toBeVisible();
});
