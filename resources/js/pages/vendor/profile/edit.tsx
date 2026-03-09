import { Form, Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Spinner } from '@/components/ui/spinner';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { dashboard } from '@/routes';
import { update as updateVendorProfile } from '@/routes/vendor/profile';
import { show as vendorShow } from '@/routes/vendors';

type VendorLocation = {
    id: number | null;
    location_name: string;
    address_line_1: string;
    address_line_2: string;
    city: string;
    region: string;
    postal_code: string;
    country: string;
    phone: string;
    hours: string;
    map_url: string;
    is_primary: boolean;
};

type Props = {
    vendor: {
        display_name: string;
        slug: string;
        bio: string | null;
        tagline: string | null;
        website_url: string | null;
        contact_email: string | null;
        contact_phone: string | null;
        whatsapp_number: string | null;
        about_title: string | null;
        craft_specialties: string[];
        years_active: number | null;
        location: string | null;
        status: string;
        is_contact_public: boolean;
        is_website_public: boolean;
        logo_url: string | null;
        cover_image_url: string | null;
        locations: VendorLocation[];
    };
    status?: string;
};

const inputClassName =
    'w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-3 text-sm text-(--welcome-strong) placeholder:text-(--welcome-muted-70) shadow-[0_8px_20px_-18px_var(--welcome-shadow-strong)] focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)';

const textAreaClassName =
    'w-full rounded-[24px] border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-3 text-sm text-(--welcome-strong) placeholder:text-(--welcome-muted-70) shadow-[0_8px_20px_-18px_var(--welcome-shadow-strong)] focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)';

const fileInputClassName =
    'w-full rounded-[24px] border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-3 text-sm text-(--welcome-strong) shadow-[0_8px_20px_-18px_var(--welcome-shadow-strong)] file:mr-4 file:rounded-full file:border-0 file:bg-(--welcome-strong) file:px-4 file:py-2 file:text-xs file:font-semibold file:uppercase file:tracking-[0.3em] file:text-(--welcome-on-strong) hover:file:bg-(--welcome-strong-hover) focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)';

function emptyLocation(): VendorLocation {
    return {
        id: null,
        location_name: '',
        address_line_1: '',
        address_line_2: '',
        city: '',
        region: '',
        postal_code: '',
        country: '',
        phone: '',
        hours: '',
        map_url: '',
        is_primary: false,
    };
}

