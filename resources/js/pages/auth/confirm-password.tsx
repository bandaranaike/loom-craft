import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Spinner } from '@/components/ui/spinner';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { store } from '@/routes/password/confirm';

const inputClassName =
    'w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-3 text-sm text-(--welcome-strong) placeholder:text-(--welcome-muted-70) shadow-[0_8px_20px_-18px_var(--welcome-shadow-strong)] focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)';

export default function ConfirmPassword() {
    return (
        <>
            <Head title="Confirm password">
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
                            Secure Area
                        </p>
                        <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                            Confirm your password.
                        </h1>
                        <p className="max-w-2xl text-sm text-(--welcome-body-text) md:text-base">
                            Please re-enter your password before continuing.
                        </p>
                    </div>

                    <div className="relative max-w-xl">
                        <div className="relative rounded-[36px] border border-(--welcome-border) bg-(--welcome-surface-1) p-8 shadow-[0_30px_80px_-45px_var(--welcome-shadow)]">
                            <Form
                                {...store.form()}
                                resetOnSuccess={['password']}
                                className="grid gap-5"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="password"
                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                            >
                                                Password
                                            </label>
                                            <input
                                                id="password"
                                                type="password"
                                                name="password"
                                                placeholder="Password"
                                                autoComplete="current-password"
                                                autoFocus
                                                className={inputClassName}
                                            />
                                            <InputError
                                                message={errors.password}
                                                className="text-xs"
                                            />
                                        </div>

                                        <button
                                            type="submit"
                                            className="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-full bg-(--welcome-strong) px-6 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-on-strong) transition hover:-translate-y-0.5 hover:bg-(--welcome-strong-hover) disabled:cursor-not-allowed disabled:opacity-70"
                                            disabled={processing}
                                            data-test="confirm-password-button"
                                        >
                                            {processing && (
                                                <Spinner className="text-(--welcome-on-strong)" />
                                            )}
                                            Confirm password
                                        </button>
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
