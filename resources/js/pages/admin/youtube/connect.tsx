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
            <Head title="YouTube Connect">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-[24px] bg-(--welcome-on-strong) p-5 text-(--welcome-strong)">
                {status && (
                    <div className="rounded-[24px] border border-(--welcome-accent-40) bg-(--welcome-surface-3) px-6 py-4 text-sm text-(--welcome-muted-text)">
                        {status}
                    </div>
                )}

                <div className="rounded-[28px] border border-(--welcome-border) bg-(--welcome-surface-1) p-7 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]">
                    <div className="flex flex-col gap-3">
                        <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                            Video Uploads
                        </p>
                        <h2 className="font-['Playfair_Display',serif] text-3xl text-(--welcome-strong)">
                            Connect your YouTube account
                        </h2>
                        <p className="text-sm text-(--welcome-body-text)">
                            LoomCraft uploads product videos to your configured
                            YouTube channel and stores the resulting links.
                        </p>
                        <div>
                            <a
                                href={auth_url}
                                className="inline-flex items-center justify-center rounded-full border border-(--welcome-muted-text) px-5 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text) transition hover:bg-(--welcome-muted-text) hover:text-(--welcome-surface-3)"
                            >
                                Authorize YouTube Uploads
                            </a>
                        </div>
                    </div>
                </div>

                <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]">
                    <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                        Refresh Token
                    </p>
                    {refresh_token ? (
                        <>
                            <p className="mt-3 text-sm text-(--welcome-body-text)">
                                Save this value in `YOUTUBE_REFRESH_TOKEN` in
                                `.env` and redeploy.
                            </p>
                            <pre className="mt-4 overflow-x-auto rounded-[20px] border border-(--welcome-border) bg-(--welcome-surface-2) p-4 text-xs text-(--welcome-strong)">
                                {refresh_token}
                            </pre>
                        </>
                    ) : (
                        <p className="mt-3 text-sm text-(--welcome-body-text)">
                            No refresh token yet. Complete the authorization
                            flow above to receive one.
                        </p>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
