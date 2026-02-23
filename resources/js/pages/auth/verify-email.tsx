// Components
import { Form, Head, Link } from '@inertiajs/react';
import { Spinner } from '@/components/ui/spinner';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

export default function VerifyEmail({ status }: { status?: string }) {
    return (
        <>
            <Head title="Email verification">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <PublicSiteLayout canRegister={false}>
                <section className="relative z-10 mx-auto grid w-full max-w-4xl gap-10 px-6 pb-16 pt-4">
                    <div className="space-y-6">
                        <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                            Email Verification
                        </p>
                        <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                            Verify your email address.
                        </h1>
                        <p className="max-w-2xl text-sm text-(--welcome-body-text) md:text-base">
                            We sent you a verification link. Open it to activate your account.
                        </p>
                    </div>

                    <div className="relative max-w-xl">
                        <div className="relative rounded-[36px] border border-(--welcome-border) bg-(--welcome-surface-1) p-8 shadow-[0_30px_80px_-45px_var(--welcome-shadow)]">
                            {status === 'verification-link-sent' && (
                                <div className="mb-4 rounded-[24px] border border-(--welcome-accent-40) bg-(--welcome-surface-3) px-4 py-3 text-sm text-(--welcome-muted-text)">
                                    A new verification link has been sent to the email address
                                    you provided during registration.
                                </div>
                            )}

                            <Form {...send.form()} className="grid gap-4">
                                {({ processing }) => (
                                    <>
                                        <button
                                            type="submit"
                                            className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-(--welcome-strong) px-6 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-on-strong) transition hover:-translate-y-0.5 hover:bg-(--welcome-strong-hover) disabled:cursor-not-allowed disabled:opacity-70"
                                            disabled={processing}
                                        >
                                            {processing && (
                                                <Spinner className="text-(--welcome-on-strong)" />
                                            )}
                                            Resend verification email
                                        </button>

                                        <Link
                                            href={logout()}
                                            method="post"
                                            as="button"
                                            className="mx-auto text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text) transition hover:text-(--welcome-strong)"
                                        >
                                            Log out
                                        </Link>
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
