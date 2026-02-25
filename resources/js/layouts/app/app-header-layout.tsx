import { AppContent } from '@/components/app-content';
import { AppHeader } from '@/components/app-header';
import { AppShell } from '@/components/app-shell';
import LegalLinks from '@/components/legal-links';
import type { AppLayoutProps } from '@/types';

export default function AppHeaderLayout({
    children,
    breadcrumbs,
}: AppLayoutProps) {
    return (
        <AppShell>
            <AppHeader breadcrumbs={breadcrumbs} />
            <AppContent>
                {children}
                <div className="border-t px-4 py-6 sm:px-6">
                    <LegalLinks
                        className="flex flex-wrap gap-4 text-xs text-muted-foreground"
                        linkClassName="transition hover:text-foreground"
                    />
                </div>
            </AppContent>
        </AppShell>
    );
}
