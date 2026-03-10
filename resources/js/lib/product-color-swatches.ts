import productColorPalette from '../../data/product-colors.json';

type ProductColorDefinition = {
    name: string;
    slug: string;
    hex: string;
};

export const productColorDefinitions = productColorPalette as ProductColorDefinition[];

export const productColorSwatchMap: Record<string, string> = Object.fromEntries(
    productColorDefinitions.map(({ slug, hex }) => [slug, hex]),
);

export function resolveProductColorSwatch(slug: string): string {
    return productColorSwatchMap[slug] ?? '#9ca3af';
}
