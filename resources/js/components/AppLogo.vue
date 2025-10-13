<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

interface Props {
    isCollapsed?: boolean;
}

defineProps<Props>();
</script>

<template>
    <div class="flex items-center w-full" :class="isCollapsed ? 'justify-center' : 'justify-start'">
        <div class="flex size-8 items-center justify-center rounded-full shrink-0">
            <AppLogoIcon class="size-auto fill-current text-white dark:text-black" />
        </div>
        
        <div 
            v-if="!isCollapsed" 
            class="ml-2 grid flex-1 text-left text-sm"
        >
            <span class="truncate leading-tight font-semibold">LGU Maramag</span>
        </div>
    </div>
</template>