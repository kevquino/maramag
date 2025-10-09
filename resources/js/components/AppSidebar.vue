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
import { Link } from '@inertiajs/vue3';
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

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    // Main Menu Items
    {
        title: 'News',
        href: '/news',
        icon: Newspaper,
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
];

const businessPermitItems: NavItem[] = [
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
];

const sanggunianItems: NavItem[] = [
    {
        title: 'Sagguniang Bayan',
        href: '/sanggunian-bayan',
        icon: FileText,
    },{
        title: 'Ordinance & Resolutions',
        href: '/ordinances-resolutions',
        icon: FileText,
    },
];

const adminItems: NavItem[] = [
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
    },
];

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
            <!-- All items in sequence with separators -->
            <NavMain :items="mainNavItems" />
            
            <!-- Separator after Awards & Recognition -->
            <div class="border-t border-gray-200 dark:border-gray-700 my-2 mx-4"></div>
            
            <NavMain :items="businessPermitItems" />
            
            <!-- Separator after Renewal Permit -->
            <div class="border-t border-gray-200 dark:border-gray-700 my-2 mx-4"></div>
            
            <NavMain :items="sanggunianItems" />
            
            <!-- Separator after Ordinance & Resolutions -->
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