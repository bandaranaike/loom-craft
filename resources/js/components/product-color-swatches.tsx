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

const colorSwatchMap: Record<string, string> = {
    red: '#dc2626',
    yellow: '#eab308',
    blue: '#2563eb',
    orange: '#ea580c',
    green: '#16a34a',
    purple: '#7c3aed',
    'yellow-orange': '#f59e0b',
    'red-orange': '#f97316',
    'red-purple': '#c026d3',
    'blue-purple': '#6366f1',
    'blue-green': '#0f766e',
    'yellow-green': '#65a30d',
    black: '#111827',
    white: '#ffffff',
    beige: '#d6c2a1',
    brown: '#8b5e3c',
    pink: '#ec4899',
    teal: '#0d9488',
    amber: '#f59e0b',
};

const resolveColorSwatch = (slug: string): string => {
    return colorSwatchMap[slug] ?? '#9ca3af';
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
                        backgroundColor: resolveColorSwatch(color.slug),
                    }}
                />
            ))}
        </div>
    );
}
