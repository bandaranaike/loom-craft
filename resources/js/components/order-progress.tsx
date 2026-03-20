type OrderProgressStep = {
    key: string;
    label: string;
    state: 'complete' | 'current' | 'upcoming';
};

type OrderProgressData = {
    is_cancelled: boolean;
    summary: {
        title: string;
        description: string;
    } | null;
    steps: OrderProgressStep[];
};

type OrderProgressProps = {
    progress: OrderProgressData | null;
};

const stepClasses = (state: OrderProgressStep['state']) => {
    if (state === 'complete') {
        return 'border-emerald-600 bg-emerald-600 text-white';
    }

    if (state === 'current') {
        return 'border-(--welcome-strong) bg-(--welcome-surface-1) text-(--welcome-strong)';
    }

    return 'border-(--welcome-border) bg-transparent text-(--welcome-muted-text)';
};

export default function OrderProgress({ progress }: OrderProgressProps) {
    if (!progress) {
        return null;
    }

    if (progress.is_cancelled && progress.summary) {
        return (
            <div className="rounded-[28px] border border-rose-200 bg-rose-50 p-6 text-rose-900">
                <p className="text-xs uppercase tracking-[0.3em] text-rose-700">
                    Tracking update
                </p>
                <h2 className="mt-3 font-['Playfair_Display',serif] text-3xl">
                    {progress.summary.title}
                </h2>
                <p className="mt-2 max-w-2xl text-sm text-rose-800">
                    {progress.summary.description}
                </p>
            </div>
        );
    }

    return (
        <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                Order progress
            </p>
            <div className="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                {progress.steps.map((step, index) => (
                    <div key={step.key} className="flex min-w-0 items-start gap-3 xl:flex-col xl:gap-4">
                        <div className="flex items-center xl:w-full">
                            <div
                                className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-full border text-xs font-semibold uppercase tracking-[0.2em] ${stepClasses(step.state)}`}
                            >
                                {index + 1}
                            </div>
                            {index < progress.steps.length - 1 && (
                                <div className="ml-3 h-px min-w-0 flex-1 bg-(--welcome-border) xl:ml-0 xl:block xl:h-px" />
                            )}
                        </div>
                        <div className="min-w-0">
                            <p className="text-sm font-semibold text-(--welcome-strong)">
                                {step.label}
                            </p>
                            <p className="mt-1 text-xs uppercase tracking-[0.25em] text-(--welcome-muted-text)">
                                {step.state}
                            </p>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}
