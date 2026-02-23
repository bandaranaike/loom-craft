import {
    addGridLayer,
    createDesign,
    createPalette,
    exportSVG,
    inflateRLE,
    setCell,
    type BaseColor,
} from '@erbitron/loom-weave-kit';
import { exportPNG } from '@erbitron/loom-weave-kit-export-canvas';
import { useCompile, useDesignHistory } from '@erbitron/loom-weave-kit-react';
import { Head } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import PublicSiteLayout from '@/layouts/public-site-layout';

const baseColors: BaseColor[] = [
    { id: 1, name: 'Black', hex: '#111111' },
    { id: 2, name: 'Gold', hex: '#C9A227' },
    { id: 3, name: 'Cream', hex: '#F6EEDC' },
];

export default function LoomWeaveDemoPage() {
    const palette = useMemo(() => createPalette(baseColors), []);
    const initialDesign = useMemo(() => {
        let design = createDesign({
            productType: 'WALL_HANGER',
            fabricId: 'COTTON',
            widthCells: 32,
            heightCells: 24,
        });
        design = addGridLayer(design, { id: 'grid-main' });

        return design;
    }, []);

    const history = useDesignHistory(initialDesign);
    const { compiled, constraints } = useCompile(history.present, palette, {
        maxRunsPerRow: 28,
    });
    const [activeColor, setActiveColor] = useState<number>(1);
    const colorById = useMemo(
        () => new Map(palette.base.map((color) => [color.id, color.hex])),
        [palette],
    );
    const compiledGrid = useMemo(
        () =>
            inflateRLE(
                compiled.rleRows,
                compiled.widthCells,
                compiled.heightCells,
            ),
        [compiled],
    );

    const svg = useMemo(
        () => exportSVG(compiled, palette, { cellSizePx: 12, showGrid: true }),
        [compiled, palette],
    );

    const paintCell = (x: number, y: number): void => {
        const next = setCell(history.present, 'grid-main', x, y, activeColor);
        history.commit(next);
    };

    const handleExportPng = async (): Promise<void> => {
        const output = await exportPNG(compiled, palette, {
            pixelSize: 8,
            output: 'blob',
        });
        const blob =
            output instanceof Blob
                ? output
                : new Blob([Uint8Array.from(output)], { type: 'image/png' });
        const url = URL.createObjectURL(blob);
        const anchor = document.createElement('a');
        anchor.href = url;
        anchor.download = `loom-preview-${Date.now()}.png`;
        anchor.click();
        URL.revokeObjectURL(url);
    };

    return (
        <>
            <Head title="Build your own woven — LoomCraft">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <PublicSiteLayout>
                <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-8 px-6 pt-6 pb-10 lg:grid-cols-[1.1fr_0.9fr]">
                    <div className="space-y-4">
                        <div className="inline-flex items-center gap-3 rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-2 text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                            Design Studio
                        </div>
                        <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                            Build your own woven
                        </h1>
                        <p className="max-w-xl text-sm text-(--welcome-body-text) md:text-base">
                            Paint your pattern, compile instantly, and export a
                            production preview PNG from the woven grid.
                        </p>
                    </div>
                    <div className="rounded-[36px] border border-(--welcome-border) bg-(--welcome-surface-1) p-6 shadow-[0_30px_80px_-45px_var(--welcome-shadow-medium)]">
                        <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                            Palette
                        </p>
                        <div className="mt-4 flex flex-wrap gap-3">
                            {palette.base.map((color) => (
                                <button
                                    key={color.id}
                                    onClick={() => setActiveColor(color.id)}
                                    className={`inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-semibold tracking-[0.2em] uppercase transition ${
                                        activeColor === color.id
                                            ? 'border-(--welcome-strong) bg-(--welcome-strong) text-(--welcome-on-strong)'
                                            : 'border-(--welcome-border) bg-(--welcome-surface-3) text-(--welcome-strong) hover:border-(--welcome-strong)'
                                    }`}
                                >
                                    <span
                                        className="h-4 w-4 rounded-full border border-(--welcome-border-quiet)"
                                        style={{ backgroundColor: color.hex }}
                                    />
                                    {color.name}
                                </button>
                            ))}
                        </div>
                        <div className="mt-6 flex flex-wrap gap-3">
                            <button
                                onClick={history.undo}
                                disabled={!history.canUndo}
                                className="rounded-full border border-(--welcome-strong) px-4 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase transition enabled:hover:bg-(--welcome-strong) enabled:hover:text-(--welcome-on-strong) disabled:cursor-not-allowed disabled:border-(--welcome-border) disabled:text-(--welcome-muted-60)"
                            >
                                Undo
                            </button>
                            <button
                                onClick={history.redo}
                                disabled={!history.canRedo}
                                className="rounded-full border border-(--welcome-strong) px-4 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase transition enabled:hover:bg-(--welcome-strong) enabled:hover:text-(--welcome-on-strong) disabled:cursor-not-allowed disabled:border-(--welcome-border) disabled:text-(--welcome-muted-60)"
                            >
                                Redo
                            </button>
                            <button
                                onClick={handleExportPng}
                                className="rounded-full border border-(--welcome-strong) bg-(--welcome-strong) px-4 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-on-strong) uppercase transition hover:bg-(--welcome-strong-hover)"
                            >
                                Export PNG
                            </button>
                        </div>
                    </div>
                </section>

                <section className="mx-auto grid w-full max-w-6xl gap-6 px-6 pb-10 lg:grid-cols-2">
                    <div className="rounded-4xl border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5">
                        <h3 className="font-['Playfair_Display',serif] text-2xl">
                            Editor Grid
                        </h3>
                        <p className="mt-2 text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                            Click cells to paint
                        </p>
                        <div className="mt-4 overflow-x-auto rounded-[20px] border border-(--welcome-border) bg-(--welcome-surface-1) p-3">
                            <div
                                className="grid gap-px bg-(--welcome-border) p-px"
                                style={{
                                    gridTemplateColumns: `repeat(${history.present.widthCells}, minmax(18px, 18px))`,
                                    width: 'fit-content',
                                }}
                            >
                                {Array.from({
                                    length: history.present.heightCells,
                                }).flatMap((_, y) =>
                                    Array.from({
                                        length: history.present.widthCells,
                                    }).map((__, x) => (
                                        <button
                                            key={`${x}-${y}`}
                                            onClick={() => paintCell(x, y)}
                                            className="h-4.5 w-4.5 border-none p-0"
                                            style={{
                                                backgroundColor:
                                                    colorById.get(
                                                        compiledGrid[
                                                            y *
                                                                compiled.widthCells +
                                                                x
                                                        ],
                                                    ) ?? '#fff',
                                            }}
                                            title={`(${x}, ${y})`}
                                        />
                                    )),
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="rounded-4xl border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5">
                        <h3 className="font-['Playfair_Display',serif] text-2xl">
                            Compiled SVG Preview
                        </h3>
                        <p className="mt-2 text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                            Real-time weave output
                        </p>
                        <div className="mt-4 overflow-x-auto rounded-[20px] border border-(--welcome-border) bg-(--welcome-surface-1) p-4">
                            <div dangerouslySetInnerHTML={{ __html: svg }} />
                        </div>
                    </div>
                </section>

                <section className="mx-auto w-full max-w-6xl px-6 pb-16">
                    <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
                        <h3 className="font-['Playfair_Display',serif] text-2xl">
                            Constraint Issues ({constraints.issues.length})
                        </h3>
                        <ul className="mt-4 space-y-2 text-sm text-(--welcome-body-text)">
                            {constraints.issues.map((issue, index) => (
                                <li
                                    key={`${issue.id}-${index}`}
                                    className="rounded-2xl border border-(--welcome-border-soft) bg-(--welcome-surface-1) px-4 py-3"
                                >
                                    <strong className="mr-2 text-(--welcome-strong)">
                                        {issue.severity.toUpperCase()}:
                                    </strong>
                                    {issue.message}
                                </li>
                            ))}
                            {constraints.issues.length === 0 ? (
                                <li className="rounded-2xl border border-(--welcome-border-soft) bg-(--welcome-surface-1) px-4 py-3 text-(--welcome-strong)">
                                    No issues.
                                </li>
                            ) : null}
                        </ul>
                    </div>
                </section>
            </PublicSiteLayout>
        </>
    );
}
