import { Head } from '@inertiajs/react';
import PublicSiteLayout from '@/layouts/public-site-layout';

const sectionClassName =
    'rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6 md:p-8';

export default function PrivacyPolicy({ canRegister = true }: { canRegister?: boolean }) {
    return (
        <>
            <Head title="Privacy Policy — LoomCraft">
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
                            Privacy Policy
                        </h1>
                        <p className="text-sm text-(--welcome-body-text)">
                            Effective date: February 25, 2026
                        </p>
                    </div>
                </section>

                <section className="mx-auto w-full max-w-4xl space-y-5 px-6 pb-20">
                    <article className={sectionClassName}>
                        <h2 className="font-['Playfair_Display',serif] text-2xl">Overview</h2>
                        <p className="mt-3 text-sm leading-7 text-(--welcome-body-text)">
                            LoomCraft collects and uses personal information to run the marketplace,
                            process orders, support vendors, and improve user experience. By using
                            the platform, you acknowledge this policy.
                        </p>
                    </article>

                    <article className={sectionClassName}>
                        <h2 className="font-['Playfair_Display',serif] text-2xl">
                            Information We Collect
                        </h2>
                        <ul className="mt-3 list-disc space-y-2 pl-5 text-sm leading-7 text-(--welcome-body-text)">
                            <li>Account information such as name, email, and login credentials.</li>
                            <li>
                                Order and payment details required to complete transactions and keep
                                accounting records.
                            </li>
                            <li>
                                Product, cart, and support interactions submitted through the
                                platform.
                            </li>
                            <li>
                                Technical data such as IP address, browser type, and session data
                                for security and analytics.
                            </li>
                        </ul>
                    </article>

                    <article className={sectionClassName}>
                        <h2 className="font-['Playfair_Display',serif] text-2xl">
                            How We Use Information
                        </h2>
                        <ul className="mt-3 list-disc space-y-2 pl-5 text-sm leading-7 text-(--welcome-body-text)">
                            <li>To provide marketplace functions, order flow, and account access.</li>
                            <li>To detect fraud, abuse, and unauthorized activity.</li>
                            <li>To fulfill legal and financial record-keeping obligations.</li>
                            <li>To communicate updates, transactional notices, and support replies.</li>
                        </ul>
                    </article>

                    <article className={sectionClassName}>
                        <h2 className="font-['Playfair_Display',serif] text-2xl">
                            Sharing of Information
                        </h2>
                        <p className="mt-3 text-sm leading-7 text-(--welcome-body-text)">
                            LoomCraft shares data only as needed for operations, including payment
                            processors, delivery-related service providers, and legal compliance.
                            We do not sell personal information.
                        </p>
                    </article>

                    <article className={sectionClassName}>
                        <h2 className="font-['Playfair_Display',serif] text-2xl">
                            Data Retention and Security
                        </h2>
                        <p className="mt-3 text-sm leading-7 text-(--welcome-body-text)">
                            We retain information for as long as required to provide services,
                            resolve disputes, and comply with legal obligations. We use reasonable
                            administrative and technical safeguards, but no method of transmission
                            or storage is completely secure.
                        </p>
                    </article>

                    <article className={sectionClassName}>
                        <h2 className="font-['Playfair_Display',serif] text-2xl">Your Rights</h2>
                        <p className="mt-3 text-sm leading-7 text-(--welcome-body-text)">
                            You may request access, correction, or deletion of personal data,
                            subject to applicable law and platform obligations. For privacy requests,
                            please contact the LoomCraft support channel listed in your account or
                            storefront contact details.
                        </p>
                    </article>
                </section>
            </PublicSiteLayout>
        </>
    );
}
