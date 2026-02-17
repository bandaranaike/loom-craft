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
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
