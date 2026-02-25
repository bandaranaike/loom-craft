import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import LegalLinks from '@/components/legal-links';
import type { AppLayoutProps } from '@/types';

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
}: AppLayoutProps) {
    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar" className="overflow-x-hidden">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
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
