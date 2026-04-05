import { Head, usePage } from '@inertiajs/react';
import { Smartphone, ShieldCheck, Trash2 } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { destroy, index as connectedDevicesIndex } from '@/routes/connected-devices';
import type { BreadcrumbItem, SharedData } from '@/types';

type ConnectedDevice = {
    id: number;
    name: string;
    abilities: string[];
    created_at: string | null;
    last_used_at: string | null;
    expires_at: string | null;
};

type Props = {
    status?: string;
    tokens: ConnectedDevice[];
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Connected Devices',
        href: connectedDevicesIndex().url,
    },
];

const formatDateTime = (value: string | null): string => {
    if (value === null) {
        return 'Never';
    }

    return new Intl.DateTimeFormat('en-LK', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const abilityLabel = (ability: string): string => {
    switch (ability) {
        case 'orders:read':
            return 'Read orders';
        case 'orders:update':
            return 'Update orders';
        case 'notifications:register':
            return 'Register notifications';
        case 'stickers:read':
            return 'Read stickers';
        default:
            return ability;
    }
};

export default function ConnectedDevicesIndex() {
    const { auth, status, tokens } = usePage<SharedData & Props>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Connected Devices" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-hidden rounded-xl p-4">
                {status && (
                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-100">
                        {status}
                    </div>
                )}

                <section className="rounded-3xl border border-sidebar-border/70 bg-sidebar/20 p-6 dark:border-sidebar-border">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div className="space-y-2">
                            <p className="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                                Mobile API access
                            </p>
                            <h1 className="text-3xl font-semibold text-foreground">
                                Connected devices
                            </h1>
                            <p className="max-w-2xl text-sm text-muted-foreground">
                                Review the mobile sessions issued for your account and revoke any device you no longer trust.
                            </p>
                        </div>

                        <div className="rounded-2xl border border-sidebar-border/70 bg-background px-4 py-3 text-sm text-muted-foreground dark:border-sidebar-border">
                            Signed in as <span className="font-semibold text-foreground">{auth.user.name}</span>
                        </div>
                    </div>
                </section>

                {tokens.length === 0 ? (
                    <section className="rounded-3xl border border-dashed border-sidebar-border/80 bg-background p-10 text-center dark:border-sidebar-border">
                        <div className="mx-auto flex max-w-md flex-col items-center gap-4">
                            <div className="rounded-full border border-sidebar-border/70 p-4 dark:border-sidebar-border">
                                <Smartphone className="size-6 text-muted-foreground" />
                            </div>
                            <div className="space-y-2">
                                <h2 className="text-xl font-semibold text-foreground">
                                    No connected mobile devices
                                </h2>
                                <p className="text-sm text-muted-foreground">
                                    Mobile API sessions will appear here after you sign in from the LoomCraft mobile app.
                                </p>
                            </div>
                        </div>
                    </section>
                ) : (
                    <section className="grid gap-4 xl:grid-cols-2">
                        {tokens.map((token) => (
                            <article
                                key={token.id}
                                className="rounded-3xl border border-sidebar-border/70 bg-background p-6 shadow-xs dark:border-sidebar-border"
                            >
                                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div className="space-y-3">
                                        <div className="flex items-center gap-3">
                                            <div className="rounded-full border border-sidebar-border/70 p-3 dark:border-sidebar-border">
                                                <Smartphone className="size-5 text-foreground" />
                                            </div>
                                            <div>
                                                <p className="text-lg font-semibold text-foreground">
                                                    {token.name}
                                                </p>
                                                <p className="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                                                    Token #{token.id}
                                                </p>
                                            </div>
                                        </div>

                                        <div className="grid gap-3 sm:grid-cols-2">
                                            <div className="rounded-2xl border border-sidebar-border/70 bg-sidebar/10 p-4 dark:border-sidebar-border">
                                                <p className="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                                                    Issued
                                                </p>
                                                <p className="mt-2 text-sm text-foreground">
                                                    {formatDateTime(token.created_at)}
                                                </p>
                                            </div>
                                            <div className="rounded-2xl border border-sidebar-border/70 bg-sidebar/10 p-4 dark:border-sidebar-border">
                                                <p className="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                                                    Last used
                                                </p>
                                                <p className="mt-2 text-sm text-foreground">
                                                    {formatDateTime(token.last_used_at)}
                                                </p>
                                            </div>
                                        </div>

                                        <div className="rounded-2xl border border-sidebar-border/70 bg-sidebar/10 p-4 dark:border-sidebar-border">
                                            <div className="flex items-center gap-2">
                                                <ShieldCheck className="size-4 text-muted-foreground" />
                                                <p className="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                                                    Abilities
                                                </p>
                                            </div>
                                            <div className="mt-3 flex flex-wrap gap-2">
                                                {token.abilities.map((ability) => (
                                                    <span
                                                        key={ability}
                                                        className="rounded-full border border-sidebar-border/70 px-3 py-1 text-xs font-semibold text-foreground dark:border-sidebar-border"
                                                    >
                                                        {abilityLabel(ability)}
                                                    </span>
                                                ))}
                                            </div>
                                        </div>
                                    </div>

                                    <form
                                        action={destroy.url(token.id)}
                                        method="post"
                                        className="sm:min-w-40"
                                    >
                                        <input type="hidden" name="_method" value="DELETE" />
                                        <button
                                            type="submit"
                                            className="inline-flex w-full items-center justify-center gap-2 rounded-full border border-red-300 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-red-700 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-red-900/60 dark:text-red-300 dark:hover:bg-red-950/30"
                                        >
                                            <Trash2 className="size-4" />
                                            Revoke
                                        </button>
                                    </form>
                                </div>
                            </article>
                        ))}
                    </section>
                )}
            </div>
        </AppLayout>
    );
}
