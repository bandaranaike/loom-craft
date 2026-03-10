import { resolveProductColorSwatch } from '@/lib/product-color-swatches';

type ProductColorItem = {
    id: number;
    name: string;
    slug: string;
};

type ProductColorSwatchesProps = {
    colors: ProductColorItem[];
    sizeClassName?: string;
    className?: string;
};

export default function ProductColorSwatches({
    colors,
    sizeClassName = 'h-6 w-6',
    className = 'mt-2 flex flex-wrap gap-1.5',
}: ProductColorSwatchesProps) {
    if (colors.length === 0) {
        return null;
    }

    return (
        <div className={className}>
            {colors.map((color) => (
                <span
                    key={color.id}
                    title={color.name}
                    className={`block rounded-sm border border-black/10 ${sizeClassName}`}
                    style={{
                        backgroundColor: resolveProductColorSwatch(color.slug),
                    }}
                />
            ))}
        </div>
    );
}
