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

defineProps<{
    items: NavItem[];
}>();

const page = usePage();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
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
                <SidebarMenuBadge 
                    v-if="item.badge" 
                    :variant="item.badgeVariant || 'default'"
                    :shape="item.badgeShape || 'auto'"
                    :class="item.badgeClass"
                >
                    {{ item.badge }}
                </SidebarMenuBadge>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>