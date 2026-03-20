import { LayoutGrid } from 'lucide-react';
import { cn } from '@/lib/utils';

type DashboardLogoIconProps = {
    className?: string;
};

export default function DashboardLogoIcon({
    className,
}: DashboardLogoIconProps) {
    return (
        <span
            className={cn(
                'inline-flex items-center justify-center rounded-2xl border border-(--welcome-border) bg-(--welcome-surface-3) text-(--welcome-strong) shadow-[0_12px_30px_-24px_var(--welcome-shadow-strong)]',
                className,
            )}
        >
            <LayoutGrid className="h-4 w-4" />
        </span>
    );
}
