import { Form } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Spinner } from '@/components/ui/spinner';
import { store as storeVendorInquiry } from '@/routes/vendors/inquiries';

type VendorInquiryFormProps = {
    vendorSlug: string;
    contactEmail?: string | null;
    contactPhone?: string | null;
    whatsappNumber?: string | null;
    status?: string | null;
    className?: string;
};

export default function VendorInquiryForm({
    vendorSlug,
    contactEmail = null,
    contactPhone = null,
    whatsappNumber = null,
    status = null,
    className = 'mt-5 grid gap-4',
}: VendorInquiryFormProps) {
    return (
        <>
            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                Contact Vendor
            </p>
            <h2 className="mt-4 font-['Playfair_Display',serif] text-3xl">
                Send an inquiry
            </h2>

            {(contactEmail || contactPhone || whatsappNumber) && (
                <div className="mt-3 space-y-1 text-sm text-(--welcome-body-text)">
                    {contactEmail && <p>Email: {contactEmail}</p>}
                    {contactPhone && <p>Phone: {contactPhone}</p>}
                    {whatsappNumber && <p>WhatsApp: {whatsappNumber}</p>}
                </div>
            )}

            {status && (
                <div className="mt-4 rounded-[18px] border border-(--welcome-accent-40) bg-(--welcome-surface-3) px-4 py-3 text-sm text-(--welcome-muted-text)">
                    {status}
                </div>
            )}

            <Form {...storeVendorInquiry.form(vendorSlug)} className={className} disableWhileProcessing>
                {({ errors, processing }) => (
                    <>
                        <div>
                            <input
                                name="name"
                                placeholder="Your name"
                                className="w-full rounded-xl border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-3 text-sm text-(--welcome-strong) focus:border-(--welcome-strong) focus:outline-none"
                            />
                            <InputError message={errors.name} className="mt-1 text-xs" />
                        </div>
                        <div>
                            <input
                                type="email"
                                name="email"
                                placeholder="Email address"
                                className="w-full rounded-xl border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-3 text-sm text-(--welcome-strong) focus:border-(--welcome-strong) focus:outline-none"
                            />
                            <InputError message={errors.email} className="mt-1 text-xs" />
                        </div>
                        <div>
                            <input
                                name="phone"
                                placeholder="Phone number (optional)"
                                className="w-full rounded-xl border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-3 text-sm text-(--welcome-strong) focus:border-(--welcome-strong) focus:outline-none"
                            />
                            <InputError message={errors.phone} className="mt-1 text-xs" />
                        </div>
                        <div>
                            <input
                                name="subject"
                                placeholder="Subject"
                                className="w-full rounded-xl border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-3 text-sm text-(--welcome-strong) focus:border-(--welcome-strong) focus:outline-none"
                            />
                            <InputError message={errors.subject} className="mt-1 text-xs" />
                        </div>
                        <div>
                            <textarea
                                name="message"
                                rows={5}
                                placeholder="Write your inquiry"
                                className="w-full rounded-xl border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-3 text-sm text-(--welcome-strong) focus:border-(--welcome-strong) focus:outline-none"
                            />
                            <InputError message={errors.message} className="mt-1 text-xs" />
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="inline-flex items-center justify-center gap-2 rounded-full bg-(--welcome-strong) px-6 py-3 text-sm font-semibold tracking-[0.2em] text-(--welcome-on-strong) uppercase transition hover:-translate-y-0.5 hover:bg-(--welcome-strong-hover) disabled:cursor-not-allowed disabled:opacity-70"
                        >
                            {processing && <Spinner className="text-(--welcome-on-strong)" />}
                            Send Inquiry
                        </button>
                    </>
                )}
            </Form>
        </>
    );
}
