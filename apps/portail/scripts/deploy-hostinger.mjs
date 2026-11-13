// Envoie le dossier dist/ vers le document root de communekafountine.com (Hostinger), sans rien supprimer d'autre (dossier dev/ préservé).
import { readdirSync, statSync, readFileSync } from 'node:fs';
import { join, relative, sep } from 'node:path';

const token = process.env.HOSTINGER_API_TOKEN;
const username = process.env.HOSTINGER_USERNAME || 'u966150428';
const domain = process.env.HOSTINGER_DOMAIN || 'communekafountine.com';
if (!token) throw new Error('HOSTINGER_API_TOKEN manquant');

const res = await fetch('https://developers.hostinger.com/api/hosting/v1/files/upload-urls', {
  method: 'POST',
  headers: { Authorization: `Bearer ${token}`, 'content-type': 'application/json', accept: 'application/json' },
  body: JSON.stringify({ username, domain }),
});
if (!res.ok) throw new Error(`upload-urls: HTTP ${res.status} ${await res.text()}`);
const { url, auth_key, rest_auth_key } = await res.json();
const base = url.replace(/\/$/, '');
const headers = { 'X-Auth': auth_key, 'X-Auth-Rest': rest_auth_key, 'Tus-Resumable': '1.0.0' };

function walk(dir) {
  return readdirSync(dir).flatMap((n) => {
    const p = join(dir, n);
    return statSync(p).isDirectory() ? walk(p) : [p];
  });
}
const root = process.argv[2] || 'dist';
const files = walk(root).map((p) => ({ p, rel: relative(root, p).split(sep).join('/') }));
// index.html en dernier : le site reste cohérent pendant l'envoi
files.sort((a, b) => (a.rel === 'index.html') - (b.rel === 'index.html'));
const hashFile = files.findIndex((f) => f.rel === '.cms-hash');
if (hashFile >= 0) files.push(files.splice(hashFile, 1)[0]);

for (const f of files) {
  const body = readFileSync(f.p);
  const target = `${base}/${f.rel.split('/').map(encodeURIComponent).join('/')}?override=true`;
  const a = await fetch(target, { method: 'POST', headers: { ...headers, 'Upload-Length': String(body.length), 'Upload-Offset': '0' } });
  if (a.status !== 201) throw new Error(`création ${f.rel}: ${a.status}`);
  const b = await fetch(target, { method: 'PATCH', headers: { ...headers, 'Content-Type': 'application/offset+octet-stream', 'Upload-Offset': '0' }, body });
  if (b.status !== 204) throw new Error(`envoi ${f.rel}: ${b.status}`);
}
console.log(`${files.length} fichiers déployés`);
