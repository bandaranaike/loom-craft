import { Link, usePage } from '@inertiajs/react';
import type { CSSProperties, PropsWithChildren, ReactNode } from 'react';
import AppLogoIcon from '@/components/app-logo-icon';
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
    'rounded-full border border-transparent px-4 py-2 font-medium text-[#2b241c]/70 transition hover:border-[#2b241c] hover:text-[#2b241c]';

export default function PublicSiteLayout({
    children,
    canRegister = true,
    showBrowseProducts = true,
    headerActions,
}: PublicSiteLayoutProps) {
    const { auth } = usePage<SharedData>().props;
    const leftSidePatternStyle: CSSProperties = {
        backgroundImage: `linear-gradient(to right, rgba(246,241,232,0.95), rgba(246,241,232,0.45), transparent), url(${dumbaraPatterns}), url(${dumbaraPatterns})`,
        backgroundSize: '100% 100%, 170px auto, 140px auto',
        backgroundRepeat: 'no-repeat, repeat-y, repeat-y',
        backgroundPosition: 'left center, 0 0, 84px 120px',
    };
    const rightSidePatternStyle: CSSProperties = {
        backgroundImage: `linear-gradient(to left, rgba(246,241,232,0.95), rgba(246,241,232,0.45), transparent), url(${dumbaraPatterns}), url(${dumbaraPatterns})`,
        backgroundSize: '100% 100%, 170px auto, 140px auto',
        backgroundRepeat: 'no-repeat, repeat-y, repeat-y',
        backgroundPosition: 'right center, 100% 0, calc(100% - 84px) 120px',
    };

    return (
        <div className="min-h-screen bg-[#F6F1E8] text-[#2b241c]">
            <div className="relative overflow-hidden">
                <div className="pointer-events-none absolute bottom-0 left-1/2 h-80 w-180 -translate-x-1/2 rounded-[100%] bg-[radial-gradient(ellipse_at_center,#e0c7a7,transparent_70%)] opacity-60" />
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
                                        className="rounded-full border border-[#2b241c] px-4 py-2 font-medium transition hover:bg-[#2b241c] hover:text-[#f6f1e8]"
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
                                                className="rounded-full border border-[#2b241c] px-4 py-2 font-medium transition hover:bg-[#2b241c] hover:text-[#f6f1e8]"
                                            >
                                                Become a Patron
                                            </Link>
                                        )}
                                    </>
                                )}
                            </>
                        )}
                    </nav>
                </header>

                <main className="relative z-10">{children}</main>

                <footer className="relative z-10 border-t border-[#e0c7a7] bg-[#f9efe2]">
                    <div className="mx-auto grid w-full max-w-6xl gap-8 px-6 py-10 md:grid-cols-3">
                        <div>
                            <p className="font-['Playfair_Display',serif] text-2xl">
                                LoomCraft
                            </p>
                            <p className="text-xs tracking-[0.3em] text-[#7a5a3a] uppercase">
                                Heritage Marketplace
                            </p>
                        </div>
                        <div>
                            <p className="text-xs tracking-[0.3em] text-[#7a5a3a] uppercase">
                                Header Menu
                            </p>
                            <div className="mt-3 flex flex-wrap gap-3 text-xs tracking-[0.25em] text-[#7a5a3a] uppercase">
                                <Link
                                    href={home()}
                                    className="hover:text-[#2b241c]"
                                >
                                    Home
                                </Link>
                                <Link
                                    href={productsIndex()}
                                    className="hover:text-[#2b241c]"
                                >
                                    Products
                                </Link>
                                <Link
                                    href={loomWeaveDemo()}
                                    className="hover:text-[#2b241c]"
                                >
                                    Loom Demo
                                </Link>
                                <Link
                                    href={cartShow()}
                                    className="hover:text-[#2b241c]"
                                >
                                    Cart
                                </Link>
                                <Link
                                    href={checkoutShow()}
                                    className="hover:text-[#2b241c]"
                                >
                                    Checkout
                                </Link>
                            </div>
                        </div>
                        <div>
                            <p className="text-xs tracking-[0.3em] text-[#7a5a3a] uppercase">
                                Footer Menu
                            </p>
                            <div className="mt-3 flex flex-wrap gap-3 text-xs tracking-[0.25em] text-[#7a5a3a] uppercase">
                                <span>Contact</span>
                                <span>Terms</span>
                                <span>Privacy</span>
                                <span>Cookies</span>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    );
}
