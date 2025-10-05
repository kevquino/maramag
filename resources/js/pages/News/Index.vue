<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { news } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue'; // Updated path

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'News',
        href: news().url,
    },
];
</script>

<template>
    <Head title="News" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        News
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Latest updates and announcements
                    </p>
                </div>
                <button
                    class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                >
                    Add News
                </button>
            </div>

            <!-- News Grid -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <!-- News Card 1 -->
                <div
                    class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-gray-800"
                >
                    <div class="relative aspect-video overflow-hidden">
                        <PlaceholderPattern />
                    </div>
                    <div class="p-4">
                        <div class="mb-2 flex items-center gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                March 15, 2024
                            </span>
                            <span class="rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                Announcement
                            </span>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">
                            New Feature Released
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm">
                            We're excited to announce the release of our latest feature that will revolutionize your workflow.
                        </p>
                    </div>
                </div>

                <!-- News Card 2 -->
                <div
                    class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-gray-800"
                >
                    <div class="relative aspect-video overflow-hidden">
                        <PlaceholderPattern />
                    </div>
                    <div class="p-4">
                        <div class="mb-2 flex items-center gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                March 10, 2024
                            </span>
                            <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200">
                                Update
                            </span>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">
                            System Maintenance
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm">
                            Scheduled maintenance will occur this weekend to improve system performance and security.
                        </p>
                    </div>
                </div>

                <!-- News Card 3 -->
                <div
                    class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-gray-800"
                >
                    <div class="relative aspect-video overflow-hidden">
                        <PlaceholderPattern />
                    </div>
                    <div class="p-4">
                        <div class="mb-2 flex items-center gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                March 5, 2024
                            </span>
                            <span class="rounded-full bg-purple-100 px-2 py-1 text-xs font-medium text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                Event
                            </span>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">
                            Upcoming Webinar
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm">
                            Join our free webinar to learn about best practices and new industry trends.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Loading/Empty State -->
            <div
                class="relative flex min-h-[200px] flex-1 items-center justify-center rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
            >
                <div class="text-center">
                    <PlaceholderPattern class="mx-auto mb-4 h-12 w-12 opacity-50" />
                    <p class="text-gray-500 dark:text-gray-400">
                        More news coming soon...
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>