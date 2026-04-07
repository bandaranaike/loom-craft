import { Form, Head, Link } from '@inertiajs/react';
import { Eye, EyeOff } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Spinner } from '@/components/ui/spinner';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { login } from '@/routes';
import { store } from '@/routes/register';

const inputClassName =
    'w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-3 text-sm text-(--welcome-strong) placeholder:text-(--welcome-muted-70) shadow-[0_8px_20px_-18px_var(--welcome-shadow-strong)] focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)';

export default function Register() {
    const [passwordVisible, setPasswordVisible] = useState(false);
    const [passwordConfirmationVisible, setPasswordConfirmationVisible] = useState(false);

    return (
        <>
            <Head title="Register">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600" rel="stylesheet" />
            </Head>
            <PublicSiteLayout canRegister={false}>
                <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-10 px-6 pt-4 pb-16 lg:grid-cols-[1.05fr_0.95fr]">
                    <div className="space-y-6">
                        <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Become a Patron</p>
                        <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">Join the LoomCraft collector circle.</h1>
                        <p className="max-w-xl text-sm text-(--welcome-body-text) md:text-base">
                            Create your account to reserve limited releases, follow artisan stories, and access a curated marketplace dedicated to heritage weaving.
                        </p>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5">
                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Vendor Approval</p>
                                <p className="mt-3 text-sm text-(--welcome-body-text)">Artisan sellers are reviewed manually to protect the heritage network.</p>
                            </div>
                            <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5">
                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Transparent Pricing</p>
                                <p className="mt-3 text-sm text-(--welcome-body-text)">Listings are reviewed and presented with clear final pricing for collectors.</p>
                            </div>
                        </div>
                    </div>

                    <div className="relative">
                        <div className="relative rounded-[36px] border border-(--welcome-border) bg-(--welcome-surface-1) p-8 shadow-[0_30px_80px_-45px_var(--welcome-shadow)]">
                            <div className="space-y-2">
                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Register</p>
                                <h2 className="font-['Playfair_Display',serif] text-2xl">Create an account</h2>
                                <p className="text-sm text-(--welcome-body-text)">Enter your details below to begin.</p>
                            </div>

                            <Form {...store.form()} resetOnSuccess={['password', 'password_confirmation']} disableWhileProcessing className="mt-6 grid gap-5">
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <label htmlFor="name" className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                Name
                                            </label>
                                            <input
                                                id="name"
                                                type="text"
                                                required
                                                autoFocus
                                                tabIndex={1}
                                                autoComplete="name"
                                                name="name"
                                                placeholder="Full name"
                                                className={inputClassName}
                                            />
                                            <InputError message={errors.name} className="text-xs" />
                                        </div>

                                        <div className="grid gap-2">
                                            <label htmlFor="email" className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                Email address
                                            </label>
                                            <input
                                                id="email"
                                                type="email"
                                                required
                                                tabIndex={2}
                                                autoComplete="email"
                                                name="email"
                                                placeholder="email@example.com"
                                                className={inputClassName}
                                            />
                                            <InputError message={errors.email} className="text-xs" />
                                        </div>

                                        <div className="grid gap-2">
                                            <label htmlFor="password" className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                Password
                                            </label>
                                            <div className="relative">
                                                <input
                                                    id="password"
                                                    type={passwordVisible ? 'text' : 'password'}
                                                    required
                                                    tabIndex={3}
                                                    autoComplete="new-password"
                                                    name="password"
                                                    placeholder="Password"
                                                    className={`${inputClassName} pr-12`}
                                                />
                                                <button
                                                    type="button"
                                                    onClick={() => setPasswordVisible((value) => !value)}
                                                    className="absolute top-1/2 right-4 inline-flex -translate-y-1/2 items-center justify-center rounded-full p-1 text-(--welcome-muted-text) transition hover:text-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                                    aria-label={passwordVisible ? 'Hide password' : 'Show password'}
                                                    aria-pressed={passwordVisible}
                                                    tabIndex={4}
                                                >
                                                    {passwordVisible ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                                </button>
                                            </div>
                                            <InputError message={errors.password} className="text-xs" />
                                        </div>

                                        <div className="grid gap-2">
                                            <label htmlFor="password_confirmation" className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                Confirm password
                                            </label>
                                            <div className="relative">
                                                <input
                                                    id="password_confirmation"
                                                    type={passwordConfirmationVisible ? 'text' : 'password'}
                                                    required
                                                    tabIndex={5}
                                                    autoComplete="new-password"
                                                    name="password_confirmation"
                                                    placeholder="Confirm password"
                                                    className={`${inputClassName} pr-12`}
                                                />
                                                <button
                                                    type="button"
                                                    onClick={() => setPasswordConfirmationVisible((value) => !value)}
                                                    className="absolute top-1/2 right-4 inline-flex -translate-y-1/2 items-center justify-center rounded-full p-1 text-(--welcome-muted-text) transition hover:text-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                                    aria-label={passwordConfirmationVisible ? 'Hide password confirmation' : 'Show password confirmation'}
                                                    aria-pressed={passwordConfirmationVisible}
                                                    tabIndex={6}
                                                >
                                                    {passwordConfirmationVisible ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                                </button>
                                            </div>
                                            <InputError message={errors.password_confirmation} className="text-xs" />
                                        </div>

                                        <button
                                            type="submit"
                                            className="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-full bg-(--welcome-strong) px-6 py-3 text-xs font-semibold tracking-[0.3em] text-(--welcome-on-strong) uppercase transition hover:-translate-y-0.5 hover:bg-(--welcome-strong-hover) disabled:cursor-not-allowed disabled:opacity-70"
                                            tabIndex={7}
                                            data-test="register-user-button"
                                        >
                                            {processing && <Spinner className="text-(--welcome-on-strong)" />}
                                            Create account
                                        </button>
                                    </>
                                )}
                            </Form>

                            <div className="mt-6 text-center text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                Already have an account?{' '}
                                <Link href={login()} className="font-semibold text-(--welcome-strong)" tabIndex={8}>
                                    Log in
                                </Link>
                            </div>
                        </div>
                    </div>
                </section>
            </PublicSiteLayout>
        </>
    );
}
