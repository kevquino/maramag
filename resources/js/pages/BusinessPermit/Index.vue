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
        title: 'Business Permit',
        href: '/business-permit',
    },
];
</script>

<template>
    <Head title="Business Permit" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full p-6">
            <div class="w-full max-w-none mx-auto">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl sm:text-3xl font-bold text-foreground truncate">Business Permit</h1>
                        <p class="text-muted-foreground mt-1">Business permit management</p>
                    </div>
                </div>

                <div class="bg-card rounded-lg border shadow-sm p-6">
                    <div class="text-center py-12">
                        <p class="text-muted-foreground">Business permit management feature coming soon...</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>