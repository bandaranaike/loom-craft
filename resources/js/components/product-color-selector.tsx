import InputError from '@/components/input-error';
import { resolveProductColorSwatch } from '@/lib/product-color-swatches';

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
                                        backgroundColor: resolveProductColorSwatch(color.slug),
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
