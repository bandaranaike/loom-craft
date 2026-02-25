import { Head } from '@inertiajs/react';
import PublicSiteLayout from '@/layouts/public-site-layout';

const sectionClassName =
    'rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6 md:p-8';

export default function TermsOfService({ canRegister = true }: { canRegister?: boolean }) {
    return (
        <>
            <Head title="Terms of Service — LoomCraft">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <PublicSiteLayout canRegister={canRegister}>
                <section className="mx-auto w-full max-w-4xl px-6 pb-10 pt-6">
                    <div className="space-y-4">
                        <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                            Legal
                        </p>
                        <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                            Terms of Service
                        </h1>
                        <p className="text-sm text-(--welcome-body-text)">
                            Effective date: February 25, 2026
                        </p>
                    </div>
                </section>

                <section className="mx-auto w-full max-w-4xl space-y-5 px-6 pb-20">
                    <article className={sectionClassName}>
                        <h2 className="font-['Playfair_Display',serif] text-2xl">
                            Acceptance of Terms
                        </h2>
                        <p className="mt-3 text-sm leading-7 text-(--welcome-body-text)">
                            By accessing or using LoomCraft, you agree to these Terms of Service.
                            If you do not agree, do not use the platform.
                        </p>
                    </article>

                    <article className={sectionClassName}>
                        <h2 className="font-['Playfair_Display',serif] text-2xl">
                            Marketplace Model
                        </h2>
                        <p className="mt-3 text-sm leading-7 text-(--welcome-body-text)">
                            LoomCraft operates a multi-vendor marketplace for woven products.
                            Vendors are responsible for product listings, fulfillment obligations,
                            and listing accuracy. LoomCraft applies a fixed platform commission as
                            defined in platform rules.
                        </p>
                    </article>

                    <article className={sectionClassName}>
                        <h2 className="font-['Playfair_Display',serif] text-2xl">
                            User Accounts and Conduct
                        </h2>
                        <ul className="mt-3 list-disc space-y-2 pl-5 text-sm leading-7 text-(--welcome-body-text)">
                            <li>Users must provide accurate account and transaction information.</li>
                            <li>
                                Unauthorized access, abuse, fraud, or interference with the
                                platform is prohibited.
                            </li>
                            <li>
                                LoomCraft may suspend or terminate accounts that violate these
                                terms or applicable law.
                            </li>
                        </ul>
                    </article>

                    <article className={sectionClassName}>
                        <h2 className="font-['Playfair_Display',serif] text-2xl">
                            Orders, Payments, and Refunds
                        </h2>
                        <p className="mt-3 text-sm leading-7 text-(--welcome-body-text)">
                            Orders are subject to availability and acceptance. Payment methods and
                            supported currencies are shown at checkout. Refunds are handled manually
                            through platform dispute handling; automatic refunds are not offered.
                        </p>
                    </article>

                    <article className={sectionClassName}>
                        <h2 className="font-['Playfair_Display',serif] text-2xl">
                            Intellectual Property
                        </h2>
                        <p className="mt-3 text-sm leading-7 text-(--welcome-body-text)">
                            LoomCraft trademarks, branding, and platform content remain property of
                            LoomCraft or relevant rights holders. Users and vendors must not copy or
                            exploit platform content beyond permitted use.
                        </p>
                    </article>

                    <article className={sectionClassName}>
                        <h2 className="font-['Playfair_Display',serif] text-2xl">
                            Limitation of Liability
                        </h2>
                        <p className="mt-3 text-sm leading-7 text-(--welcome-body-text)">
                            To the extent permitted by law, LoomCraft is not liable for indirect,
                            incidental, or consequential damages resulting from use of the platform.
                            Total liability is limited to amounts directly paid to LoomCraft for the
                            transaction in question.
                        </p>
                    </article>

                    <article className={sectionClassName}>
                        <h2 className="font-['Playfair_Display',serif] text-2xl">
                            Changes to Terms
                        </h2>
                        <p className="mt-3 text-sm leading-7 text-(--welcome-body-text)">
                            LoomCraft may update these terms when required by legal, operational, or
                            product changes. Continued use after updates means you accept the latest
                            version.
                        </p>
                    </article>
                </section>
            </PublicSiteLayout>
        </>
    );
}
