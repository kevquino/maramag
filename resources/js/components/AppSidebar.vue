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

// Define a proper type for badge counts
interface BadgeCounts {
    news?: number;
    trash?: number;
    bids_awards?: number;
    full_disclosure?: number;
    tourism?: number;
    awards_recognition?: number;
    sangguniang_bayan?: number;
    ordinance_resolutions?: number;
    users?: number;
    activity_logs?: number;
}

// Compute badge counts from page props with proper typing
const badgeCounts = computed(() => {
    const props = page.props.badgeCounts as BadgeCounts | undefined;
    return {
        news: props?.news ?? 0,
        trash: props?.trash ?? 0,
        bids_awards: props?.bids_awards ?? 0,
        full_disclosure: props?.full_disclosure ?? 0,
        tourism: props?.tourism ?? 0,
        awards_recognition: props?.awards_recognition ?? 0,
        sangguniang_bayan: props?.sangguniang_bayan ?? 0,
        ordinance_resolutions: props?.ordinance_resolutions ?? 0,
        users: props?.users ?? 0,
        activity_logs: props?.activity_logs ?? 0,
    };
});

// Helper to show badge only if count > 0
const showBadge = (count: number) => count > 0 ? count : undefined;

// Check if user has permission
const hasPermission = (permission: string) => {
    const authUser = page.props.auth?.user as User;
    if (!authUser) return false;
    
    // Admin has all permissions
    if (authUser.role === 'admin') return true;
    
    // Check user permissions array
    const userPermissions = authUser.permissions || [];
    return userPermissions.includes(permission);
};

// Check if user is admin
const isAdmin = computed(() => {
    const authUser = page.props.auth?.user as User;
    return authUser?.role === 'admin';
});

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
        visible: hasPermission('dashboard') || isAdmin.value
    },
    {
        title: 'News',
        href: '/news',
        icon: Newspaper,
        badge: showBadge(badgeCounts.value.news),
        badgeVariant: 'default' as const,
        badgeShape: 'rounded' as const,
        badgeClass: 'bg-yellow-500 text-black shadow-sm',
        visible: hasPermission('news')
    },
    {
        title: 'Bids & Awards',
        href: '/bids-awards',
        icon: Gavel,
        badge: showBadge(badgeCounts.value.bids_awards),
        badgeVariant: 'default' as const,
        badgeShape: 'rounded' as const,
        badgeClass: 'bg-blue-500 text-white shadow-sm',
        visible: hasPermission('bids_awards')
    },
    {
        title: 'Full Disclosure',
        href: '/full-disclosure',
        icon: Shield,
        badge: showBadge(badgeCounts.value.full_disclosure),
        badgeVariant: 'default' as const,
        badgeShape: 'rounded' as const,
        badgeClass: 'bg-green-500 text-white shadow-sm',
        visible: hasPermission('full_disclosure')
    },
    {
        title: 'Tourism',
        href: '/tourism',
        icon: MapPin,
        badge: showBadge(badgeCounts.value.tourism),
        badgeVariant: 'default' as const,
        badgeShape: 'rounded' as const,
        badgeClass: 'bg-purple-500 text-white shadow-sm',
        visible: hasPermission('tourism')
    },
    {
        title: 'Awards & Recognition',
        href: '/awards-recognition',
        icon: Award,
        badge: showBadge(badgeCounts.value.awards_recognition),
        badgeVariant: 'default' as const,
        badgeShape: 'rounded' as const,
        badgeClass: 'bg-orange-500 text-white shadow-sm',
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
        href: '/business-permit/new-application',
        icon: Briefcase,
        visible: hasPermission('business_permit')
    },
    {
        title: 'Renewal Permit',
        href: '/business-permit/renewal-permit',
        icon: Briefcase,
        visible: hasPermission('business_permit')
    },
]);

const sanggunianItems = computed<NavItem[]>(() => [
    {
        title: 'Sangguniang Bayan',
        href: '/sangguniang-bayan',
        icon: Users,
        badge: showBadge(badgeCounts.value.sangguniang_bayan),
        badgeVariant: 'default' as const,
        badgeShape: 'rounded' as const,
        badgeClass: 'bg-indigo-500 text-white shadow-sm',
        visible: hasPermission('sangguniang_bayan')
    },
    {
        title: 'Ordinance & Resolutions',
        href: '/ordinance-resolutions',
        icon: FileText,
        badge: showBadge(badgeCounts.value.ordinance_resolutions),
        badgeVariant: 'default' as const,
        badgeShape: 'rounded' as const,
        badgeClass: 'bg-pink-500 text-white shadow-sm',
        visible: hasPermission('ordinance_resolutions')
    },
]);

const adminItems = computed<NavItem[]>(() => [
    {
        title: 'User Management',
        href: '/user-management',
        icon: Users,
        badge: showBadge(badgeCounts.value.users),
        badgeVariant: 'default' as const,
        badgeShape: 'rounded' as const,
        badgeClass: 'bg-red-500 text-white shadow-sm',
        visible: hasPermission('user_management') || isAdmin.value
    },
    {
        title: 'Activity Logs',
        href: '/activity-logs',
        icon: Activity,
        badge: showBadge(badgeCounts.value.activity_logs),
        badgeVariant: 'default' as const,
        badgeShape: 'rounded' as const,
        badgeClass: 'bg-gray-500 text-white shadow-sm',
        visible: hasPermission('activity_logs') || isAdmin.value
    },
    {
        title: 'Trash',
        href: '/trash',
        icon: Trash,
        badge: showBadge(badgeCounts.value.trash),
        badgeVariant: 'outline' as const,
        badgeShape: 'rounded' as const,
        badgeClass: 'border border-gray-200 bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        // FIX: Show trash to users with news permission OR admin
        visible: hasPermission('news') || isAdmin.value
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
            <!-- Main Navigation -->
            <NavMain v-if="visibleMainNavItems.length > 0" :items="visibleMainNavItems" />
            
            <!-- Business Permit Section -->
            <div v-if="visibleBusinessPermitItems.length > 0" class="border-t border-gray-200 dark:border-gray-700 my-2 mx-4"></div>
            <NavMain v-if="visibleBusinessPermitItems.length > 0" :items="visibleBusinessPermitItems" />
            
            <!-- Sanggunian Section -->
            <div v-if="visibleSanggunianItems.length > 0" class="border-t border-gray-200 dark:border-gray-700 my-2 mx-4"></div>
            <NavMain v-if="visibleSanggunianItems.length > 0" :items="visibleSanggunianItems" />
            
            <!-- Admin Section -->
            <div v-if="visibleAdminItems.length > 0" class="border-t border-gray-200 dark:border-gray-700 my-2 mx-4"></div>
            <NavMain v-if="visibleAdminItems.length > 0" :items="visibleAdminItems" />
        </SidebarContent>

        <!-- Show message if no navigation items are visible -->
        <SidebarContent v-else>
            <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                <p class="text-sm">No navigation items available.</p>
                <p class="text-xs mt-1">Contact administrator for access.</p>
            </div>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>