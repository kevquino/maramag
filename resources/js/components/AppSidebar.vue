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
import { type NavItem, type User } from '@/types';
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

// Check if user has permission
const hasPermission = (permission: string) => {
    const authUser = page.props.auth.user as User;
    if (!authUser) return false;
    
    // Admin has all permissions
    if (authUser.role === 'admin') return true;
    
    // Check user permissions array
    const userPermissions = authUser.permissions || [];
    return userPermissions.includes(permission);
};

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
        visible: hasPermission('dashboard') // Dashboard will be hidden if user doesn't have permission
    },
    {
        title: 'News',
        href: '/news',
        icon: Newspaper,
        badge: showBadge(badgeCounts.value.news),
        badgeVariant: 'default',
        badgeShape: 'rounded',
        badgeClass: 'bg-yellow-500 text-black shadow-sm',
        visible: hasPermission('news')
    },
    {
        title: 'Bids & Awards',
        href: '/bids-awards',
        icon: Gavel,
        visible: hasPermission('bids_awards')
    },
    {
        title: 'Full Disclosure Policy',
        href: '/full-disclosure',
        icon: Shield,
        visible: hasPermission('full_disclosure')
    },
    {
        title: 'Tourism',
        href: '/tourism',
        icon: MapPin,
        visible: hasPermission('tourism')
    },
    {
        title: 'Awards & Recognition',
        href: '/awards-recognition',
        icon: Award,
        visible: hasPermission('awards_recognition')
    },
]);

const businessPermitItems = computed<NavItem[]>(() => [
    {
        title: 'Business Permit',
        href: '/business-permit',
        icon: Briefcase,
        visible: hasPermission('business_permit')
    },
    {
        title: 'New Application',
        href: '/new-application',
        icon: Briefcase,
        visible: hasPermission('business_permit')
    },
    {
        title: 'Renewal Permit',
        href: '/renewal-permit',
        icon: Briefcase,
        visible: hasPermission('business_permit')
    },
]);

const sanggunianItems = computed<NavItem[]>(() => [
    {
        title: 'Sangguniang Bayan',
        href: '/sangguniang-bayan',
        icon: Users,
        visible: hasPermission('sangguniang_bayan')
    },
    {
        title: 'Ordinance & Resolutions',
        href: '/ordinance-resolutions',
        icon: FileText,
        visible: hasPermission('ordinance_resolutions')
    },
]);

const adminItems = computed<NavItem[]>(() => [
    {
        title: 'User Management',
        href: '/user-management',
        icon: Users,
        visible: hasPermission('user_management')
    },
    {
        title: 'Activity Logs',
        href: '/activity-logs',
        icon: Activity,
        visible: hasPermission('activity_logs')
    },
    {
        title: 'Trash',
        href: '/trash',
        icon: Trash,
        badge: showBadge(badgeCounts.value.trash),
        badgeVariant: 'outline',
        badgeShape: 'rounded',
        badgeClass: 'border border-gray-200 bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        visible: hasPermission('trash')
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

// Filter visible items only
const visibleMainNavItems = computed(() => mainNavItems.value.filter(item => item.visible !== false));
const visibleBusinessPermitItems = computed(() => businessPermitItems.value.filter(item => item.visible !== false));
const visibleSanggunianItems = computed(() => sanggunianItems.value.filter(item => item.visible !== false));
const visibleAdminItems = computed(() => adminItems.value.filter(item => item.visible !== false));

// Check if any navigation items are visible
const hasVisibleNavigation = computed(() => {
    return visibleMainNavItems.value.length > 0 ||
           visibleBusinessPermitItems.value.length > 0 ||
           visibleSanggunianItems.value.length > 0 ||
           visibleAdminItems.value.length > 0;
});
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

        <SidebarContent v-if="hasVisibleNavigation">
            <NavMain :items="visibleMainNavItems" />
            
            <div v-if="visibleBusinessPermitItems.length > 0" class="border-t border-gray-200 dark:border-gray-700 my-2 mx-4"></div>
            <NavMain v-if="visibleBusinessPermitItems.length > 0" :items="visibleBusinessPermitItems" />
            
            <div v-if="visibleSanggunianItems.length > 0" class="border-t border-gray-200 dark:border-gray-700 my-2 mx-4"></div>
            <NavMain v-if="visibleSanggunianItems.length > 0" :items="visibleSanggunianItems" />
            
            <div v-if="visibleAdminItems.length > 0" class="border-t border-gray-200 dark:border-gray-700 my-2 mx-4"></div>
            <NavMain v-if="visibleAdminItems.length > 0" :items="visibleAdminItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>