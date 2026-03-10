import { Link } from '@inertiajs/react';
import ProductColorSwatches from '@/components/product-color-swatches';
import { formatMoney } from '@/lib/currency';
import { show as productShow } from '@/routes/products';
import { show as vendorShow } from '@/routes/vendors';

export type ProductCardItem = {
    id: number;
    slug: string;
    name: string;
    description?: string | null;
    original_price: string;
    selling_price: string;
    effective_discount_percentage: string;
    has_discount: boolean;
    vendor_name: string;
    vendor_slug: string | null;
    vendor_location?: string | null;
    image_url: string | null;
    categories?: Array<{
        id: number;
        name: string;
        slug: string;
    }>;
    colors: Array<{
        id: number;
        name: string;
        slug: string;
    }>;
};

type ProductCardProps = {
    product: ProductCardItem;
};

export default function ProductCard({
    product,
}: ProductCardProps) {
    return (
        <article className="group flex h-full flex-col overflow-hidden rounded-4xl border border-(--welcome-border-soft) bg-(--welcome-surface-3) transition hover:-translate-y-1 hover:border-(--welcome-accent)">
            <Link
                href={productShow(product.slug)}
                className="relative aspect-4/3 overflow-hidden bg-(--welcome-surface-1)"
            >
                {product.has_discount && (
                    <span className="absolute top-3 right-3 z-10 rounded-full bg-(--welcome-strong) px-3 py-1 text-[10px] font-semibold tracking-[0.3em] text-(--welcome-on-strong) uppercase">
                        {product.effective_discount_percentage}% Off
                    </span>
                )}
                {product.image_url ? (
                    <img
                        src={product.image_url}
                        alt={product.name}
                        className="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                    />
                ) : (
                    <div className="flex h-full w-full items-center justify-center text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                        Image forthcoming
                    </div>
                )}
            </Link>
            <div className="flex flex-1 flex-col gap-3 p-5">
                <div className="space-y-2">
                    <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                        {product.vendor_slug ? (
                            <Link href={vendorShow(product.vendor_slug)}>
                                {product.vendor_name}
                            </Link>
                        ) : (
                            product.vendor_name
                        )}
                        {product.vendor_location
                            ? ` • ${product.vendor_location}`
                            : ''}
                    </p>
                    <h3 className="font-['Playfair_Display',serif] text-xl">
                        <Link href={productShow(product.slug)}>
                            {product.name}
                        </Link>
                    </h3>
                    {product.categories && product.categories.length > 0 && (
                        <div className="flex flex-wrap gap-2">
                            {product.categories.map((category) => (
                                <span
                                    key={category.id}
                                    className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-2.5 py-1 text-[10px] tracking-[0.2em] text-(--welcome-muted-text) uppercase"
                                >
                                    {category.name}
                                </span>
                            ))}
                        </div>
                    )}
                    <ProductColorSwatches
                        colors={product.colors}
                        className="mt-2 flex flex-wrap gap-1.5"
                        sizeClassName="h-5 w-5"
                    />
                </div>
                <div className="mt-auto flex items-end justify-between gap-3 text-sm">
                    <span className="text-(--welcome-body-text)">
                        Selling price
                    </span>
                    <div className="text-right">
                        <p className="font-semibold text-(--welcome-strong)">
                            {formatMoney(product.selling_price, 'LKR')}
                        </p>
                        {product.has_discount && (
                            <p className="text-xs text-(--welcome-muted-text) line-through decoration-(--welcome-muted-text) decoration-1">
                                {formatMoney(product.original_price, 'LKR')}
                            </p>
                        )}
                    </div>
                </div>
            </div>
        </article>
    );
}
