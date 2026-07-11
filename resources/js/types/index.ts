export type * from './auth';
export type * from './navigation';
export type * from './ui';

import type { Auth } from './auth';

export type SiteConfig = {
    key: 'loomcraft' | 'naturesnature' | string;
    name: string;
    displayName: string;
    domain: string;
    theme: string;
    tagline: string;
    description: string;
    marketplaceLabel: string;
    productsLabel: string;
    dashboardLabel: string;
    registerLabel: string;
    vendorLabel: string;
    vendorPluralLabel: string;
    reviewerLabel: string;
    hideLoomFeatures: boolean;
};

export type SharedData = {
    name: string;
    site: SiteConfig;
    auth: Auth;
    sidebarOpen: boolean;
    [key: string]: unknown;
};
