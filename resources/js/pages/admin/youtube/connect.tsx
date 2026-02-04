import { Head, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { connect as adminYouTubeConnect } from '@/routes/admin/youtube';
import type { BreadcrumbItem } from '@/types';

type Props = {
    auth_url: string;
    refresh_token?: string | null;
    status?: string;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'YouTube Connect',
        href: adminYouTubeConnect().url,
    },
];

export default function YouTubeConnect() {
    const { auth_url, refresh_token, status } = usePage<Props>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="YouTube Connect" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                {status && (
                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-100">
                        {status}
                    </div>
                )}

                <div className="rounded-xl border border-sidebar-border/70 bg-sidebar/30 p-6 dark:border-sidebar-border">
                    <div className="flex flex-col gap-3">
                        <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                            Video Uploads
                        </p>
                        <h2 className="text-2xl font-semibold text-foreground">
                            Connect your YouTube account
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            LoomCraft uploads product videos to your configured
                            YouTube channel and stores the resulting links.
                        </p>
                        <div>
                            <a
                                href={auth_url}
                                className="inline-flex items-center justify-center rounded-full border border-foreground/70 px-5 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-foreground transition hover:bg-foreground hover:text-background"
                            >
                                Authorize YouTube Uploads
                            </a>
                        </div>
                    </div>
                </div>

                <div className="rounded-xl border border-sidebar-border/70 bg-background p-6 shadow-xs dark:border-sidebar-border">
                    <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                        Refresh Token
                    </p>
                    {refresh_token ? (
                        <>
                            <p className="mt-3 text-sm text-foreground">
                                Save this value in `YOUTUBE_REFRESH_TOKEN` in
                                `.env` and redeploy.
                            </p>
                            <pre className="mt-4 overflow-x-auto rounded-xl border border-sidebar-border/70 bg-sidebar/30 p-4 text-xs text-foreground dark:border-sidebar-border">
                                {refresh_token}
                            </pre>
                        </>
                    ) : (
                        <p className="mt-3 text-sm text-muted-foreground">
                            No refresh token yet. Complete the authorization
                            flow above to receive one.
                        </p>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
