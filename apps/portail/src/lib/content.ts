export interface PortalQuickAction {
  label: string;
  description: string;
  href: string;
  icon: string;
}

export interface PortalService {
  id: number;
  slug: string;
  title: { rendered: string };
  content: { rendered: string; protected: boolean };
  excerpt: { rendered: string; protected: boolean };
  link: string;
  parent: number;
  menu_order: number;
}

export interface PortalPerson {
  id: number;
  slug: string;
  title: { rendered: string };
  excerpt: { rendered: string; protected: boolean };
  content: { rendered: string; protected: boolean };
}

export interface PortalCommission {
  id: number;
  slug: string;
  title: { rendered: string };
  content: { rendered: string; protected: boolean };
  excerpt: { rendered: string; protected: boolean };
}

export interface PortalSession {
  id: number;
  date: string;
  slug: string;
  title: { rendered: string };
  content: { rendered: string; protected: boolean };
  excerpt: { rendered: string; protected: boolean };
}

export interface PortalProject {
  id: number;
  slug: string;
  title: { rendered: string };
  content: { rendered: string; protected: boolean };
  excerpt: { rendered: string; protected: boolean };
  ck_zone?: number[];
}

export interface PortalMarket {
  id: number;
  date: string;
  slug: string;
  title: { rendered: string };
  content: { rendered: string; protected: boolean };
  excerpt: { rendered: string; protected: boolean };
  link: string;
  ck_zone?: number[];
}

export interface PortalEvent {
  id: number;
  slug: string;
  date: string;
  title: { rendered: string };
  content: { rendered: string; protected: boolean };
  excerpt: { rendered: string; protected: boolean };
  ck_zone?: number[];
}

export interface PortalAlert {
  id: number;
  slug: string;
  date: string;
  title: { rendered: string };
  content: { rendered: string; protected: boolean };
  excerpt: { rendered: string; protected: boolean };
  ck_zone?: number[];
}

export interface PortalDemarche {
  id: number;
  slug: string;
  title: { rendered: string };
  content: { rendered: string; protected: boolean };
  excerpt: { rendered: string; protected: boolean };
  ck_famille_demarche?: number[];
}

export interface PortalDocument {
  id: number;
  date: string;
  slug: string;
  title: { rendered: string };
  content: { rendered: string; protected: boolean };
  excerpt: { rendered: string; protected: boolean };
  link: string;
  ck_zone?: number[];
  ck_type_document?: number[];
}

export interface PortalTerm {
  id: number;
  count: number;
  name: string;
  slug: string;
  taxonomy: string;
}


export interface PortalZone {
  id: number;
  count: number;
  name: string;
  slug: string;
  taxonomy: string;
}

export interface PortalSettings {
  denomination: string;
  presentation: string;
  region: string;
  departement: string;
  arrondissement: string;
  address: string;
  telephone: string;
  email: string;
  hours: Array<{ day: string; value: string }>;
  quickActions: PortalQuickAction[];
}

const fallbackSettings: PortalSettings = {
  denomination: 'Commune de Kafountine',
  presentation:
    'Le portail officiel de la commune : services municipaux, conseil communal, projets et informations utiles.',
  region: 'Ziguinchor',
  departement: 'Bignona',
  arrondissement: 'Diouloulou',
  address: 'Mairie de Kafountine, Sénégal',
  telephone: 'Coordonnées à confirmer',
  email: 'Adresse e-mail à confirmer',
  hours: [
    { day: 'Lundi — vendredi', value: '08:00 — 17:00' },
    { day: 'Samedi — dimanche', value: 'Fermé' },
  ],
  quickActions: [
    {
      label: 'Démarches administratives',
      description: 'Trouvez les pièces, étapes et contacts utiles.',
      href: '/services/',
      icon: '01',
    },
    {
      label: 'Conseil municipal',
      description: 'Élus, commissions, sessions et décisions.',
      href: '/conseil/',
      icon: '02',
    },
    {
      label: 'Documents publics',
      description: 'Accédez aux actes et ressources publiés.',
      href: '/transparence/documents/',
      icon: '03',
    },
  ],
};

export function getPortalSettings(): PortalSettings {
  return fallbackSettings;
}

