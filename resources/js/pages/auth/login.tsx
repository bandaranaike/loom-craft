import { Form, Head, Link } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

type Props = {
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
};

const inputClassName =
    'w-full rounded-full border border-[#d4b28c] bg-[#fdf8f0] px-4 py-3 text-sm text-[#2b241c] placeholder:text-[#7a5a3a]/70 shadow-[0_8px_20px_-18px_rgba(43,36,28,0.7)] focus:border-[#2b241c] focus:outline-none focus:ring-2 focus:ring-[#2b241c]/20';

export default function Login({
    status,
    canResetPassword,
    canRegister,
}: Props) {
    return (
        <>
            <Head title="Log in">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <PublicSiteLayout
                canRegister={canRegister}
                headerActions={
                    canRegister ? (
                        <Link
                            href={register()}
                            className="rounded-full border border-[#2b241c] px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-[#2b241c] transition hover:bg-[#2b241c] hover:text-[#f6f1e8]"
                        >
                            Become a Patron
                        </Link>
                    ) : undefined
                }
            >
                    <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-10 px-6 pb-16 pt-4 lg:grid-cols-[1.05fr_0.95fr]">
                        <div className="space-y-6">
                            <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                Welcome Back
                            </p>
                            <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                                Return to the LoomCraft atelier.
                            </h1>
                            <p className="max-w-xl text-sm text-[#5a4a3a] md:text-base">
                                Sign in to manage your collections, review artisan releases, and
                                follow the provenance of every textile in your care.
                            </p>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="rounded-[28px] border border-[#e0c7a7] bg-[#fff8ed] p-5">
                                    <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Atelier Notes
                                    </p>
                                    <p className="mt-3 text-sm text-[#5a4a3a]">
                                        Guest checkout remains available, with curated guidance
                                        when you return.
                                    </p>
                                </div>
                                <div className="rounded-[28px] border border-[#e0c7a7] bg-[#fff8ed] p-5">
                                    <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Secure Access
                                    </p>
                                    <p className="mt-3 text-sm text-[#5a4a3a]">
                                        Two-factor challenges protect the artisan network you
                                        support.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="relative">
                            <div className="absolute -right-4 top-10 h-48 w-48 rounded-[32px] border border-[#d4b28c] bg-[#fdf8f0] shadow-[0_20px_60px_-30px_rgba(43,36,28,0.45)]" />
                            <div className="relative rounded-[36px] border border-[#d4b28c] bg-[#f9efe2] p-8 shadow-[0_30px_80px_-45px_rgba(43,36,28,0.6)]">
                                <div className="space-y-2">
                                    <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Patron Access
                                    </p>
                                    <h2 className="font-['Playfair_Display',serif] text-2xl">
                                        Log in to your account
                                    </h2>
                                    <p className="text-sm text-[#5a4a3a]">
                                        Enter your credentials to continue.
                                    </p>
                                </div>

                                {status && (
                                    <div className="mt-4 rounded-[24px] border border-[#b6623a]/40 bg-[#fff8ed] px-4 py-3 text-sm text-[#7a5a3a]">
                                        {status}
                                    </div>
                                )}

                                <Form
                                    {...store.form()}
                                    resetOnSuccess={['password']}
                                    className="mt-6 grid gap-5"
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            <div className="grid gap-2">
                                                <label
                                                    htmlFor="email"
                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-[#7a5a3a]"
                                                >
                                                    Email address
                                                </label>
                                                <input
                                                    id="email"
                                                    type="email"
                                                    name="email"
                                                    required
                                                    autoFocus
                                                    tabIndex={1}
                                                    autoComplete="email"
                                                    placeholder="email@example.com"
                                                    className={inputClassName}
                                                />
                                                <InputError
                                                    message={errors.email}
                                                    className="text-xs"
                                                />
                                            </div>

                                            <div className="grid gap-2">
                                                <div className="flex flex-wrap items-center justify-between gap-2">
                                                    <label
                                                        htmlFor="password"
                                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-[#7a5a3a]"
                                                    >
                                                        Password
                                                    </label>
                                                    {canResetPassword && (
                                                        <Link
                                                            href={request()}
                                                            className="text-xs uppercase tracking-[0.25em] text-[#7a5a3a] transition hover:text-[#2b241c]"
                                                            tabIndex={5}
                                                        >
                                                            Forgot password?
                                                        </Link>
                                                    )}
                                                </div>
                                                <input
                                                    id="password"
                                                    type="password"
                                                    name="password"
                                                    required
                                                    tabIndex={2}
                                                    autoComplete="current-password"
                                                    placeholder="Password"
                                                    className={inputClassName}
                                                />
                                                <InputError
                                                    message={errors.password}
                                                    className="text-xs"
                                                />
                                            </div>

                                            <label className="flex items-center gap-3 text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                                <input
                                                    id="remember"
                                                    name="remember"
                                                    type="checkbox"
                                                    tabIndex={3}
                                                    className="h-4 w-4 rounded border border-[#b98a5f] bg-[#fdf8f0] text-[#2b241c] focus:ring-2 focus:ring-[#2b241c]/20"
                                                />
                                                Remember me
                                            </label>

                                            <button
                                                type="submit"
                                                className="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#2b241c] px-6 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-[#f6f1e8] transition hover:-translate-y-0.5 hover:bg-[#3a2f25] disabled:cursor-not-allowed disabled:opacity-70"
                                                tabIndex={4}
                                                disabled={processing}
                                                data-test="login-button"
                                            >
                                                {processing && (
                                                    <Spinner className="text-[#f6f1e8]" />
                                                )}
                                                Log in
                                            </button>
                                        </>
                                    )}
                                </Form>

                                {canRegister && (
                                    <div className="mt-6 text-center text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Don&apos;t have an account?{' '}
                                        <Link
                                            href={register()}
                                            className="font-semibold text-[#2b241c]"
                                            tabIndex={6}
                                        >
                                            Sign up
                                        </Link>
                                    </div>
                                )}
                            </div>
                        </div>
                    </section>
            </PublicSiteLayout>
        </>
    );
}
