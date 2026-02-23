import { Moon, Sun } from 'lucide-react';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { useAppearance } from '@/hooks/use-appearance';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function AppSidebarHeader({
    breadcrumbs = [],
}: {
    breadcrumbs?: BreadcrumbItemType[];
}) {
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const isDark = resolvedAppearance === 'dark';

    return (
        <header className="flex h-16 shrink-0 items-center rounded-xl mb-2 gap-2 border-(--welcome-border) bg-(--welcome-surface-1) px-6 text-(--welcome-strong) transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
            <div className="flex items-center gap-2">
                <SidebarTrigger className="-ml-1 rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) text-(--welcome-strong) hover:bg-(--welcome-surface-1)" />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
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
