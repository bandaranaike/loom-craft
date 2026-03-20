import { Link } from '@inertiajs/react';
import { Moon, Sun } from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Breadcrumbs } from '@/components/breadcrumbs';
import DashboardLogoIcon from '@/components/dashboard-logo-icon';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { useAppearance } from '@/hooks/use-appearance';
import { dashboard, home } from '@/routes';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const isDark = resolvedAppearance === 'dark';

    return (
        <header className="mb-2 flex h-16 min-w-0 shrink-0 items-center gap-2 rounded-xl border-(--welcome-border) bg-(--welcome-surface-1) px-3 text-(--welcome-strong) transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 sm:px-4">
            <div className="flex min-w-0 items-center gap-2">
                <SidebarTrigger className="-ml-1 rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) text-(--welcome-strong) hover:bg-(--welcome-surface-1)" />
                <Link
                    href={home()}
                    className="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-(--welcome-border) bg-(--welcome-surface-3) p-1 transition hover:bg-(--welcome-surface-1)"
                    aria-label="Main site"
                    title="Main site"
                >
                    <AppLogoIcon className="h-8 w-auto object-contain" />
                </Link>
                <Link
                    href={dashboard()}
                    className="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl transition hover:opacity-90"
                    aria-label="Dashboard"
                    title="Dashboard"
                >
                    <DashboardLogoIcon className="h-10 w-10" />
                </Link>
                <div className="hidden h-8 w-px shrink-0 bg-(--welcome-border) md:block" />
                <div className="hidden min-w-0 md:block">
                    <Breadcrumbs breadcrumbs={breadcrumbs} />
                </div>
            </div>
            <button
                type="button"
                onClick={() => updateAppearance(isDark ? 'light' : 'dark')}
                className="ml-auto inline-flex h-9 w-9 items-center justify-center rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) text-(--welcome-muted-text) transition hover:bg-(--welcome-surface-1) hover:text-(--welcome-strong)"
                aria-label={isDark ? 'Switch to light theme' : 'Switch to dark theme'}
                title={isDark ? 'Switch to light theme' : 'Switch to dark theme'}
            >
                {isDark ? <Sun className="h-4 w-4" /> : <Moon className="h-4 w-4" />}
            </button>
        </header>
    );
}
