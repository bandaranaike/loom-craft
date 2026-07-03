import { Head } from '@inertiajs/react';
import type { PropsWithChildren, ReactNode } from 'react';

type SeoHeadProps = PropsWithChildren<{
    title: string;
    description?: string;
    canonical?: string;
    image?: string | null;
    noIndex?: boolean;
    schema?: Record<string, unknown> | Record<string, unknown>[] | null;
}>;

export default function SeoHead({
    title,
    description,
    canonical,
    image,
    noIndex = false,
    schema = null,
    children,
}: SeoHeadProps): ReactNode {
    return (
        <Head title={title}>
            {description && (
                <meta
                    head-key="description"
                    name="description"
                    content={description}
                />
            )}
            {canonical && (
                <link head-key="canonical" rel="canonical" href={canonical} />
            )}
            {noIndex && (
                <meta
                    head-key="robots"
                    name="robots"
                    content="noindex, nofollow"
                />
            )}
            <meta head-key="og:type" property="og:type" content="website" />
            <meta head-key="og:title" property="og:title" content={title} />
            {description && (
                <meta
                    head-key="og:description"
                    property="og:description"
                    content={description}
                />
            )}
            {canonical && (
                <meta
                    head-key="og:url"
                    property="og:url"
                    content={canonical}
                />
            )}
            {image && (
                <meta head-key="og:image" property="og:image" content={image} />
            )}
            <meta
                head-key="twitter:card"
                name="twitter:card"
                content={image ? 'summary_large_image' : 'summary'}
            />
            <meta
                head-key="twitter:title"
                name="twitter:title"
                content={title}
            />
            {description && (
                <meta
                    head-key="twitter:description"
                    name="twitter:description"
                    content={description}
                />
            )}
            {image && (
                <meta
                    head-key="twitter:image"
                    name="twitter:image"
                    content={image}
                />
            )}
            {schema && (
                <script
                    head-key="structured-data"
                    type="application/ld+json"
                    dangerouslySetInnerHTML={{
                        __html: JSON.stringify(schema),
                    }}
                />
            )}
            {children}
        </Head>
    );
}
