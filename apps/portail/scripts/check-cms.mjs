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
  const response = await fetch(`${cmsUrl}${path}`, {
    headers: { accept: 'application/json' },
    signal: AbortSignal.timeout(15_000),
  });
  if (!response.ok) {
    throw new Error(`${path} → HTTP ${response.status}`);
  }
  return response.json();
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
