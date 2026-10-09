export interface PortalQuickAction {
  label: string;
  description: string;
  href: string;
  icon: string;
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
