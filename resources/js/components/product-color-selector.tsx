import InputError from '@/components/input-error';

type ProductColorOption = {
    id: number;
    name: string;
    slug: string;
};

type ProductColorSelectorProps = {
    colors: ProductColorOption[];
    selectedColorIds?: number[];
    errorMessage?: string;
    itemErrorMessage?: string;
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

export default function ProductColorSelector({
    colors,
    selectedColorIds = [],
    errorMessage,
    itemErrorMessage,
}: ProductColorSelectorProps) {
    return (
        <div className="grid gap-2">
            <p className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                Colors
            </p>
            <div className="grid gap-1 rounded-[20px] border border-(--welcome-border) bg-(--welcome-surface-2) p-3">
                {colors.length === 0 ? (
                    <p className="text-xs text-(--welcome-muted-text)">
                        No active colors are available yet.
                    </p>
                ) : (
                    <div className="flex flex-wrap gap-2">
                        {colors.map((color) => (
                            <label
                                key={color.id}
                                title={color.name}
                                className="relative grid h-8 w-8 cursor-pointer place-items-center rounded-md border border-(--welcome-border) bg-(--welcome-surface-1) p-1"
                            >
                                <input
                                    type="checkbox"
                                    name="color_ids[]"
                                    value={color.id}
                                    defaultChecked={selectedColorIds.includes(color.id)}
                                    className="peer sr-only"
                                />
                                <span
                                    className="block h-5 w-5 rounded-sm border border-black/10 peer-checked:ring-2 peer-checked:ring-(--welcome-strong)"
                                    style={{
                                        backgroundColor: resolveColorSwatch(color.slug),
                                    }}
                                />
                                <span className="pointer-events-none absolute inset-0 grid place-items-center text-xs font-bold text-white opacity-0 mix-blend-difference peer-checked:opacity-100">
                                    ✓
                                </span>
                            </label>
                        ))}
                    </div>
                )}
            </div>
            <InputError message={errorMessage} className="text-xs" />
            <InputError message={itemErrorMessage} className="text-xs" />
        </div>
    );
}
