import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowRight, MessageCircle, PackageSearch, ShieldCheck, Tag } from 'lucide-react';
import ContactController from '@/actions/App/Http/Controllers/ContactController';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { dashboard, login } from '@/routes';
import { index as productsIndex } from '@/routes/products';
import type { SharedData } from '@/types';

const optionClassName =
    'rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6 shadow-[0_18px_50px_-42px_var(--welcome-shadow)]';
const actionClassName =
    'inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-(--welcome-strong) px-5 py-3 text-xs font-semibold uppercase tracking-[0.24em] text-(--welcome-on-strong) transition hover:-translate-y-0.5 hover:bg-(--welcome-strong-hover)';
const secondaryActionClassName =
    'inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-(--welcome-strong) px-5 py-3 text-xs font-semibold uppercase tracking-[0.24em] text-(--welcome-strong) transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)';

export default function ManagePlans() {
    const { auth } = usePage<SharedData>().props;
    const accountHref = auth.user ? dashboard() : login({ query: { redirect: '/manage-plans' } });

    return (
        <>
            <Head title="Manage Plans — LoomCraft">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>

            <PublicSiteLayout>
                <section className="mx-auto grid w-full max-w-6xl gap-10 px-6 pt-4 pb-16 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                    <div className="space-y-6">
                        <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                            Autopay Support
                        </p>
                        <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-6xl">
                            Manage your plan before cancelling.
                        </h1>
                        <p className="max-w-2xl text-sm leading-7 text-(--welcome-body-text) md:text-base">
                            Review your LoomCraft account, switch to a better-fit option, or ask our team to help
                            adjust your plan before you stop automatic payments.
                        </p>
                        <div className="flex flex-col gap-3 sm:flex-row">
                            <Link href={accountHref} className={actionClassName}>
                                <ShieldCheck className="h-4 w-4" />
                                Review account
                                <ArrowRight className="h-4 w-4" />
                            </Link>
                            <Link href={ContactController.show()} className={secondaryActionClassName}>
                                <MessageCircle className="h-4 w-4" />
                                Talk to support
                            </Link>
                        </div>
                    </div>

                    <div className="rounded-[36px] border border-(--welcome-border) bg-(--welcome-surface-1) p-6 shadow-[0_30px_80px_-45px_var(--welcome-shadow)]">
                        <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                            Options Before You Leave
                        </p>
                        <div className="mt-5 grid gap-4">
                            <article className={optionClassName}>
                                <Tag className="h-5 w-5 text-(--welcome-accent)" />
                                <h2 className="mt-4 font-['Playfair_Display',serif] text-2xl">
                                    Request a lighter plan
                                </h2>
                                <p className="mt-3 text-sm leading-6 text-(--welcome-body-text)">
                                    Ask us to reduce future commitments or move you to a simpler account arrangement.
                                </p>
                            </article>
                            <article className={optionClassName}>
                                <PackageSearch className="h-5 w-5 text-(--welcome-accent)" />
                                <h2 className="mt-4 font-['Playfair_Display',serif] text-2xl">
                                    Browse alternatives
                                </h2>
                                <p className="mt-3 text-sm leading-6 text-(--welcome-body-text)">
                                    Explore current woven collections and one-time purchases before changing payments.
                                </p>
                                <Link
                                    href={productsIndex()}
                                    className="mt-5 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.24em] text-(--welcome-strong)"
                                >
                                    Browse products
                                    <ArrowRight className="h-4 w-4" />
                                </Link>
                            </article>
                        </div>
                    </div>
                </section>
            </PublicSiteLayout>
        </>
    );
}
