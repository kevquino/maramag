<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Activity Logs',
        href: '/activity-logs',
    },
];
</script>

<template>
    <Head title="Activity Logs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full p-6">
            <div class="w-full max-w-none mx-auto">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl sm:text-3xl font-bold text-foreground truncate">Activity Logs</h1>
                        <p class="text-muted-foreground mt-1">System activity and user actions</p>
                    </div>
                </div>

                <div class="bg-card rounded-lg border shadow-sm p-6">
                    <div class="text-center py-12">
                        <p class="text-muted-foreground">Activity logs feature coming soon...</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>