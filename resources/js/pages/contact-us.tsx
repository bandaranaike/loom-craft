import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import ContactController from '@/actions/App/Http/Controllers/ContactController';
import InputError from '@/components/input-error';
import SeoHead from '@/components/seo-head';
import { Spinner } from '@/components/ui/spinner';
import PublicSiteLayout from '@/layouts/public-site-layout';

type Props = {
    status?: string;
    formDefaults: {
        name: string;
        email: string;
        phone: string;
        message: string;
    };
};

const inputClassName =
    'w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-3 text-sm text-(--welcome-strong) placeholder:text-(--welcome-muted-70) shadow-[0_8px_20px_-18px_var(--welcome-shadow-strong)] focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)';

const textAreaClassName =
    'min-h-40 w-full rounded-[28px] border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-4 text-sm text-(--welcome-strong) placeholder:text-(--welcome-muted-70) shadow-[0_8px_20px_-18px_var(--welcome-shadow-strong)] focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)';

export default function ContactUs({ status, formDefaults }: Props) {
    const form = useForm({
        name: formDefaults.name,
        email: formDefaults.email,
        phone: formDefaults.phone,
        message: formDefaults.message,
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.post(ContactController.store.url(), {
            preserveScroll: true,
            onSuccess: () => {
                form.setData('message', '');
            },
        });
    };

    return (
        <>
            <SeoHead
                title="Contact Us — LoomCraft"
                description="Contact LoomCraft for order help, artisan partnerships, support questions, and general inquiries."
                canonical="/contact-us"
            >
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </SeoHead>

            <PublicSiteLayout>
                <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-10 px-6 pt-4 pb-16 lg:grid-cols-[1.05fr_0.95fr]">
                    <div className="space-y-6">
                        <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">Contact LoomCraft</p>
                        <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                            Send a note to the LoomCraft team.
                        </h1>
                        <p className="max-w-xl text-sm text-(--welcome-body-text) md:text-base">
                            Reach out about orders, artisan partnerships, support questions, or anything else you need.
                            We will reply using your email when available, and you may also leave a phone number for
                            follow-up.
                        </p>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5">
                                <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">Response Flow</p>
                                <p className="mt-3 text-sm text-(--welcome-body-text)">
                                    Messages are reviewed by LoomCraft admins and tracked through a managed status queue.
                                </p>
                            </div>
                            <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5">
                                <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">Direct Contact</p>
                                <p className="mt-3 text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">Call or WhatsApp</p>
                                <p className="mt-2 font-['Playfair_Display',serif] text-3xl text-(--welcome-strong)">
                                    +94 712 512 512
                                </p>
                                <p className="mt-3 text-sm text-(--welcome-body-text)">
                                    Name and message are required in the form, and at least one of email or phone must
                                    be provided.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="relative">
                        <div className="relative rounded-[36px] border border-(--welcome-border) bg-(--welcome-surface-1) p-8 shadow-[0_30px_80px_-45px_var(--welcome-shadow)]">
                            <div className="space-y-2">
                                <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">Open A Conversation</p>
                                <h2 className="font-['Playfair_Display',serif] text-2xl">Tell us how we can help</h2>
                                <p className="text-sm text-(--welcome-body-text)">
                                    Logged-in details are prefilled when available, but you can edit them before sending.
                                </p>
                            </div>

                            {status && (
                                <div className="mt-4 rounded-[24px] border border-(--welcome-accent-40) bg-(--welcome-surface-3) px-4 py-3 text-sm text-(--welcome-muted-text)">
                                    {status}
                                </div>
                            )}

                            <form onSubmit={submit} className="mt-6 grid gap-5">
                                <div className="grid gap-2">
                                    <label htmlFor="name" className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Name
                                    </label>
                                    <input
                                        id="name"
                                        name="name"
                                        required
                                        autoFocus
                                        value={form.data.name}
                                        onChange={(event) => form.setData('name', event.target.value)}
                                        placeholder="Your name"
                                        className={inputClassName}
                                    />
                                    <InputError message={form.errors.name} className="text-xs" />
                                </div>

                                <div className="grid gap-5 sm:grid-cols-2">
                                    <div className="grid gap-2">
                                        <label htmlFor="email" className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                            Email address
                                        </label>
                                        <input
                                            id="email"
                                            type="email"
                                            name="email"
                                            value={form.data.email}
                                            onChange={(event) => form.setData('email', event.target.value)}
                                            placeholder="email@example.com"
                                            className={inputClassName}
                                        />
                                        <InputError message={form.errors.email} className="text-xs" />
                                    </div>

                                    <div className="grid gap-2">
                                        <label htmlFor="phone" className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                            Phone number
                                        </label>
                                        <input
                                            id="phone"
                                            type="text"
                                            name="phone"
                                            value={form.data.phone}
                                            onChange={(event) => form.setData('phone', event.target.value)}
                                            placeholder="+94 77 123 4567"
                                            className={inputClassName}
                                        />
                                        <InputError message={form.errors.phone} className="text-xs" />
                                    </div>
                                </div>

                                <div className="grid gap-2">
                                    <label htmlFor="message" className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Message
                                    </label>
                                    <textarea
                                        id="message"
                                        name="message"
                                        required
                                        value={form.data.message}
                                        onChange={(event) => form.setData('message', event.target.value)}
                                        placeholder="Share the details of your request."
                                        className={textAreaClassName}
                                    />
                                    <InputError message={form.errors.message} className="text-xs" />
                                </div>

                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-full bg-(--welcome-strong) px-6 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-on-strong) transition hover:-translate-y-0.5 hover:bg-(--welcome-strong-hover) disabled:cursor-not-allowed disabled:opacity-70"
                                >
                                    {form.processing && <Spinner className="text-(--welcome-on-strong)" />}
                                    Send message
                                </button>
                            </form>
                        </div>
                    </div>
                </section>
            </PublicSiteLayout>
        </>
    );
}
