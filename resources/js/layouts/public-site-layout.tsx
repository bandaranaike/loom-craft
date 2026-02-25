import { Link, usePage } from '@inertiajs/react';
import { Moon, Sun } from 'lucide-react';
import type { CSSProperties, PropsWithChildren, ReactNode } from 'react';
import AppLogoIcon from '@/components/app-logo-icon';
import LegalLinks from '@/components/legal-links';
import { useAppearance } from '@/hooks/use-appearance';
import dumbaraPatterns from '@/images/dumbara-patterns.png';
import { dashboard, home, login, loomWeaveDemo, register } from '@/routes';
import { show as cartShow } from '@/routes/cart';
import { show as checkoutShow } from '@/routes/checkout';
import { index as productsIndex } from '@/routes/products';
import type { SharedData } from '@/types';

type PublicSiteLayoutProps = PropsWithChildren<{
    canRegister?: boolean;
    showBrowseProducts?: boolean;
    headerActions?: ReactNode;
}>;

const menuItemClass =
    'rounded-full border border-transparent px-4 py-2 font-medium text-(--welcome-strong-70) transition hover:border-(--welcome-strong) hover:text-(--welcome-strong)';

export default function PublicSiteLayout({
    children,
    canRegister = true,
    showBrowseProducts = true,
    headerActions,
}: PublicSiteLayoutProps) {
    const { auth } = usePage<SharedData>().props;
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const isDark = resolvedAppearance === 'dark';
    const leftSidePatternStyle: CSSProperties = {
        backgroundImage: `linear-gradient(to right, var(--welcome-on-strong-95), var(--welcome-on-strong-45), transparent), url(${dumbaraPatterns}), url(${dumbaraPatterns})`,
        backgroundSize: '100% 100%, 170px auto, 140px auto',
        backgroundRepeat: 'no-repeat, repeat-y, repeat-y',
        backgroundPosition: 'left center, 0 0, 84px 120px',
    };
    const rightSidePatternStyle: CSSProperties = {
        backgroundImage: `linear-gradient(to left, var(--welcome-on-strong-95), var(--welcome-on-strong-45), transparent), url(${dumbaraPatterns}), url(${dumbaraPatterns})`,
        backgroundSize: '100% 100%, 170px auto, 140px auto',
        backgroundRepeat: 'no-repeat, repeat-y, repeat-y',
        backgroundPosition: 'right center, 100% 0, calc(100% - 84px) 120px',
    };

    return (
        <div className="min-h-screen bg-(--welcome-on-strong) text-(--welcome-strong)">
            <div className="relative overflow-hidden">
                <div className="pointer-events-none absolute bottom-0 left-1/2 h-80 w-180 -translate-x-1/2 rounded-[100%] bg-[radial-gradient(ellipse_at_center,var(--welcome-border-soft),transparent_70%)] opacity-60" />
                <div
                    className="pointer-events-none absolute inset-y-0 left-0 w-34 mask-[linear-gradient(to_right,black,transparent)] opacity-10 mix-blend-multiply"
                    style={leftSidePatternStyle}
                />
                <div
                    className="pointer-events-none absolute inset-y-0 right-0 w-34 mask-[linear-gradient(to_left,black,transparent)] opacity-10 mix-blend-multiply"
                    style={rightSidePatternStyle}
                />

                <header className="relative z-10 mx-auto flex w-full max-w-6xl items-center justify-between gap-6 px-6 pt-8 pb-6">
                    <Link href={home()} className="flex items-center gap-3">
                        <AppLogoIcon className="h-24 w-auto object-contain" />
                    </Link>
                    <nav className="flex flex-wrap items-center gap-3 text-sm">
                        {headerActions ? (
                            headerActions
                        ) : (
                            <>
                                {showBrowseProducts && (
                                    <Link
                                        href={productsIndex()}
                                        className={menuItemClass}
                                    >
                                        Browse Products
                                    </Link>
                                )}
                                <Link
                                    href={loomWeaveDemo()}
                                    className={menuItemClass}
                                >
                                    Design Studio
                                </Link>
                                {auth.user ? (
                                    <Link
                                        href={dashboard()}
                                        className="rounded-full border border-(--welcome-strong) px-4 py-2 font-medium transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                                    >
                                        Enter Atelier
                                    </Link>
                                ) : (
                                    <>
                                        <Link
                                            href={login()}
                                            className={menuItemClass}
                                        >
                                            Log in
                                        </Link>
                                        {canRegister && (
                                            <Link
                                                href={register()}
                                                className="rounded-full border border-(--welcome-strong) px-4 py-2 font-medium transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                                            >
                                                Become a Patron
                                            </Link>
                                        )}
                                    </>
                                )}
                            </>
                        )}
                        <button
                            type="button"
                            onClick={() => updateAppearance(isDark ? 'light' : 'dark')}
                            className="inline-flex h-9 w-9 items-center justify-center rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) text-(--welcome-muted-text) transition hover:bg-(--welcome-surface-1) hover:text-(--welcome-strong)"
                            aria-label={isDark ? 'Switch to light theme' : 'Switch to dark theme'}
                            title={isDark ? 'Switch to light theme' : 'Switch to dark theme'}
                        >
                            {isDark ? <Sun className="h-4 w-4" /> : <Moon className="h-4 w-4" />}
                        </button>
                    </nav>
                </header>

                <main className="relative z-10">{children}</main>

                <footer className="relative z-10 border-t border-(--welcome-border-soft) bg-(--welcome-surface-1)">
                    <div className="mx-auto grid w-full max-w-6xl gap-8 px-6 py-10 md:grid-cols-3">
                        <div>
                            <p className="font-['Playfair_Display',serif] text-2xl">
                                LoomCraft
                            </p>
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                Heritage Marketplace
                            </p>
                        </div>
                        <div>
                            <div className="mt-3 flex flex-wrap gap-3 text-xs tracking-[0.25em] text-(--welcome-muted-text) uppercase">
                                <Link
                                    href={home()}
                                    className="hover:text-(--welcome-strong)"
                                >
                                    Home
                                </Link>
                                <Link
                                    href={productsIndex()}
                                    className="hover:text-(--welcome-strong)"
                                >
                                    Products
                                </Link>
                            </div>
                        </div>
                        <div>
                            <LegalLinks
                                className="mt-3 flex flex-wrap gap-3 text-xs tracking-[0.25em] text-(--welcome-muted-text) uppercase"
                                linkClassName="hover:text-(--welcome-strong)"
                            />
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    );
}
