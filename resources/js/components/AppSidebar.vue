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

// Import sidebar context
import { useSidebar } from '@/components/ui/sidebar/utils';

const page = usePage();
const { state } = useSidebar();
const isCollapsed = computed(() => state.value === 'collapsed');

// Define proper types for badge counts (handle both old and new structures)
interface SimpleBadgeCounts {
    news?: number | { total: number; draft: number; pending: number };
    trash?: number;
    bids_awards?: number | { total: number; active: number; closed: number };
    full_disclosure?: number | { total: number; recent: number };
    tourism?: number | { total: number; active: number; featured: number };
    awards_recognition?: number | { total: number; active: number; featured: number };
    sangguniang_bayan?: number | { total: number; active: number };
    ordinance_resolutions?: number | { total: number; ordinances: number; resolutions: number };
    users?: number | { total: number; active: number; pending: number };
    activity_logs?: number;
}

// Compute badge counts from page props with proper typing and structure handling
const badgeCounts = computed(() => {
    const props = page.props.badgeCounts as SimpleBadgeCounts | undefined;
    
    // Helper function to extract count from both old and new structures
    const getCount = (value: any): number => {
        if (typeof value === 'number') {
            return value;
        }
        if (value && typeof value === 'object' && 'total' in value) {
            return value.total as number;
        }
        return 0;
    };

    return {
        news: getCount(props?.news),
        trash: getCount(props?.trash),
        bids_awards: getCount(props?.bids_awards),
        full_disclosure: getCount(props?.full_disclosure),
        tourism: getCount(props?.tourism),
        awards_recognition: getCount(props?.awards_recognition),
        sangguniang_bayan: getCount(props?.sangguniang_bayan),
        ordinance_resolutions: getCount(props?.ordinance_resolutions),
        users: getCount(props?.users),
        activity_logs: getCount(props?.activity_logs),
    };
});

// Compute total badge count for global mini badge
const totalBadgeCount = computed(() => {
    return Object.values(badgeCounts.value).reduce((sum, count) => sum + count, 0);
});

// Helper function for global mini badge display
const getGlobalMiniBadgeDisplay = (count: number): string => {
    return count > 99 ? '99+' : count.toString();
};

const showGlobalMiniBadge = computed(() => {
    return isCollapsed.value && totalBadgeCount.value > 0;
});

// Helper to show badge only if count > 0
const showBadge = (count: number) => count > 0 ? count : undefined;

// Universal permission check - grants access based on database permissions array
const hasPermission = (permission: string) => {
    const authUser = page.props.auth?.user as User;
    
    if (!authUser) {
        return false;
    }
    
    // Superadmin has all permissions
    if (authUser.role === 'superadmin') {
        return true;
    }

    // Universal permission system: Check database permissions array
    const userPermissions = authUser.permissions;
    
    if (!userPermissions) {
        return false;
    }

    let permissionsArray: string[] = [];

    // Handle different permission formats from database
    if (Array.isArray(userPermissions)) {
        permissionsArray = userPermissions;
    } else if (typeof userPermissions === 'string') {
        try {
            // Try to parse as JSON
            const parsed = JSON.parse(userPermissions);
            if (Array.isArray(parsed)) {
                permissionsArray = parsed;
            } else {
                permissionsArray = [];
            }
        } catch (e) {
            // If not JSON, try comma-separated
            permissionsArray = (userPermissions as string).split(',').map((p: string) => p.trim());
        }
    } else {
        return false;
    }

    // Clean permissions array (remove empty strings, etc.)
    permissionsArray = permissionsArray.filter(p => p && typeof p === 'string');
    
    // UNIVERSAL RULE: If permission exists in database array, grant access
    return permissionsArray.includes(permission);
};

// Check if user is superadmin
const isSuperAdmin = computed(() => {
    const authUser = page.props.auth?.user as User;
    return authUser?.role === 'superadmin';
});

// Check if user can manage users (special case for superadmin)
const canManageUsers = computed(() => {
    return hasPermission('user_management') || isSuperAdmin.value;
});

// Check if user can view activity logs (special case for superadmin)
const canViewActivityLogs = computed(() => {
    return hasPermission('activity_logs') || isSuperAdmin.value;
});

// Main navigation items - visibility based solely on database permissions
const mainNavItems = computed<NavItem[]>(() => {
    return [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
            // Dashboard is always visible to authenticated users
            visible: true
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
    ];
});

// Business permit items
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
        visible: hasPermission('new_application')
    },
    {
        title: 'Renewal Permit',
        href: '/business-permit/renewal-permit',
        icon: Briefcase,
        visible: hasPermission('renewal_permit')
    },
]);

// Sanggunian items
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

// Admin items - visibility based on database permissions or superadmin status
const adminItems = computed<NavItem[]>(() => [
    {
        title: 'User Management',
        href: '/user-management',
        icon: Users,
        badge: showBadge(badgeCounts.value.users),
        badgeVariant: 'default' as const,
        badgeShape: 'rounded' as const,
        badgeClass: 'bg-red-500 text-white shadow-sm',
        visible: canManageUsers.value
    },
    {
        title: 'Activity Logs',
        href: '/activity-logs',
        icon: Activity,
        badge: showBadge(badgeCounts.value.activity_logs),
        badgeVariant: 'default' as const,
        badgeShape: 'rounded' as const,
        badgeClass: 'bg-gray-500 text-white shadow-sm',
        visible: canViewActivityLogs.value
    },
    {
        title: 'Trash',
        href: '/trash',
        icon: Trash,
        badge: showBadge(badgeCounts.value.trash),
        badgeVariant: 'outline' as const,
        badgeShape: 'rounded' as const,
        badgeClass: 'border border-gray-200 bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        visible: hasPermission('trash')
    },
]);

// Footer items
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

// Filter visible items
const visibleMainNavItems = computed(() => {
    return mainNavItems.value.filter(item => item.visible !== false);
});

const visibleBusinessPermitItems = computed(() => {
    return businessPermitItems.value.filter(item => item.visible !== false);
});

const visibleSanggunianItems = computed(() => {
    return sanggunianItems.value.filter(item => item.visible !== false);
});

const visibleAdminItems = computed(() => {
    return adminItems.value.filter(item => item.visible !== false);
});

// Check if any navigation items are visible
const hasVisibleNavigation = computed(() => {
    return visibleMainNavItems.value.length > 0 ||
        visibleBusinessPermitItems.value.length > 0 ||
        visibleSanggunianItems.value.length > 0 ||
        visibleAdminItems.value.length > 0;
});
</script>

<template>
    <Sidebar 
        collapsible="icon" 
        variant="inset"
        class="bg-white dark:bg-gray-950 md:bg-transparent relative"
    >

        <SidebarHeader class="bg-white dark:bg-gray-950 md:bg-transparent">
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

        <SidebarContent 
            v-if="hasVisibleNavigation" 
            class="bg-white dark:bg-gray-950 md:bg-transparent"
        >
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
        <SidebarContent 
            v-else 
            class="bg-white dark:bg-gray-950 md:bg-transparent"
        >
            <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                <p class="text-sm">No navigation items available.</p>
                <p class="text-xs mt-1">Contact administrator for access.</p>
                <p class="text-xs mt-1">User: {{ page.props.auth?.user?.email }}</p>
                <p class="text-xs mt-1">Role: {{ page.props.auth?.user?.role }}</p>
                <p class="text-xs mt-1">Permissions: {{ page.props.auth?.user?.permissions }}</p>
            </div>
        </SidebarContent>

        <SidebarFooter class="bg-white dark:bg-gray-950 md:bg-transparent">
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>