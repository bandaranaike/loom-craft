import { Form, Head, Link, usePage } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Spinner } from '@/components/ui/spinner';
import { dashboard, home } from '@/routes';
import { store } from '@/routes/vendor/register';

type Props = {
    status?: string;
};

const inputClassName =
    'w-full rounded-full border border-[#d4b28c] bg-[#fdf8f0] px-4 py-3 text-sm text-[#2b241c] placeholder:text-[#7a5a3a]/70 shadow-[0_8px_20px_-18px_rgba(43,36,28,0.7)] focus:border-[#2b241c] focus:outline-none focus:ring-2 focus:ring-[#2b241c]/20';

export default function VendorRegister() {
    const { status } = usePage<Props>().props;

    return (
        <>
            <Head title="Vendor Registration">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <div className="min-h-screen bg-[#F6F1E8] text-[#2b241c]">
                <div className="relative overflow-hidden">
                    <div className="pointer-events-none absolute -left-40 top-0 h-[420px] w-[420px] rounded-full bg-[radial-gradient(circle_at_top,_#c77b45,_transparent_65%)] opacity-40" />
                    <div className="pointer-events-none absolute -right-32 top-20 h-[360px] w-[360px] rounded-full bg-[radial-gradient(circle,_#a14d2a,_transparent_68%)] opacity-30" />
                    <div className="pointer-events-none absolute bottom-0 left-1/2 h-[320px] w-[720px] -translate-x-1/2 rounded-[100%] bg-[radial-gradient(ellipse_at_center,_#e0c7a7,_transparent_70%)] opacity-60" />

                    <header className="relative z-10 mx-auto flex w-full max-w-6xl items-center justify-between gap-6 px-6 pb-6 pt-8">
                        <Link href={home()} className="flex items-center gap-3">
                            <div className="grid h-12 w-12 place-items-center rounded-full border border-[#2b241c] bg-[#f2e4d4] text-lg font-semibold tracking-[0.08em]">
                                LC
                            </div>
                            <div>
                                <p className="text-sm uppercase tracking-[0.3em] text-[#7a5a3a]">
                                    LoomCraft
                                </p>
                                <p className="font-['Playfair_Display',serif] text-xl">
                                    Woven Heritage House
                                </p>
                            </div>
                        </Link>
                        <nav className="flex items-center gap-3 text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                            <Link
                                href={dashboard()}
                                className="rounded-full border border-[#2b241c] px-4 py-2 font-semibold text-[#2b241c] transition hover:bg-[#2b241c] hover:text-[#f6f1e8]"
                            >
                                Back to Dashboard
                            </Link>
                        </nav>
                    </header>

                    <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-10 px-6 pb-16 pt-4 lg:grid-cols-[1.05fr_0.95fr]">
                        <div className="space-y-6">
                            <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                Vendor Application
                            </p>
                            <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                                Apply to sell heritage pieces on LoomCraft.
                            </h1>
                            <p className="max-w-xl text-sm text-[#5a4a3a] md:text-base">
                                Vendor approval is manual to preserve craft quality and
                                provenance. Share your artisan profile and atelier details
                                for review.
                            </p>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="rounded-[28px] border border-[#e0c7a7] bg-[#fff8ed] p-5">
                                    <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Commission
                                    </p>
                                    <p className="mt-3 text-sm text-[#5a4a3a]">
                                        A fixed 7% commission supports the marketplace and
                                        artisan storytelling.
                                    </p>
                                </div>
                                <div className="rounded-[28px] border border-[#e0c7a7] bg-[#fff8ed] p-5">
                                    <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Approval Process
                                    </p>
                                    <p className="mt-3 text-sm text-[#5a4a3a]">
                                        Every vendor application is reviewed by LoomCraft
                                        curators.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="relative">
                            <div className="absolute -right-4 top-10 h-48 w-48 rounded-[32px] border border-[#d4b28c] bg-[#fdf8f0] shadow-[0_20px_60px_-30px_rgba(43,36,28,0.45)]" />
                            <div className="relative rounded-[36px] border border-[#d4b28c] bg-[#f9efe2] p-8 shadow-[0_30px_80px_-45px_rgba(43,36,28,0.6)]">
                                <div className="space-y-2">
                                    <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Vendor Profile
                                    </p>
                                    <h2 className="font-['Playfair_Display',serif] text-2xl">
                                        Registration details
                                    </h2>
                                    <p className="text-sm text-[#5a4a3a]">
                                        Submit your artisan profile for review.
                                    </p>
                                </div>

                                {status && (
                                    <div className="mt-4 rounded-[24px] border border-[#b6623a]/40 bg-[#fff8ed] px-4 py-3 text-sm text-[#7a5a3a]">
                                        {status}
                                    </div>
                                )}

                                <Form
                                    {...store.form()}
                                    className="mt-6 grid gap-5"
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            <div className="grid gap-2">
                                                <label
                                                    htmlFor="display_name"
                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-[#7a5a3a]"
                                                >
                                                    Display name
                                                </label>
                                                <input
                                                    id="display_name"
                                                    type="text"
                                                    name="display_name"
                                                    placeholder="Studio name"
                                                    className={inputClassName}
                                                    required
                                                />
                                                <InputError
                                                    message={errors.display_name}
                                                    className="text-xs"
                                                />
                                            </div>

                                            <div className="grid gap-2">
                                                <label
                                                    htmlFor="location"
                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-[#7a5a3a]"
                                                >
                                                    Location
                                                </label>
                                                <input
                                                    id="location"
                                                    type="text"
                                                    name="location"
                                                    placeholder="Kandy, Sri Lanka"
                                                    className={inputClassName}
                                                />
                                                <InputError
                                                    message={errors.location}
                                                    className="text-xs"
                                                />
                                            </div>

                                            <div className="grid gap-2">
                                                <label
                                                    htmlFor="bio"
                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-[#7a5a3a]"
                                                >
                                                    Artisan bio
                                                </label>
                                                <textarea
                                                    id="bio"
                                                    name="bio"
                                                    rows={5}
                                                    placeholder="Share your weaving lineage, materials, and atelier story."
                                                    className="w-full rounded-[24px] border border-[#d4b28c] bg-[#fdf8f0] px-4 py-3 text-sm text-[#2b241c] placeholder:text-[#7a5a3a]/70 shadow-[0_8px_20px_-18px_rgba(43,36,28,0.7)] focus:border-[#2b241c] focus:outline-none focus:ring-2 focus:ring-[#2b241c]/20"
                                                />
                                                <InputError
                                                    message={errors.bio}
                                                    className="text-xs"
                                                />
                                            </div>

                                            <button
                                                type="submit"
                                                className="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#2b241c] px-6 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-[#f6f1e8] transition hover:-translate-y-0.5 hover:bg-[#3a2f25] disabled:cursor-not-allowed disabled:opacity-70"
                                                disabled={processing}
                                            >
                                                {processing && (
                                                    <Spinner className="text-[#f6f1e8]" />
                                                )}
                                                Submit for Review
                                            </button>
                                            <p className="text-center text-xs uppercase tracking-[0.25em] text-[#7a5a3a]">
                                                Submissions open to approved patrons only.
                                            </p>
                                        </>
                                    )}
                                </Form>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </>
    );
}
