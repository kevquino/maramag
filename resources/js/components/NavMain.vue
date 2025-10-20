<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuBadge,
} from '@/components/ui/sidebar';
import { urlIsActive } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// Import sidebar context to get collapsed state
import { useSidebar } from '@/components/ui/sidebar/utils';

interface Props {
    items: NavItem[];
}

defineProps<Props>();

const page = usePage();
const { state } = useSidebar();
const isCollapsed = computed(() => state.value === 'collapsed');

// Helper function to safely compare badge values
const shouldTruncateBadge = (badge: string | number | undefined): boolean => {
    if (!badge) return false;
    const num = typeof badge === 'string' ? parseInt(badge, 10) : badge;
    return num > 99;
};

// Helper function to get display value for mini badge
const getMiniBadgeDisplay = (badge: string | number | undefined): string => {
    if (!badge) return '';
    const num = typeof badge === 'string' ? parseInt(badge, 10) : badge;
    return num > 99 ? '99+' : badge.toString();
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title" class="relative">
                <SidebarMenuButton
                    as-child
                    :tooltip="item.title"
                    :class="[
                        'hover:bg-gradient-to-r hover:from-blue-500/20 hover:to-green-400/10 hover:text-accent-foreground hover:backdrop-blur-md',
                        { 'bg-gradient-to-r from-blue-500/30 to-green-400/15 backdrop-blur-md': urlIsActive(item.href, page.url) }
                    ]"
                >
                    <Link :href="item.href" class="flex items-center gap-2">
                        <component :is="item.icon" class="h-4 w-4" />
                        <span class="text-sm">{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
                
                <!-- Regular badge for expanded sidebar -->
                <SidebarMenuBadge 
                    v-if="item.badge && !isCollapsed" 
                    :variant="item.badgeVariant || 'default'"
                    :shape="item.badgeShape || 'auto'"
                    :class="item.badgeClass"
                >
                    {{ item.badge }}
                </SidebarMenuBadge>
                
                <!-- Mini badge for collapsed sidebar - Larger size -->
                <div 
                    v-else-if="item.badge && isCollapsed"
                    :class="[
                        'absolute -top-1 -right-1 w-5 h-5 flex items-center justify-center rounded-full text-xs font-medium border-2 border-white dark:border-gray-950 z-10 shadow-sm',
                        item.badgeClass || 'bg-red-500 text-white'
                    ]"
                >
                    {{ getMiniBadgeDisplay(item.badge) }}
                </div>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>