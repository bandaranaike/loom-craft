import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Spinner } from '@/components/ui/spinner';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { update } from '@/routes/password';

type Props = {
    token: string;
    email: string;
};

const inputClassName =
    'w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-3 text-sm text-(--welcome-strong) placeholder:text-(--welcome-muted-70) shadow-[0_8px_20px_-18px_var(--welcome-shadow-strong)] focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)';

export default function ResetPassword({ token, email }: Props) {
    return (
        <>
            <Head title="Reset password">
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
                            Password Update
                        </p>
                        <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                            Set your new password.
                        </h1>
                        <p className="max-w-2xl text-sm text-(--welcome-body-text) md:text-base">
                            Choose a strong password to continue protecting your account.
                        </p>
                    </div>

                    <div className="relative max-w-xl">
                        <div className="relative rounded-[36px] border border-(--welcome-border) bg-(--welcome-surface-1) p-8 shadow-[0_30px_80px_-45px_var(--welcome-shadow)]">
                            <Form
                                {...update.form()}
                                transform={(data) => ({ ...data, token, email })}
                                resetOnSuccess={['password', 'password_confirmation']}
                                className="grid gap-5"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="email"
                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                            >
                                                Email address
                                            </label>
                                            <input
                                                id="email"
                                                type="email"
                                                name="email"
                                                autoComplete="email"
                                                value={email}
                                                className={`${inputClassName} cursor-not-allowed opacity-75`}
                                                readOnly
                                            />
                                            <InputError
                                                message={errors.email}
                                                className="text-xs"
                                            />
                                        </div>

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
                                                autoComplete="new-password"
                                                autoFocus
                                                placeholder="Password"
                                                className={inputClassName}
                                            />
                                            <InputError
                                                message={errors.password}
                                                className="text-xs"
                                            />
                                        </div>

                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="password_confirmation"
                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                            >
                                                Confirm password
                                            </label>
                                            <input
                                                id="password_confirmation"
                                                type="password"
                                                name="password_confirmation"
                                                autoComplete="new-password"
                                                placeholder="Confirm password"
                                                className={inputClassName}
                                            />
                                            <InputError
                                                message={errors.password_confirmation}
                                                className="text-xs"
                                            />
                                        </div>

                                        <button
                                            type="submit"
                                            className="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-full bg-(--welcome-strong) px-6 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-on-strong) transition hover:-translate-y-0.5 hover:bg-(--welcome-strong-hover) disabled:cursor-not-allowed disabled:opacity-70"
                                            disabled={processing}
                                            data-test="reset-password-button"
                                        >
                                            {processing && (
                                                <Spinner className="text-(--welcome-on-strong)" />
                                            )}
                                            Reset password
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
