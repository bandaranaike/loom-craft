import { Link, usePage } from '@inertiajs/react';
import {
    BookOpen,
    Folder,
    LayoutGrid,
    MessageSquareQuote,
    Package,
    ShieldCheck,
    Video,
} from 'lucide-react';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { pending as adminFeedbackPending } from '@/routes/admin/feedback';
import { pending as adminProductsPending } from '@/routes/admin/products';
import { connect as adminYouTubeConnect } from '@/routes/admin/youtube';
import { pending as adminVendorsPending } from '@/routes/admin/vendors';
import { create as vendorFeedbackCreate } from '@/routes/vendor/feedback';
import { index as vendorProductsIndex } from '@/routes/vendor/products';
import type { NavItem, SharedData } from '@/types';
import AppLogo from './app-logo';

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    const { auth } = usePage<SharedData>().props;
    const isAdmin = auth?.user?.role === 'admin';
    const isVendor = auth?.user?.role === 'vendor';

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
        ...(isAdmin
            ? [
                  {
                      title: 'Vendor Approvals',
                      href: adminVendorsPending(),
                      icon: ShieldCheck,
                  },
                  {
                      title: 'Product Approvals',
                      href: adminProductsPending(),
                      icon: Package,
                  },
                  {
                      title: 'YouTube Connect',
                      href: adminYouTubeConnect(),
                      icon: Video,
                  },
                  {
                      title: 'Feedback Approvals',
                      href: adminFeedbackPending(),
                      icon: MessageSquareQuote,
                  },
              ]
            : []),
        ...(isVendor
            ? [
                  {
                      title: 'My Products',
                      href: vendorProductsIndex(),
                      icon: Package,
                  },
                  {
                      title: 'Vendor Feedback',
                      href: vendorFeedbackCreate(),
                      icon: MessageSquareQuote,
                  },
              ]
            : []),
    ];

    return (
        <Sidebar
            collapsible="icon"
            variant="inset"
            className="text-(--welcome-strong) **:data-[sidebar=sidebar]:bg-(--welcome-on-strong)"
        >
            <SidebarHeader className="rounded-[20px] border border-(--welcome-border) bg-(--welcome-surface-1) p-2 shadow-[0_20px_45px_-38px_var(--welcome-shadow-heavy)]">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            size="lg"
                            asChild
                            className="rounded-[14px]"
                        >
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent className="mt-2 rounded-[20px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-1">
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter className="mt-2 rounded-[20px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-2">
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
