const cmsUrl = (process.env.CMS_URL || 'https://cms.communekafountine.com').replace(/\/$/, '');
const expectedTypes = [
  'ck_alerte',
  'ck_agent',
  'ck_commission',
  'ck_demarche',
  'ck_document',
  'ck_elu',
  'ck_evenement',
  'ck_marche',
  'ck_projet',
  'ck_service',
  'ck_session',
];
const expectedTaxonomies = [
  'ck_categorie_evenement',
  'ck_famille_demarche',
  'ck_theme',
  'ck_type_document',
  'ck_zone',
];

async function getJson(path) {
  let lastError;
  for (let attempt = 1; attempt <= 3; attempt += 1) {
    try {
      const url = new URL(`${cmsUrl}${path}`);
      url.searchParams.set('_portal_check', `${Date.now()}-${attempt}`);
      const response = await fetch(url, {
        headers: { accept: 'application/json', 'cache-control': 'no-cache' },
        signal: AbortSignal.timeout(20_000),
      });
      if (!response.ok) {
        throw new Error(`${path} → HTTP ${response.status}`);
      }
      return response.json();
    } catch (error) {
      lastError = error;
      if (attempt < 3) await new Promise((resolve) => setTimeout(resolve, 500 * attempt));
    }
  }
  throw lastError;
}

const [types, taxonomies] = await Promise.all([
  getJson('/wp-json/wp/v2/types'),
  getJson('/wp-json/wp/v2/taxonomies'),
]);

const missingTypes = expectedTypes.filter((name) => !types[name]);
const missingTaxonomies = expectedTaxonomies.filter((name) => !taxonomies[name]);

console.log(`CMS: ${cmsUrl}`);
console.log(`Types portail présents : ${expectedTypes.length - missingTypes.length}/${expectedTypes.length}`);
console.log(`Taxonomies portail présentes : ${expectedTaxonomies.length - missingTaxonomies.length}/${expectedTaxonomies.length}`);

if (missingTypes.length > 0) {
  console.log(`Types manquants : ${missingTypes.join(', ')}`);
}
if (missingTaxonomies.length > 0) {
  console.log(`Taxonomies manquantes : ${missingTaxonomies.join(', ')}`);
}

if (missingTypes.length || missingTaxonomies.length) {
  console.error('Le CMS n’est pas encore prêt pour la connexion du portail.');
  process.exitCode = 1;
} else {
  console.log('Le CMS expose les structures nécessaires au portail.');
}