export default function VendorProfileEdit() {
    const { vendor, status } = usePage<Props>().props;
    const [locations, setLocations] = useState<VendorLocation[]>(
        vendor.locations.length > 0 ? vendor.locations : [emptyLocation()],
    );

    function updateLocation(index: number, key: keyof VendorLocation, value: string | boolean | number | null) {
        setLocations((current) =>
            current.map((location, locationIndex) =>
                locationIndex === index ? { ...location, [key]: value } : location,
            ),
        );
    }

    function markPrimary(index: number) {
        setLocations((current) =>
            current.map((location, locationIndex) => ({
                ...location,
                is_primary: locationIndex === index,
            })),
        );
    }

    function addLocation() {
        setLocations((current) => [...current, emptyLocation()]);
    }

    function removeLocation(index: number) {
        setLocations((current) => {
            const next = current.filter((_, locationIndex) => locationIndex !== index);

            if (next.length === 0) {
                return [emptyLocation()];
            }

            if (! next.some((location) => location.is_primary)) {
                next[0] = { ...next[0], is_primary: true };
            }

            return next;
        });
    }

    return (
        <>
            <Head title="Vendor Profile">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <PublicSiteLayout
                canRegister={false}
                headerActions={
                    <div className="flex gap-2">
                        <Link
                            href={dashboard()}
                            className="rounded-full border border-(--welcome-strong) px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-strong) transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                        >
                            Dashboard
                        </Link>
                        <Link
                            href={vendorShow(vendor.slug)}
                            className="rounded-full bg-(--welcome-strong) px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-on-strong) transition hover:bg-(--welcome-strong-hover)"
                        >
                            View Public Page
                        </Link>
                    </div>
                }
            >
                <section className="relative z-10 mx-auto grid w-full max-w-7xl gap-8 px-6 pb-16 pt-4 lg:grid-cols-[0.8fr_1.2fr]">
                    <div className="space-y-5">
                        <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                            Vendor Studio Profile
                        </p>
                        <h1 className="font-['Playfair_Display',serif] text-3xl leading-tight md:text-4xl">
                            Manage the full public storefront profile.
                        </h1>
                        <p className="max-w-lg text-sm text-(--welcome-body-text)">
                            This form exposes the full vendor profile dataset used by your public
                            vendor page. Update branding, story, contact details, and visibility
                            controls here.
                        </p>
                        <div className="grid gap-3 sm:grid-cols-2">
                            <div className="rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-4">
                                <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                    Public Status
                                </p>
                                <p className="mt-2 text-sm text-(--welcome-body-text)">
                                    {vendor.status}
                                </p>
                            </div>
                            <div className="rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-4">
                                <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                    Slug Preview
                                </p>
                                <p className="mt-2 text-sm text-(--welcome-body-text)">
                                    /vendors/{vendor.slug}
                                </p>
                            </div>
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-1) p-4">
                                <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                    Current Logo
                                </p>
                                {vendor.logo_url ? (
                                    <img
                                        src={vendor.logo_url}
                                        alt="Vendor logo"
                                        className="mt-3 h-36 w-full rounded-[20px] object-cover"
                                    />
                                ) : (
                                    <p className="mt-3 text-sm text-(--welcome-body-text)">No logo uploaded.</p>
                                )}
                            </div>
                            <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-1) p-4">
                                <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                    Current Cover
                                </p>
                                {vendor.cover_image_url ? (
                                    <img
                                        src={vendor.cover_image_url}
                                        alt="Vendor cover"
                                        className="mt-3 h-36 w-full rounded-[20px] object-cover"
                                    />
                                ) : (
                                    <p className="mt-3 text-sm text-(--welcome-body-text)">No cover uploaded.</p>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="relative">
                        <div className="relative rounded-[36px] border border-(--welcome-border) bg-(--welcome-surface-1) p-6 shadow-[0_30px_80px_-45px_var(--welcome-shadow)] lg:p-8">
                            <div className="space-y-2">
                                <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                    Full Vendor Record
                                </p>
                                <h2 className="font-['Playfair_Display',serif] text-2xl">
                                    Storefront profile details
                                </h2>
                                <p className="text-sm text-(--welcome-body-text)">
                                    Every vendor profile field is editable here after the initial
                                    record has been created.
                                </p>
                            </div>

                            {status && (
                                <div className="mt-4 rounded-[24px] border border-(--welcome-accent-40) bg-(--welcome-surface-3) px-4 py-3 text-sm text-(--welcome-muted-text)">
                                    {status}
                                </div>
                            )}

                            <Form
                                {...updateVendorProfile.form()}
                                className="mt-6 grid gap-5 lg:grid-cols-2"
                                encType="multipart/form-data"
                            >
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="display_name"
                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                            >
                                                Display name
                                            </label>
                                            <input
                                                id="display_name"
                                                type="text"
                                                name="display_name"
                                                defaultValue={vendor.display_name}
                                                className={inputClassName}
                                                required
                                            />
                                            <InputError message={errors.display_name} className="text-xs" />
                                        </div>

                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="slug"
                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                            >
                                                Public slug
                                            </label>
                                            <input
                                                id="slug"
                                                type="text"
                                                name="slug"
                                                defaultValue={vendor.slug}
                                                className={inputClassName}
                                                required
                                            />
                                            <InputError message={errors.slug} className="text-xs" />
                                        </div>

                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="tagline"
                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                            >
                                                Tagline
                                            </label>
                                            <input
                                                id="tagline"
                                                type="text"
                                                name="tagline"
                                                defaultValue={vendor.tagline ?? ''}
                                                className={inputClassName}
                                            />
                                            <InputError message={errors.tagline} className="text-xs" />
                                        </div>

                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="about_title"
                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                            >
                                                About title
                                            </label>
                                            <input
                                                id="about_title"
                                                type="text"
                                                name="about_title"
                                                defaultValue={vendor.about_title ?? ''}
                                                className={inputClassName}
                                            />
                                            <InputError message={errors.about_title} className="text-xs" />
                                        </div>

                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="location"
                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                            >
                                                Location
                                            </label>
                                            <input
                                                id="location"
                                                type="text"
                                                name="location"
                                                defaultValue={vendor.location ?? ''}
                                                className={inputClassName}
                                            />
                                            <InputError message={errors.location} className="text-xs" />
                                        </div>

                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="years_active"
                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                            >
                                                Years active
                                            </label>
                                            <input
                                                id="years_active"
                                                type="number"
                                                name="years_active"
                                                min="0"
                                                max="200"
                                                defaultValue={vendor.years_active ?? ''}
                                                className={inputClassName}
                                            />
                                            <InputError message={errors.years_active} className="text-xs" />
                                        </div>

                                        <div className="grid gap-2 lg:col-span-2">
                                            <label
                                                htmlFor="bio"
                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                            >
                                                Bio
                                            </label>
                                            <textarea
                                                id="bio"
                                                name="bio"
                                                rows={6}
                                                defaultValue={vendor.bio ?? ''}
                                                className={textAreaClassName}
                                            />
                                            <InputError message={errors.bio} className="text-xs" />
                                        </div>

                                        <div className="grid gap-2 lg:col-span-2">
                                            <label
                                                htmlFor="craft_specialties"
                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                            >
                                                Craft specialties
                                            </label>
                                            <textarea
                                                id="craft_specialties"
                                                name="craft_specialties"
                                                rows={3}
                                                defaultValue={vendor.craft_specialties.join(', ')}
                                                placeholder="Handloom, Natural Dyes, Cotton Weaving"
                                                className={textAreaClassName}
                                            />
                                            <p className="text-xs text-(--welcome-muted-text)">
                                                Separate specialties with commas or new lines.
                                            </p>
                                            <InputError message={errors.craft_specialties} className="text-xs" />
                                        </div>

                                        <div className="grid gap-4 rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5 lg:col-span-2">
                                            <div className="flex flex-wrap items-center justify-between gap-3">
                                                <div>
                                                    <p className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                                        Store locations
                                                    </p>
                                                    <p className="mt-1 text-sm text-(--welcome-body-text)">
                                                        Manage every location shown on the public vendor page.
                                                    </p>
                                                </div>
                                                <button
                                                    type="button"
                                                    onClick={addLocation}
                                                    className="rounded-full border border-(--welcome-strong) px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-strong) transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                                                >
                                                    Add Location
                                                </button>
                                            </div>
                                            <InputError message={errors.locations} className="text-xs" />
                                            <div className="grid gap-4">
                                                {locations.map((location, index) => (
                                                    <div
                                                        key={location.id ?? `new-location-${index}`}
                                                        className="grid gap-4 rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-1) p-4"
                                                    >
                                                        <input
                                                            type="hidden"
                                                            name={`locations[${index}][id]`}
                                                            value={location.id ?? ''}
                                                        />
                                                        <input
                                                            type="hidden"
                                                            name={`locations[${index}][is_primary]`}
                                                            value={location.is_primary ? '1' : '0'}
                                                        />
                                                        <div className="flex flex-wrap items-center justify-between gap-3">
                                                            <p className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                                                Location {index + 1}
                                                            </p>
                                                            <div className="flex gap-2">
                                                                <button
                                                                    type="button"
                                                                    onClick={() => markPrimary(index)}
                                                                    className={`rounded-full px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.25em] ${
                                                                        location.is_primary
                                                                            ? 'bg-(--welcome-strong) text-(--welcome-on-strong)'
                                                                            : 'border border-(--welcome-border) text-(--welcome-muted-text)'
                                                                    }`}
                                                                >
                                                                    {location.is_primary ? 'Primary' : 'Set Primary'}
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    onClick={() => removeLocation(index)}
                                                                    className="rounded-full border border-(--welcome-border) px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.25em] text-(--welcome-muted-text)"
                                                                >
                                                                    Remove
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div className="grid gap-4 md:grid-cols-2">
                                                            <div className="grid gap-2">
                                                                <label
                                                                    htmlFor={`locations-${index}-location_name`}
                                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                                >
                                                                    Location name
                                                                </label>
                                                                <input
                                                                    id={`locations-${index}-location_name`}
                                                                    type="text"
                                                                    name={`locations[${index}][location_name]`}
                                                                    value={location.location_name}
                                                                    onChange={(event) => updateLocation(index, 'location_name', event.target.value)}
                                                                    className={inputClassName}
                                                                />
                                                                <InputError message={errors[`locations.${index}.location_name`]} className="text-xs" />
                                                            </div>
                                                            <div className="grid gap-2">
                                                                <label
                                                                    htmlFor={`locations-${index}-address_line_1`}
                                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                                >
                                                                    Address line 1
                                                                </label>
                                                                <input
                                                                    id={`locations-${index}-address_line_1`}
                                                                    type="text"
                                                                    name={`locations[${index}][address_line_1]`}
                                                                    value={location.address_line_1}
                                                                    onChange={(event) => updateLocation(index, 'address_line_1', event.target.value)}
                                                                    className={inputClassName}
                                                                />
                                                                <InputError message={errors[`locations.${index}.address_line_1`]} className="text-xs" />
                                                            </div>
                                                            <div className="grid gap-2 md:col-span-2">
                                                                <label
                                                                    htmlFor={`locations-${index}-address_line_2`}
                                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                                >
                                                                    Address line 2
                                                                </label>
                                                                <input
                                                                    id={`locations-${index}-address_line_2`}
                                                                    type="text"
                                                                    name={`locations[${index}][address_line_2]`}
                                                                    value={location.address_line_2}
                                                                    onChange={(event) => updateLocation(index, 'address_line_2', event.target.value)}
                                                                    className={inputClassName}
                                                                />
                                                            </div>
                                                            <div className="grid gap-2">
                                                                <label
                                                                    htmlFor={`locations-${index}-city`}
                                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                                >
                                                                    City
                                                                </label>
                                                                <input
                                                                    id={`locations-${index}-city`}
                                                                    type="text"
                                                                    name={`locations[${index}][city]`}
                                                                    value={location.city}
                                                                    onChange={(event) => updateLocation(index, 'city', event.target.value)}
                                                                    className={inputClassName}
                                                                />
                                                                <InputError message={errors[`locations.${index}.city`]} className="text-xs" />
                                                            </div>
                                                            <div className="grid gap-2">
                                                                <label
                                                                    htmlFor={`locations-${index}-region`}
                                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                                >
                                                                    Region
                                                                </label>
                                                                <input
                                                                    id={`locations-${index}-region`}
                                                                    type="text"
                                                                    name={`locations[${index}][region]`}
                                                                    value={location.region}
                                                                    onChange={(event) => updateLocation(index, 'region', event.target.value)}
                                                                    className={inputClassName}
                                                                />
                                                            </div>
                                                            <div className="grid gap-2">
                                                                <label
                                                                    htmlFor={`locations-${index}-postal_code`}
                                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                                >
                                                                    Postal code
                                                                </label>
                                                                <input
                                                                    id={`locations-${index}-postal_code`}
                                                                    type="text"
                                                                    name={`locations[${index}][postal_code]`}
                                                                    value={location.postal_code}
                                                                    onChange={(event) => updateLocation(index, 'postal_code', event.target.value)}
                                                                    className={inputClassName}
                                                                />
                                                            </div>
                                                            <div className="grid gap-2">
                                                                <label
                                                                    htmlFor={`locations-${index}-country`}
                                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                                >
                                                                    Country
                                                                </label>
                                                                <input
                                                                    id={`locations-${index}-country`}
                                                                    type="text"
                                                                    name={`locations[${index}][country]`}
                                                                    value={location.country}
                                                                    onChange={(event) => updateLocation(index, 'country', event.target.value)}
                                                                    className={inputClassName}
                                                                />
                                                                <InputError message={errors[`locations.${index}.country`]} className="text-xs" />
                                                            </div>
                                                            <div className="grid gap-2">
                                                                <label
                                                                    htmlFor={`locations-${index}-phone`}
                                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                                >
                                                                    Phone
                                                                </label>
                                                                <input
                                                                    id={`locations-${index}-phone`}
                                                                    type="text"
                                                                    name={`locations[${index}][phone]`}
                                                                    value={location.phone}
                                                                    onChange={(event) => updateLocation(index, 'phone', event.target.value)}
                                                                    className={inputClassName}
                                                                />
                                                            </div>
                                                            <div className="grid gap-2">
                                                                <label
                                                                    htmlFor={`locations-${index}-hours`}
                                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                                >
                                                                    Opening hours
                                                                </label>
                                                                <input
                                                                    id={`locations-${index}-hours`}
                                                                    type="text"
                                                                    name={`locations[${index}][hours]`}
                                                                    value={location.hours}
                                                                    onChange={(event) => updateLocation(index, 'hours', event.target.value)}
                                                                    className={inputClassName}
                                                                />
                                                            </div>
                                                            <div className="grid gap-2 md:col-span-2">
                                                                <label
                                                                    htmlFor={`locations-${index}-map_url`}
                                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                                >
                                                                    Google Maps URL
                                                                </label>
                                                                <input
                                                                    id={`locations-${index}-map_url`}
                                                                    type="url"
                                                                    name={`locations[${index}][map_url]`}
                                                                    value={location.map_url}
                                                                    onChange={(event) => updateLocation(index, 'map_url', event.target.value)}
                                                                    className={inputClassName}
                                                                />
                                                                <InputError message={errors[`locations.${index}.map_url`]} className="text-xs" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>

                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="website_url"
                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                            >
                                                Website URL
                                            </label>
                                            <input
                                                id="website_url"
                                                type="url"
                                                name="website_url"
                                                defaultValue={vendor.website_url ?? ''}
                                                className={inputClassName}
                                            />
                                            <InputError message={errors.website_url} className="text-xs" />
                                        </div>

                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="contact_email"
                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                            >
                                                Contact email
                                            </label>
                                            <input
                                                id="contact_email"
                                                type="email"
                                                name="contact_email"
                                                defaultValue={vendor.contact_email ?? ''}
                                                className={inputClassName}
                                            />
                                            <InputError message={errors.contact_email} className="text-xs" />
                                        </div>

                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="contact_phone"
                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                            >
                                                Contact phone
                                            </label>
                                            <input
                                                id="contact_phone"
                                                type="text"
                                                name="contact_phone"
                                                defaultValue={vendor.contact_phone ?? ''}
                                                className={inputClassName}
                                            />
                                            <InputError message={errors.contact_phone} className="text-xs" />
                                        </div>

                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="whatsapp_number"
                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                            >
                                                WhatsApp number
                                            </label>
                                            <input
                                                id="whatsapp_number"
                                                type="text"
                                                name="whatsapp_number"
                                                defaultValue={vendor.whatsapp_number ?? ''}
                                                className={inputClassName}
                                            />
                                            <InputError message={errors.whatsapp_number} className="text-xs" />
                                        </div>

                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="logo"
                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                            >
                                                Logo image
                                            </label>
                                            <input
                                                id="logo"
                                                type="file"
                                                name="logo"
                                                accept=".jpg,.jpeg,.png,.webp,.gif"
                                                className={fileInputClassName}
                                            />
                                            <InputError message={errors.logo} className="text-xs" />
                                        </div>

                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="cover_image"
                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                            >
                                                Cover image
                                            </label>
                                            <input
                                                id="cover_image"
                                                type="file"
                                                name="cover_image"
                                                accept=".jpg,.jpeg,.png,.webp,.gif"
                                                className={fileInputClassName}
                                            />
                                            <InputError message={errors.cover_image} className="text-xs" />
                                        </div>

                                        <div className="grid gap-4 rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5 lg:col-span-2">
                                            <p className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                                Public visibility
                                            </p>
                                            <label className="flex items-start gap-3 text-sm text-(--welcome-body-text)">
                                                <input type="hidden" name="is_contact_public" value="0" />
                                                <input
                                                    type="checkbox"
                                                    name="is_contact_public"
                                                    value="1"
                                                    defaultChecked={vendor.is_contact_public}
                                                    className="mt-1 h-4 w-4 rounded border-(--welcome-border)"
                                                />
                                                <span>Show contact email, phone, and WhatsApp on the public vendor page.</span>
                                            </label>
                                            <label className="flex items-start gap-3 text-sm text-(--welcome-body-text)">
                                                <input type="hidden" name="is_website_public" value="0" />
                                                <input
                                                    type="checkbox"
                                                    name="is_website_public"
                                                    value="1"
                                                    defaultChecked={vendor.is_website_public}
                                                    className="mt-1 h-4 w-4 rounded border-(--welcome-border)"
                                                />
                                                <span>Show the website URL on the public vendor page.</span>
                                            </label>
                                        </div>

                                        <button
                                            type="submit"
                                            className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-(--welcome-strong) px-6 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-on-strong) transition hover:-translate-y-0.5 hover:bg-(--welcome-strong-hover) disabled:cursor-not-allowed disabled:opacity-70 lg:col-span-2"
                                            disabled={processing}
                                        >
                                            {processing && <Spinner className="text-(--welcome-on-strong)" />}
                                            Save Vendor Profile
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
