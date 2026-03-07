import { Link, usePage } from '@inertiajs/react';
import { Menu, Moon, Sun, X } from 'lucide-react';
import { useState } from 'react';
import type { CSSProperties, MouseEventHandler, PropsWithChildren, ReactNode } from 'react';
import AppLogoIcon from '@/components/app-logo-icon';
import LegalLinks from '@/components/legal-links';
import { useAppearance } from '@/hooks/use-appearance';
import dumbaraPatterns from '@/images/dumbara-patterns.png';
import { dashboard, home, login, loomWeaveDemo, register } from '@/routes';
import { index as productsIndex } from '@/routes/products';
import type { SharedData } from '@/types';

type PublicSiteLayoutProps = PropsWithChildren<{
    canRegister?: boolean;
    showBrowseProducts?: boolean;
    headerActions?: ReactNode;
}>;

const menuItemClass =
    'rounded-full border border-transparent px-4 py-2 font-medium text-(--welcome-strong-70) transition hover:border-(--welcome-strong) hover:text-(--welcome-strong)';
const mobileMenuItemClass =
    'block w-full border-b border-(--welcome-border-soft) px-1 py-3 text-left text-sm font-semibold tracking-[0.18em] text-(--welcome-muted-text) uppercase transition hover:text-(--welcome-strong)';
const actionButtonClass =
    'rounded-full border border-(--welcome-strong) px-4 py-2 font-medium transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)';
const mobileActionButtonClass =
    'block w-full border-b border-(--welcome-border-soft) px-1 py-3 text-left text-sm font-semibold tracking-[0.18em] text-(--welcome-strong) uppercase transition hover:text-(--welcome-accent)';
const iconButtonClass =
    'inline-flex h-9 w-9 items-center justify-center rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) text-(--welcome-muted-text) transition hover:bg-(--welcome-surface-1) hover:text-(--welcome-strong)';

export default function PublicSiteLayout({
    children,
    canRegister = true,
    showBrowseProducts = true,
    headerActions,
}: PublicSiteLayoutProps) {
    const { auth } = usePage<SharedData>().props;
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
    const isDark = resolvedAppearance === 'dark';
    const toggleAppearanceLabel = isDark
        ? 'Switch to light theme'
        : 'Switch to dark theme';
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
    const handleMenuItemClick: MouseEventHandler = () => {
        setIsMobileMenuOpen(false);
    };

    const renderMenuActions = (isMobile: boolean = false): ReactNode => {
        if (headerActions) {
            return headerActions;
        }

        return (
            <>
                {showBrowseProducts && (
                    <Link
                        href={productsIndex()}
                        className={isMobile ? mobileMenuItemClass : menuItemClass}
                        onClick={isMobile ? handleMenuItemClick : undefined}
                    >
                        Browse Products
                    </Link>
                )}
                {auth.user ? (
                    <Link
                        href={dashboard()}
                        className={isMobile ? mobileActionButtonClass : actionButtonClass}
                        onClick={isMobile ? handleMenuItemClick : undefined}
                    >
                        Enter Atelier
                    </Link>
                ) : (
                    <>
                        <Link
                            href={login()}
                            className={isMobile ? mobileMenuItemClass : menuItemClass}
                            onClick={isMobile ? handleMenuItemClick : undefined}
                        >
                            Log in
                        </Link>
                        {canRegister && (
                            <Link
                                href={register()}
                                className={isMobile ? mobileActionButtonClass : actionButtonClass}
                                onClick={isMobile ? handleMenuItemClick : undefined}
                            >
                                Become a Patron
                            </Link>
                        )}
                    </>
                )}
            </>
        );
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

                <div className="relative z-20">
                    <header className="relative z-10 mx-auto flex w-full max-w-6xl items-center justify-between gap-6 px-6 pt-8 pb-6">
                        <Link href={home()} className="flex items-center gap-3">
                            <AppLogoIcon className="h-24 w-auto object-contain" />
                        </Link>
                        <div className="flex items-center gap-3">
                            <nav className="hidden flex-wrap items-center gap-3 text-sm md:flex">
                                {renderMenuActions()}
                                <button
                                    type="button"
                                    onClick={() => updateAppearance(isDark ? 'light' : 'dark')}
                                    className={iconButtonClass}
                                    aria-label={toggleAppearanceLabel}
                                    title={toggleAppearanceLabel}
                                >
                                    {isDark ? <Sun className="h-4 w-4" /> : <Moon className="h-4 w-4" />}
                                </button>
                            </nav>
                            <button
                                type="button"
                                onClick={() => setIsMobileMenuOpen((value) => !value)}
                                className="inline-flex h-9 w-9 items-center justify-center rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) text-(--welcome-muted-text) transition hover:bg-(--welcome-surface-1) hover:text-(--welcome-strong) md:hidden"
                                aria-label={isMobileMenuOpen ? 'Close menu' : 'Open menu'}
                                aria-expanded={isMobileMenuOpen}
                                aria-controls="public-site-mobile-menu"
                            >
                                {isMobileMenuOpen ? <X className="h-4 w-4" /> : <Menu className="h-4 w-4" />}
                            </button>
                        </div>
                    </header>
                    {isMobileMenuOpen && (
                        <button
                            type="button"
                            className="fixed inset-0 z-15 bg-(--welcome-on-strong-45) backdrop-blur-[2px] md:hidden"
                            aria-label="Close menu overlay"
                            onClick={() => setIsMobileMenuOpen(false)}
                        />
                    )}
                    {isMobileMenuOpen && (
                        <div
                            id="public-site-mobile-menu"
                            className="absolute top-full right-0 left-0 z-30 px-6 pt-3 md:hidden"
                        >
                            <nav className="mx-auto w-full max-w-6xl rounded-3xl border border-(--welcome-border-soft) bg-(--welcome-surface-1) px-5 py-4 text-sm shadow-[0_28px_70px_-30px_var(--welcome-shadow)]">
                                <p className="pb-2 text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                    Navigation
                                </p>
                                {renderMenuActions(true)}
                                <button
                                    type="button"
                                    onClick={() => updateAppearance(isDark ? 'light' : 'dark')}
                                    className="flex w-full items-center justify-between px-1 pt-3 text-left text-sm font-semibold tracking-[0.18em] text-(--welcome-muted-text) uppercase transition hover:text-(--welcome-strong)"
                                >
                                    <span>{toggleAppearanceLabel}</span>
                                    {isDark ? <Sun className="h-4 w-4" /> : <Moon className="h-4 w-4" />}
                                </button>
                            </nav>
                        </div>
                    )}
                </div>

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
                                <Link
                                    href={loomWeaveDemo()}
                                    className="hover:text-(--welcome-strong)"
                                >
                                    Design Studio
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
