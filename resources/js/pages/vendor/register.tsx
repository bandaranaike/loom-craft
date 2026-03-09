import { Form, Head, Link, usePage } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes';
import { store } from '@/routes/vendor/register';

type Props = {
    status?: string;
};

const inputClassName =
    'w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-3 text-sm text-(--welcome-strong) placeholder:text-(--welcome-muted-70) shadow-[0_8px_20px_-18px_var(--welcome-shadow-strong)] focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)';

export default function VendorRegister() {
    const { status } = usePage<Props>().props;

    return (
        <>
            <Head title="Vendor Registration">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <PublicSiteLayout
                canRegister={false}
                headerActions={
                    <Link
                        href={dashboard()}
                        className="rounded-full border border-(--welcome-strong) px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-strong) transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                    >
                        Back to Dashboard
                    </Link>
                }
            >
                    <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-10 px-6 pb-16 pt-4 lg:grid-cols-[1.05fr_0.95fr]">
                        <div className="space-y-6">
                            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                Vendor Application
                            </p>
                            <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                                Apply to sell heritage pieces on LoomCraft.
                            </h1>
                            <p className="max-w-xl text-sm text-(--welcome-body-text) md:text-base">
                                Vendor approval is manual to preserve craft quality and
                                provenance. Start with the compulsory studio name now,
                                then complete the full public profile after creation.
                            </p>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5">
                                    <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Marketplace Standards
                                    </p>
                                    <p className="mt-3 text-sm text-(--welcome-body-text)">
                                        Listings follow LoomCraft quality checks for heritage,
                                        presentation, and artisan authenticity.
                                    </p>
                                </div>
                                <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5">
                                    <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Approval Process
                                    </p>
                                    <p className="mt-3 text-sm text-(--welcome-body-text)">
                                        Every vendor application is reviewed by LoomCraft
                                        curators.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="relative">
                            <div className="absolute -right-4 top-10 h-48 w-48 rounded-[32px] border border-(--welcome-border) bg-(--welcome-surface-2) shadow-[0_20px_60px_-30px_var(--welcome-shadow-soft)]" />
                            <div className="relative rounded-[36px] border border-(--welcome-border) bg-(--welcome-surface-1) p-8 shadow-[0_30px_80px_-45px_var(--welcome-shadow)]">
                                <div className="space-y-2">
                                    <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Vendor Profile
                                    </p>
                                    <h2 className="font-['Playfair_Display',serif] text-2xl">
                                        Registration details
                                    </h2>
                                    <p className="text-sm text-(--welcome-body-text)">
                                        Submit the minimum details required to open your vendor record.
                                    </p>
                                </div>

                                {status && (
                                    <div className="mt-4 rounded-[24px] border border-(--welcome-accent-40) bg-(--welcome-surface-3) px-4 py-3 text-sm text-(--welcome-muted-text)">
                                        {status}
                                    </div>
                                )}

                                <Form
                                    {...store.form()}
                                    className="mt-6 grid gap-5"
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            <div className="grid gap-2">
                                                <label
                                                    htmlFor="display_name"
                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                >
                                                    Display name
                                                </label>
                                                <input
                                                    id="display_name"
                                                    type="text"
                                                    name="display_name"
                                                    placeholder="Studio name"
                                                    className={inputClassName}
                                                    required
                                                />
                                                <InputError
                                                    message={errors.display_name}
                                                    className="text-xs"
                                                />
                                            </div>

                                            <button
                                                type="submit"
                                                className="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-full bg-(--welcome-strong) px-6 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-on-strong) transition hover:-translate-y-0.5 hover:bg-(--welcome-strong-hover) disabled:cursor-not-allowed disabled:opacity-70"
                                                disabled={processing}
                                            >
                                                {processing && (
                                                    <Spinner className="text-(--welcome-on-strong)" />
                                                )}
                                                Submit for Review
                                            </button>
                                            <p className="text-center text-xs uppercase tracking-[0.25em] text-(--welcome-muted-text)">
                                                Full profile details can be completed after registration.
                                            </p>
                                        </>
                                    )}
                                </Form>
                            </div>
                        </div>
                    </section>
            </PublicSiteLayout>
        </>
    );
}
