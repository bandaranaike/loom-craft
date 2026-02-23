import { Form, Head } from '@inertiajs/react';
import { REGEXP_ONLY_DIGITS } from 'input-otp';
import { useMemo, useState } from 'react';
import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { OTP_MAX_LENGTH } from '@/hooks/use-two-factor-auth';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { store } from '@/routes/two-factor/login';

export default function TwoFactorChallenge() {
    const [showRecoveryInput, setShowRecoveryInput] = useState<boolean>(false);
    const [code, setCode] = useState<string>('');

    const authConfigContent = useMemo<{
        title: string;
        description: string;
        toggleText: string;
    }>(() => {
        if (showRecoveryInput) {
            return {
                title: 'Recovery Code',
                description:
                    'Please confirm access to your account by entering one of your emergency recovery codes.',
                toggleText: 'login using an authentication code',
            };
        }

        return {
            title: 'Authentication Code',
            description:
                'Enter the authentication code provided by your authenticator application.',
            toggleText: 'login using a recovery code',
        };
    }, [showRecoveryInput]);

    const toggleRecoveryMode = (clearErrors: () => void): void => {
        setShowRecoveryInput(!showRecoveryInput);
        clearErrors();
        setCode('');
    };

    return (
        <>
            <Head title="Two-Factor Authentication">
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
                            Account Security
                        </p>
                        <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                            {authConfigContent.title}
                        </h1>
                        <p className="max-w-2xl text-sm text-(--welcome-body-text) md:text-base">
                            {authConfigContent.description}
                        </p>
                    </div>

                    <div className="relative max-w-xl">
                        <div className="relative rounded-[36px] border border-(--welcome-border) bg-(--welcome-surface-1) p-8 shadow-[0_30px_80px_-45px_var(--welcome-shadow)]">
                            <Form
                                {...store.form()}
                                className="grid gap-4"
                                resetOnError
                                resetOnSuccess={!showRecoveryInput}
                            >
                                {({ errors, processing, clearErrors }) => (
                                    <>
                                        {showRecoveryInput ? (
                                            <div className="grid gap-2">
                                                <label
                                                    htmlFor="recovery_code"
                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                >
                                                    Recovery code
                                                </label>
                                                <Input
                                                    id="recovery_code"
                                                    name="recovery_code"
                                                    type="text"
                                                    placeholder="Enter recovery code"
                                                    autoFocus={showRecoveryInput}
                                                    required
                                                    className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-3 text-sm text-(--welcome-strong) placeholder:text-(--welcome-muted-70) shadow-[0_8px_20px_-18px_var(--welcome-shadow-strong)] focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)"
                                                />
                                                <InputError
                                                    message={errors.recovery_code}
                                                    className="text-xs"
                                                />
                                            </div>
                                        ) : (
                                            <div className="flex flex-col items-center justify-center gap-3 text-center">
                                                <div className="flex w-full items-center justify-center">
                                                    <InputOTP
                                                        name="code"
                                                        maxLength={OTP_MAX_LENGTH}
                                                        value={code}
                                                        onChange={(value) =>
                                                            setCode(value)
                                                        }
                                                        disabled={processing}
                                                        pattern={REGEXP_ONLY_DIGITS}
                                                    >
                                                        <InputOTPGroup>
                                                            {Array.from(
                                                                {
                                                                    length: OTP_MAX_LENGTH,
                                                                },
                                                                (_, index) => (
                                                                    <InputOTPSlot
                                                                        key={
                                                                            index
                                                                        }
                                                                        index={
                                                                            index
                                                                        }
                                                                    />
                                                                ),
                                                            )}
                                                        </InputOTPGroup>
                                                    </InputOTP>
                                                </div>
                                                <InputError
                                                    message={errors.code}
                                                    className="text-xs"
                                                />
                                            </div>
                                        )}

                                        <button
                                            type="submit"
                                            className="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-full bg-(--welcome-strong) px-6 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-on-strong) transition hover:-translate-y-0.5 hover:bg-(--welcome-strong-hover) disabled:cursor-not-allowed disabled:opacity-70"
                                            disabled={processing}
                                        >
                                            Continue
                                        </button>

                                        <div className="text-center text-xs uppercase tracking-[0.24em] text-(--welcome-muted-text)">
                                            Or{' '}
                                            <button
                                                type="button"
                                                className="cursor-pointer font-semibold text-(--welcome-strong)"
                                                onClick={() =>
                                                    toggleRecoveryMode(clearErrors)
                                                }
                                            >
                                                {authConfigContent.toggleText}
                                            </button>
                                        </div>
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
