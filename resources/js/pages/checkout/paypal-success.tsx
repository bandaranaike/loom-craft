import { Link } from '@inertiajs/react';
import { ArrowRight, CheckCircle2, MailCheck, ReceiptText } from 'lucide-react';
import SeoHead from '@/components/seo-head';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { home } from '@/routes';
import { index as productsIndex } from '@/routes/products';

const actionClassName =
    'inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-(--welcome-strong) px-5 py-3 text-xs font-semibold tracking-[0.24em] text-(--welcome-on-strong) uppercase transition hover:-translate-y-0.5 hover:bg-(--welcome-strong-hover)';
const secondaryActionClassName =
    'inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-(--welcome-strong) px-5 py-3 text-xs font-semibold tracking-[0.24em] text-(--welcome-strong) uppercase transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)';

type PayPalSuccessProps = {
    canRegister?: boolean;
    headline: string;
    receiptMessage: string;
    detailsMessage: string;
};

export default function PayPalSuccess({
    canRegister = true,
    headline,
    receiptMessage,
    detailsMessage,
}: PayPalSuccessProps) {
    return (
        <>
            <SeoHead
                title="Payment Complete - LoomCraft"
                description="Your LoomCraft PayPal payment is complete."
                canonical="/checkout/paypal/success"
                noIndex
            >
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </SeoHead>

            <PublicSiteLayout canRegister={canRegister}>
                <section className="mx-auto grid w-full max-w-6xl gap-10 px-6 pt-4 pb-16 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                    <div className="space-y-6">
                        <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                            PayPal Payment
                        </p>
                        <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-6xl">
                            {headline}
                        </h1>
                        <p className="max-w-2xl text-sm leading-7 text-(--welcome-body-text) md:text-base">
                            {receiptMessage}
                        </p>
                        <div className="flex flex-col gap-3 sm:flex-row">
                            <Link href={productsIndex()} className={actionClassName}>
                                Browse products
                                <ArrowRight className="h-4 w-4" />
                            </Link>
                            <Link href={home()} className={secondaryActionClassName}>
                                Return home
                            </Link>
                        </div>
                    </div>

                    <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6 shadow-[0_18px_50px_-42px_var(--welcome-shadow)]">
                        <div className="flex items-center gap-3">
                            <span className="inline-flex h-12 w-12 items-center justify-center rounded-full bg-(--welcome-surface-1) text-(--welcome-accent)">
                                <CheckCircle2 className="h-6 w-6" />
                            </span>
                            <div>
                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                    Payment made
                                </p>
                                <p className="mt-1 text-sm text-(--welcome-body-text)">
                                    The PayPal transaction is complete.
                                </p>
                            </div>
                        </div>

                        <div className="mt-6 grid gap-4">
                            <article className="rounded-[22px] border border-(--welcome-border-soft) bg-(--welcome-surface-1) p-5">
                                <ReceiptText className="h-5 w-5 text-(--welcome-accent)" />
                                <h2 className="mt-4 font-['Playfair_Display',serif] text-2xl">
                                    Transaction details
                                </h2>
                                <p className="mt-3 text-sm leading-6 text-(--welcome-body-text)">
                                    PayPal keeps the final payment record in your PayPal account.
                                </p>
                            </article>
                            <article className="rounded-[22px] border border-(--welcome-border-soft) bg-(--welcome-surface-1) p-5">
                                <MailCheck className="h-5 w-5 text-(--welcome-accent)" />
                                <h2 className="mt-4 font-['Playfair_Display',serif] text-2xl">
                                    Email receipt
                                </h2>
                                <p className="mt-3 text-sm leading-6 text-(--welcome-body-text)">
                                    {detailsMessage}
                                </p>
                            </article>
                        </div>
                    </div>
                </section>
            </PublicSiteLayout>
        </>
    );
}
