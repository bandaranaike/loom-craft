import { Head, Link, usePage } from '@inertiajs/react';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { pending as adminVendorPending } from '@/routes/admin/vendors';
import { register as vendorRegister } from '@/routes/vendor';
import type { SharedData } from '@/types';
import type { BreadcrumbItem } from '@/types';

type Props = {
    status?: string;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

export default function Dashboard() {
    const { status, auth } = usePage<SharedData & Props>().props;
    const isAdmin = auth?.user?.role === 'admin';

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {status && (
                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-100">
                        {status}
                    </div>
                )}
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="relative flex h-full flex-col justify-between overflow-hidden rounded-xl border border-sidebar-border/70 bg-sidebar/40 p-6 dark:border-sidebar-border">
                        <div className="space-y-2">
                            <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                Vendor Access
                            </p>
                            <h2 className="text-xl font-semibold text-foreground">
                                Apply to become a vendor
                            </h2>
                            <p className="text-sm text-muted-foreground">
                                Submit your artisan profile for manual approval.
                            </p>
                        </div>
                        <Link
                            href={vendorRegister()}
                            className="mt-4 inline-flex w-fit items-center rounded-full border border-foreground/70 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-foreground transition hover:bg-foreground hover:text-background"
                        >
                            Start Application
                        </Link>
                    </div>
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                    <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                </div>
                {isAdmin && (
                    <div className="rounded-xl border border-sidebar-border/70 bg-sidebar/30 p-6 dark:border-sidebar-border">
                        <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                    Admin Review
                                </p>
                                <h2 className="text-lg font-semibold text-foreground">
                                    Pending vendor approvals
                                </h2>
                                <p className="text-sm text-muted-foreground">
                                    Review and approve artisan applications.
                                </p>
                            </div>
                            <Link
                                href={adminVendorPending()}
                                className="inline-flex items-center rounded-full border border-foreground/70 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-foreground transition hover:bg-foreground hover:text-background"
                            >
                                Review Vendors
                            </Link>
                        </div>
                    </div>
                )}
                <div className="relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                    <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                </div>
            </div>
        </AppLayout>
    );
}