export function getCmsUrl(): string {
  return import.meta.env.CMS_URL || 'https://cms.communekafountine.com';
}

async function fetchCmsJson<T>(path: string): Promise<T | null> {
  for (let attempt = 1; attempt <= 3; attempt += 1) {
    try {
      const url = new URL(`${getCmsUrl()}${path}`);
      url.searchParams.set('_portal_fetch', `${Date.now()}-${attempt}`);
      const response = await fetch(url, {
        headers: { accept: 'application/json', 'cache-control': 'no-cache' },
        signal: AbortSignal.timeout(12_000),
      });
      if (!response.ok) return null;
      return (await response.json()) as T;
    } catch {
      if (attempt === 3) return null;
    }
  }

  return null;
}

export async function getPortalServices(): Promise<PortalService[]> {
  const services = await fetchCmsJson<PortalService[]>('/wp-json/wp/v2/ck_service?per_page=100&orderby=menu_order&order=asc');
  return Array.isArray(services) ? services : [];
}

export async function getPortalAgents(): Promise<PortalPerson[]> {
  const agents = await fetchCmsJson<PortalPerson[]>('/wp-json/wp/v2/ck_agent?per_page=100&orderby=title&order=asc');
  return Array.isArray(agents) ? agents : [];
}

export async function getPortalCouncilMembers(): Promise<PortalPerson[]> {
  const members = await fetchCmsJson<PortalPerson[]>('/wp-json/wp/v2/ck_elu?per_page=100&orderby=title&order=asc');
  return Array.isArray(members) ? members : [];
}

export async function getPortalCommissions(): Promise<PortalCommission[]> {
  const commissions = await fetchCmsJson<PortalCommission[]>('/wp-json/wp/v2/ck_commission?per_page=100&orderby=title&order=asc');
  return Array.isArray(commissions) ? commissions : [];
}

export async function getPortalSessions(): Promise<PortalSession[]> {
  const sessions = await fetchCmsJson<PortalSession[]>('/wp-json/wp/v2/ck_session?per_page=100&orderby=date&order=desc');
  return Array.isArray(sessions) ? sessions : [];
}

export async function getPortalProjects(): Promise<PortalProject[]> {
  const projects = await fetchCmsJson<PortalProject[]>('/wp-json/wp/v2/ck_projet?per_page=100&orderby=date&order=desc');
  return Array.isArray(projects) ? projects : [];
}

export async function getPortalMarkets(): Promise<PortalMarket[]> {
  const markets = await fetchCmsJson<PortalMarket[]>('/wp-json/wp/v2/ck_marche?per_page=100&orderby=date&order=desc');
  return Array.isArray(markets) ? markets : [];
}

export async function getPortalEvents(): Promise<PortalEvent[]> {
  const events = await fetchCmsJson<PortalEvent[]>('/wp-json/wp/v2/ck_evenement?per_page=100&orderby=date&order=asc');
  return Array.isArray(events) ? events : [];
}

export async function getPortalAlerts(): Promise<PortalAlert[]> {
  const alerts = await fetchCmsJson<PortalAlert[]>('/wp-json/wp/v2/ck_alerte?per_page=100&orderby=date&order=desc');
  return Array.isArray(alerts) ? alerts : [];
}

export async function getPortalDemarches(): Promise<PortalDemarche[]> {
  const demarches = await fetchCmsJson<PortalDemarche[]>('/wp-json/wp/v2/ck_demarche?per_page=100&orderby=title&order=asc');
  return Array.isArray(demarches) ? demarches : [];
}

export async function getPortalDemarcheFamilies(): Promise<PortalTerm[]> {
  const families = await fetchCmsJson<PortalTerm[]>('/wp-json/wp/v2/ck_famille_demarche?per_page=100&orderby=name&order=asc');
  return Array.isArray(families) ? families : [];
}

export async function getPortalDocuments(): Promise<PortalDocument[]> {
  const documents = await fetchCmsJson<PortalDocument[]>('/wp-json/wp/v2/ck_document?per_page=100&orderby=date&order=desc');
  return Array.isArray(documents) ? documents : [];
}

