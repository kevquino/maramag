<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
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
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    BookOpen, 
    Folder, 
    LayoutGrid, 
    Newspaper,
    Gavel,
    Shield,
    MapPin,
    Award,
    Briefcase,
    FileText,
    Users,
    Activity,
    Trash
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

const page = usePage();

// Compute badge counts from page props with fallback
const badgeCounts = computed(() => page.props.badgeCounts || {
    news: 0,
    trash: 0,
});

// Helper to show badge only if count > 0
const showBadge = (count: number) => count > 0 ? count : undefined;

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'News',
        href: '/news',
        icon: Newspaper,
        badge: showBadge(badgeCounts.value.news),
        badgeVariant: 'default',
        badgeShape: 'rounded',
        badgeClass: 'bg-yellow-500 text-black shadow-sm'
    },
    {
        title: 'Bids & Awards',
        href: '/bids-awards',
        icon: Gavel,
    },
    {
        title: 'Full Disclosure Policy',
        href: '/full-disclosure',
        icon: Shield,
    },
    {
        title: 'Tourism',
        href: '/tourism',
        icon: MapPin,
    },
    {
        title: 'Awards & Recognition',
        href: '/awards-recognition',
        icon: Award,
    },
]);

const businessPermitItems = computed<NavItem[]>(() => [
    {
        title: 'Business Permit',
        href: '/business-permit',
        icon: Briefcase,
    },
    {
        title: 'New Application',
        href: '/new-application',
        icon: Briefcase,
    },
    {
        title: 'Renewal Permit',
        href: '/renewal-permit',
        icon: Briefcase,
    },
]);

const sanggunianItems = computed<NavItem[]>(() => [
    {
        title: 'Sangguniang Bayan',
        href: '/sangguniang-bayan',
        icon: Users,
    },
    {
        title: 'Ordinance & Resolutions',
        href: '/ordinance-resolutions',
        icon: FileText,
    },
]);

const adminItems = computed<NavItem[]>(() => [
    {
        title: 'User Management',
        href: '/user-management',
        icon: Users,
    },
    {
        title: 'Activity Logs',
        href: '/activity-logs',
        icon: Activity,
    },
    {
        title: 'Trash',
        href: '/trash',
        icon: Trash,
        badge: showBadge(badgeCounts.value.trash),
        badgeVariant: 'outline',
        badgeShape: 'rounded',
        badgeClass: 'border border-gray-200 bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
    },
]);

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <div class="border-t border-gray-200 dark:border-gray-700 my-2 mx-4"></div>
            <NavMain :items="businessPermitItems" />
            <div class="border-t border-gray-200 dark:border-gray-700 my-2 mx-4"></div>
            <NavMain :items="sanggunianItems" />
            <div class="border-t border-gray-200 dark:border-gray-700 my-2 mx-4"></div>
            <NavMain :items="adminItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>