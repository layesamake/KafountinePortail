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
  try {
    const response = await fetch(`${getCmsUrl()}${path}`, {
      headers: { accept: 'application/json' },
      signal: AbortSignal.timeout(8_000),
    });
    if (!response.ok) return null;
    return (await response.json()) as T;
  } catch {
    return null;
  }
}

export async function getPortalServices(): Promise<PortalService[]> {
  const services = await fetchCmsJson<PortalService[]>('/wp-json/wp/v2/ck_service?per_page=100&orderby=menu_order&order=asc');
  return Array.isArray(services) ? services : [];
}

export async function getPortalCouncilMembers(): Promise<PortalPerson[]> {
  const members = await fetchCmsJson<PortalPerson[]>('/wp-json/wp/v2/ck_elu?per_page=100&orderby=title&order=asc');
  return Array.isArray(members) ? members : [];
}

export async function getPortalCommissions(): Promise<PortalCommission[]> {
  const commissions = await fetchCmsJson<PortalCommission[]>('/wp-json/wp/v2/ck_commission?per_page=100&orderby=title&order=asc');
  return Array.isArray(commissions) ? commissions : [];
}

export async function getPortalZones(): Promise<PortalZone[]> {
  const zones = await fetchCmsJson<PortalZone[]>('/wp-json/wp/v2/ck_zone?per_page=100&orderby=name&order=asc');
  return Array.isArray(zones) ? zones : [];
}