export async function getPortalDocumentTypes(): Promise<PortalTerm[]> {
  const types = await fetchCmsJson<PortalTerm[]>('/wp-json/wp/v2/ck_type_document?per_page=100&orderby=name&order=asc');
  return Array.isArray(types) ? types : [];
}

export async function getPortalZones(): Promise<PortalZone[]> {
  const zones = await fetchCmsJson<PortalZone[]>('/wp-json/wp/v2/ck_zone?per_page=100&orderby=name&order=asc');
  return Array.isArray(zones) ? zones : [];
}

export interface PortalSearchEntry {
  title: string;
  excerpt: string;
  type: string;
  href: string;
}

function stripHtml(value: string): string {
  return value.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
}

function searchText(value: { rendered: string }): string {
  return stripHtml(value.rendered || '');
}

export async function getPortalSearchEntries(): Promise<PortalSearchEntry[]> {
  const [services, agents, members, commissions, sessions, zones, projects, markets, demarches, events, alerts, documents] = await Promise.all([
    getPortalServices(),
    getPortalAgents(),
    getPortalCouncilMembers(),
    getPortalCommissions(),
    getPortalSessions(),
    getPortalZones(),
    getPortalProjects(),
    getPortalMarkets(),
    getPortalDemarches(),
    getPortalEvents(),
    getPortalAlerts(),
    getPortalDocuments(),
  ]);

  return [
    ...services.map((service) => ({
      title: searchText(service.title),
      excerpt: searchText(service.excerpt) || searchText(service.content),
      type: 'Service municipal',
      href: `/services/${service.slug}/`,
    })),
    ...agents.map((agent) => ({
      title: searchText(agent.title),
      excerpt: searchText(agent.excerpt) || searchText(agent.content),
      type: 'Agent municipal',
      href: `/mairie/agents/${agent.slug}/`,
    })),
    ...members.map((member) => ({
      title: searchText(member.title),
      excerpt: searchText(member.excerpt) || searchText(member.content),
      type: 'Élu municipal',
      href: `/conseil/elus/${member.slug}/`,
    })),
    ...commissions.map((commission) => ({
      title: searchText(commission.title),
      excerpt: searchText(commission.excerpt) || searchText(commission.content),
      type: 'Commission municipale',
      href: `/conseil/commissions/${commission.slug}/`,
    })),
    ...sessions.map((session) => ({
      title: searchText(session.title),
      excerpt: searchText(session.excerpt) || searchText(session.content),
      type: 'Session du conseil municipal',
      href: `/conseil/sessions/${session.slug}/`,
    })),
    ...zones.map((zone) => ({
      title: zone.name,
      excerpt: `${zone.count} contenus territoriaux associés`,
      type: 'Village ou zone',
      href: '/villages/',
    })),
    ...projects.map((project) => ({
      title: searchText(project.title),
      excerpt: searchText(project.excerpt) || searchText(project.content),
      type: 'Projet municipal',
      href: `/projets/${project.slug}/`,
    })),
    ...markets.map((market) => ({
      title: searchText(market.title),
      excerpt: searchText(market.excerpt) || searchText(market.content),
      type: 'Marché public',
      href: `/transparence/marches/${market.slug}/`,
    })),
    ...demarches.map((demarche) => ({
      title: searchText(demarche.title),
      excerpt: searchText(demarche.excerpt) || searchText(demarche.content),
      type: 'Démarche administrative',
      href: `/demarches/${demarche.slug}/`,
    })),
    ...events.map((event) => ({
      title: searchText(event.title),
      excerpt: searchText(event.excerpt) || searchText(event.content),
      type: 'Événement communal',
      href: `/agenda/${event.slug}/`,
    })),
    ...alerts.map((alert) => ({
      title: searchText(alert.title),
      excerpt: searchText(alert.excerpt) || searchText(alert.content),
      type: 'Alerte communale',
      href: '/participer/',
    })),
    ...documents.map((document) => ({
      title: searchText(document.title),
      excerpt: searchText(document.excerpt) || searchText(document.content),
      type: 'Document public',
      href: '/transparence/documents/',
    })),
  ].filter((entry) => entry.title.length > 0);
}
