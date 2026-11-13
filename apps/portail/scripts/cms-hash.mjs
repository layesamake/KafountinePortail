// Calcule une empreinte du contenu du CMS (toutes les routes utilisées par le build).
import { createHash } from 'node:crypto';

const cms = (process.env.CMS_URL || 'https://cms.communekafountine.com').replace(/\/$/, '');
const paths = [
  '/wp-json/ck/v1/portal-settings',
  ...['ck_service', 'ck_agent', 'ck_elu', 'ck_commission', 'ck_session', 'ck_projet', 'ck_marche',
    'ck_evenement', 'ck_alerte', 'actualites', 'ck_demarche', 'ck_document',
    'ck_famille_demarche', 'ck_type_document', 'ck_zone', 'ck_theme', 'ck_categorie_evenement']
    .map((t) => `/wp-json/wp/v2/${t}?per_page=100&context=view`),
];
const hash = createHash('sha256');
for (const p of paths) {
  try {
    const r = await fetch(cms + p, { headers: { accept: 'application/json' }, signal: AbortSignal.timeout(30000) });
    hash.update(`${p}|${r.status}|${await r.text()}`);
  } catch (e) {
    hash.update(`${p}|ERR`);
  }
}
console.log(hash.digest('hex'));
