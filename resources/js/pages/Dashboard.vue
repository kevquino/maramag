<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

// Shadcn/vue components
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';

// Calendar imports
import type { DateValue } from "@internationalized/date";
import type { CalendarRootEmits, CalendarRootProps } from "reka-ui";
import type { HTMLAttributes, Ref } from "vue";
import { getLocalTimeZone, today } from "@internationalized/date";
import { useVModel } from "@vueuse/core";
import { CalendarRoot, useDateFormatter, useForwardPropsEmits } from "reka-ui";
import { createDecade, createYear, toDate } from "reka-ui/date";
import { CalendarCell, CalendarCellTrigger, CalendarGrid, CalendarGridBody, CalendarGridHead, CalendarGridRow, CalendarHeadCell, CalendarHeader, CalendarHeading } from "@/components/ui/calendar";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

// Enhanced Calendar Component
const EnhancedCalendarProps = withDefaults(defineProps<CalendarRootProps & { class?: HTMLAttributes["class"] }>(), {
  modelValue: undefined,
  placeholder() {
    return today(getLocalTimeZone())
  },
  weekdayFormat: "short",
})

const EnhancedCalendarEmits = defineEmits<CalendarRootEmits>()

const delegatedProps = computed(() => {
  const { class: _, placeholder: __, ...delegated } = EnhancedCalendarProps
  return delegated
})

const placeholder = useVModel(EnhancedCalendarProps, "modelValue", EnhancedCalendarEmits, {
  passive: true,
  defaultValue: today(getLocalTimeZone()),
}) as Ref<DateValue>

const forwarded = useForwardPropsEmits(delegatedProps, EnhancedCalendarEmits)

const formatter = useDateFormatter("en")

// Calendar value
const calendarValue = ref(today(getLocalTimeZone())) as Ref<DateValue>;

// Types for our data
interface Activity {
    id: number;
    description: string;
    type: string;
    created_at: string;
}

interface NewsItem {
    id: number;
    title: string;
    category: string;
    status: string;
    is_featured: boolean;
    published_at?: string;
    created_at: string;
}

// Reactive data
const stats = ref({
    news: {
        total: 0,
        published: 0,
        draft: 0,
        featured: 0
    },
    bids: {
        total: 0,
        active: 0,
        completed: 0,
        upcoming: 0
    },
    disclosure: {
        total: 0,
        published: 0,
        pending: 0
    },
    tourism: {
        total: 0,
        active: 0,
        featured: 0,
        upcoming: 0
    },
    awards: {
        total: 0,
        given: 0,
        pending: 0,
        categories: 0
    },
    sanggunian: {
        total: 0,
        active: 0,
        completed: 0
    },
    ordinance: {
        total: 0,
        passed: 0,
        pending: 0
    },
    users: {
        total: 0,
        active: 0,
        new: 0
    }
});

const recentNews = ref<NewsItem[]>([]);
const recentBids = ref([]);
const recentDisclosures = ref([]);
const recentTourism = ref([]);
const recentAwards = ref([]);
const recentSanggunian = ref([]);
const recentOrdinance = ref([]);
const recentActivity = ref<Activity[]>([]);
const isLoading = ref(true);

// Fetch dashboard data
const fetchDashboardData = async () => {
    try {
        const response = await fetch('/api/dashboard/stats');
        const data = await response.json();
        
        stats.value = data.stats;
        recentNews.value = data.recentNews || [];
        recentBids.value = data.recentBids || [];
        recentDisclosures.value = data.recentDisclosures || [];
        recentTourism.value = data.recentTourism || [];
        recentAwards.value = data.recentAwards || [];
        recentSanggunian.value = data.recentSanggunian || [];
        recentOrdinance.value = data.recentOrdinance || [];
        recentActivity.value = data.recentActivity || [];
    } catch (error) {
        console.error('Error fetching dashboard data:', error);
    } finally {
        isLoading.value = false;
    }
};

// Navigation functions
const navigateTo = (route: string) => {
    router.visit(route);
};

// Format date
const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

// Get status variant for badge
const getStatusVariant = (status: string) => {
    switch (status) {
        case 'published':
        case 'active':
        case 'completed':
        case 'passed':
        case 'given':
            return 'default';
        case 'draft':
        case 'upcoming':
        case 'pending':
            return 'secondary';
        case 'archived':
        case 'inactive':
            return 'outline';
        default:
            return 'outline';
    }
};

// Get category variant for badge
const getCategoryVariant = (category: string) => {
    const variants: Record<string, string> = {
        'Business': 'default',
        'Finance': 'secondary',
        'Events': 'outline',
        'Partnerships': 'default',
        'Sustainability': 'secondary',
        'Company News': 'outline',
        'Announcement': 'default',
        'Update': 'secondary',
        'Event': 'outline',
        'Maintenance': 'default'
    };
    return variants[category] || 'outline';
};

onMounted(() => {
    fetchDashboardData();
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Top Section - 4 Columns: Recent Activity (3 cols) + Calendar (1 col) -->
            <div class="grid gap-6 lg:grid-cols-4">
                <!-- Recent Activities - Span 3 columns -->
                <Card class="relative overflow-hidden lg:col-span-3">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            Recent Activities
                        </CardTitle>
                        <CardDescription>
                            Latest updates and activities across all systems
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="isLoading" class="space-y-4">
                            <div v-for="n in 8" :key="n" class="flex items-start space-x-3">
                                <Skeleton class="h-8 w-8 rounded-full" />
                                <div class="space-y-2 flex-1">
                                    <Skeleton class="h-4 w-full" />
                                    <Skeleton class="h-3 w-2/3" />
                                </div>
                            </div>
                        </div>
                        
                        <div v-else-if="recentActivity.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="rounded-full bg-muted p-4">
                                <svg class="h-8 w-8 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <p class="mt-4 text-lg font-medium">No recent activity</p>
                            <p class="mt-2 text-sm text-muted-foreground max-w-sm">
                                Activity feed will appear here as users interact with the system
                            </p>
                        </div>
                        
                        <div v-else class="space-y-4 max-h-[500px] overflow-y-auto">
                            <div 
                                v-for="activity in recentActivity" 
                                :key="activity.id"
                                class="flex items-start space-x-3 p-3 rounded-lg border transition-colors hover:bg-muted/50"
                            >
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center dark:bg-blue-900">
                                        <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-foreground">{{ activity.description }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <Badge variant="outline" class="text-xs">
                                            {{ activity.type }}
                                        </Badge>
                                        <span class="text-xs text-muted-foreground">
                                            {{ formatDate(activity.created_at) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Enhanced Calendar - 1 column -->
                <Card class="w-full flex flex-col">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Calendar
                        </CardTitle>
                        <CardDescription>
                            Upcoming events and schedules
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="flex-1 flex items-center justify-center">
                        <CalendarRoot
                            v-slot="{ date, grid, weekDays }"
                            v-model:placeholder="placeholder"
                            v-bind="forwarded"
                            class="rounded-md border p-3 w-full max-w-sm mx-auto"
                        >
                            <CalendarHeader>
                                <CalendarHeading class="flex w-full items-center justify-between gap-2">
                                    <Select
                                        :default-value="placeholder.month.toString()"
                                        @update:model-value="(v) => {
                                            if (!v || !placeholder) return;
                                            if (Number(v) === placeholder?.month) return;
                                            placeholder = placeholder.set({
                                                month: Number(v),
                                            })
                                        }"
                                    >
                                        <SelectTrigger aria-label="Select month" class="w-[60%]">
                                            <SelectValue placeholder="Select month" />
                                        </SelectTrigger>
                                        <SelectContent class="max-h-[200px]">
                                            <SelectItem
                                                v-for="month in createYear({ dateObj: date })"
                                                :key="month.toString()" :value="month.month.toString()"
                                            >
                                                {{ formatter.custom(toDate(month), { month: 'long' }) }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>

                                    <Select
                                        :default-value="placeholder.year.toString()"
                                        @update:model-value="(v) => {
                                            if (!v || !placeholder) return;
                                            if (Number(v) === placeholder?.year) return;
                                            placeholder = placeholder.set({
                                                year: Number(v),
                                            })
                                        }"
                                    >
                                        <SelectTrigger aria-label="Select year" class="w-[40%]">
                                            <SelectValue placeholder="Select year" />
                                        </SelectTrigger>
                                        <SelectContent class="max-h-[200px]">
                                            <SelectItem
                                                v-for="yearValue in createDecade({ dateObj: date, startIndex: -10, endIndex: 10 })"
                                                :key="yearValue.toString()" :value="yearValue.year.toString()"
                                            >
                                                {{ yearValue.year }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </CalendarHeading>
                            </CalendarHeader>

                            <div class="flex flex-col space-y-4 pt-4">
                                <CalendarGrid v-for="month in grid" :key="month.value.toString()">
                                    <CalendarGridHead>
                                        <CalendarGridRow>
                                            <CalendarHeadCell
                                                v-for="day in weekDays" :key="day"
                                            >
                                                {{ day }}
                                            </CalendarHeadCell>
                                        </CalendarGridRow>
                                    </CalendarGridHead>
                                    <CalendarGridBody class="grid">
                                        <CalendarGridRow v-for="(weekDates, index) in month.rows" :key="`weekDate-${index}`" class="mt-2 w-full">
                                            <CalendarCell
                                                v-for="weekDate in weekDates"
                                                :key="weekDate.toString()"
                                                :date="weekDate"
                                            >
                                                <CalendarCellTrigger
                                                    :day="weekDate"
                                                    :month="month.value"
                                                />
                                            </CalendarCell>
                                        </CalendarGridRow>
                                    </CalendarGridBody>
                                </CalendarGrid>
                            </div>
                        </CalendarRoot>
                    </CardContent>
                </Card>
            </div>

            <!-- Middle Section - 5 Columns -->
            <div class="grid gap-6 lg:grid-cols-5">
                <!-- News & Articles -->
                <Card class="relative overflow-hidden">
                    <CardHeader class="pb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="text-sm flex items-center gap-2">
                                    <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9m0 0v12m0-12a2 2 0 012-2h2a2 2 0 012 2M9 6h6m-6 6h6m-6 6h6"></path>
                                    </svg>
                                    News & Articles
                                </CardTitle>
                            </div>
                            <Button variant="ghost" size="sm" @click="navigateTo('/news')">
                                View
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-foreground">
                                <Skeleton v-if="isLoading" class="h-7 w-12 mx-auto" />
                                <span v-else>{{ stats.news.total }}</span>
                            </div>
                            <div class="text-xs text-muted-foreground">Total Articles</div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-center">
                            <div>
                                <div class="text-sm font-semibold text-green-600">{{ stats.news.published }}</div>
                                <div class="text-xs text-muted-foreground">Published</div>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-yellow-600">{{ stats.news.draft }}</div>
                                <div class="text-xs text-muted-foreground">Drafts</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Bids & Awards -->
                <Card class="relative overflow-hidden">
                    <CardHeader class="pb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="text-sm flex items-center gap-2">
                                    <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    Bids & Awards
                                </CardTitle>
                            </div>
                            <Button variant="ghost" size="sm" @click="navigateTo('/bids')">
                                View
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-foreground">
                                <Skeleton v-if="isLoading" class="h-7 w-12 mx-auto" />
                                <span v-else>{{ stats.bids.total }}</span>
                            </div>
                            <div class="text-xs text-muted-foreground">Total Bids</div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-center">
                            <div>
                                <div class="text-sm font-semibold text-green-600">{{ stats.bids.active }}</div>
                                <div class="text-xs text-muted-foreground">Active</div>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-blue-600">{{ stats.bids.completed }}</div>
                                <div class="text-xs text-muted-foreground">Completed</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Full Disclosure -->
                <Card class="relative overflow-hidden">
                    <CardHeader class="pb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="text-sm flex items-center gap-2">
                                    <svg class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Full Disclosure
                                </CardTitle>
                            </div>
                            <Button variant="ghost" size="sm" @click="navigateTo('/disclosures')">
                                View
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-foreground">
                                <Skeleton v-if="isLoading" class="h-7 w-12 mx-auto" />
                                <span v-else>{{ stats.disclosure.total }}</span>
                            </div>
                            <div class="text-xs text-muted-foreground">Total Documents</div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-center">
                            <div>
                                <div class="text-sm font-semibold text-green-600">{{ stats.disclosure.published }}</div>
                                <div class="text-xs text-muted-foreground">Published</div>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-yellow-600">{{ stats.disclosure.pending }}</div>
                                <div class="text-xs text-muted-foreground">Pending</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Tourism -->
                <Card class="relative overflow-hidden">
                    <CardHeader class="pb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="text-sm flex items-center gap-2">
                                    <svg class="h-4 w-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Tourism
                                </CardTitle>
                            </div>
                            <Button variant="ghost" size="sm" @click="navigateTo('/tourism')">
                                View
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-foreground">
                                <Skeleton v-if="isLoading" class="h-7 w-12 mx-auto" />
                                <span v-else>{{ stats.tourism.total }}</span>
                            </div>
                            <div class="text-xs text-muted-foreground">Total Items</div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-center">
                            <div>
                                <div class="text-sm font-semibold text-green-600">{{ stats.tourism.active }}</div>
                                <div class="text-xs text-muted-foreground">Active</div>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-purple-600">{{ stats.tourism.featured }}</div>
                                <div class="text-xs text-muted-foreground">Featured</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Awards & Recognition -->
                <Card class="relative overflow-hidden">
                    <CardHeader class="pb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="text-sm flex items-center gap-2">
                                    <svg class="h-4 w-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.048 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                    </svg>
                                    Awards & Recognition
                                </CardTitle>
                            </div>
                            <Button variant="ghost" size="sm" @click="navigateTo('/awards')">
                                View
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-foreground">
                                <Skeleton v-if="isLoading" class="h-7 w-12 mx-auto" />
                                <span v-else>{{ stats.awards.total }}</span>
                            </div>
                            <div class="text-xs text-muted-foreground">Total Awards</div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-center">
                            <div>
                                <div class="text-sm font-semibold text-green-600">{{ stats.awards.given }}</div>
                                <div class="text-xs text-muted-foreground">Given</div>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-yellow-600">{{ stats.awards.pending }}</div>
                                <div class="text-xs text-muted-foreground">Pending</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Bottom Section - 3 Columns -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Sangguniang Bayan -->
                <Card class="relative overflow-hidden">
                    <CardHeader class="pb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="text-sm flex items-center gap-2">
                                    <svg class="h-4 w-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    Sangguniang Bayan
                                </CardTitle>
                            </div>
                            <Button variant="ghost" size="sm" @click="navigateTo('/sanggunian')">
                                View
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-foreground">
                                <Skeleton v-if="isLoading" class="h-7 w-12 mx-auto" />
                                <span v-else>{{ stats.sanggunian.total }}</span>
                            </div>
                            <div class="text-xs text-muted-foreground">Total Members</div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-center">
                            <div>
                                <div class="text-sm font-semibold text-green-600">{{ stats.sanggunian.active }}</div>
                                <div class="text-xs text-muted-foreground">Active</div>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-blue-600">{{ stats.sanggunian.completed }}</div>
                                <div class="text-xs text-muted-foreground">Completed</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Ordinance & Resolution -->
                <Card class="relative overflow-hidden">
                    <CardHeader class="pb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="text-sm flex items-center gap-2">
                                    <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Ordinance & Resolution
                                </CardTitle>
                            </div>
                            <Button variant="ghost" size="sm" @click="navigateTo('/ordinance')">
                                View
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-foreground">
                                <Skeleton v-if="isLoading" class="h-7 w-12 mx-auto" />
                                <span v-else>{{ stats.ordinance.total }}</span>
                            </div>
                            <div class="text-xs text-muted-foreground">Total Documents</div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-center">
                            <div>
                                <div class="text-sm font-semibold text-green-600">{{ stats.ordinance.passed }}</div>
                                <div class="text-xs text-muted-foreground">Passed</div>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-yellow-600">{{ stats.ordinance.pending }}</div>
                                <div class="text-xs text-muted-foreground">Pending</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- User Management -->
                <Card class="relative overflow-hidden">
                    <CardHeader class="pb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="text-sm flex items-center gap-2">
                                    <svg class="h-4 w-4 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                    User Management
                                </CardTitle>
                            </div>
                            <Button variant="ghost" size="sm" @click="navigateTo('/users')">
                                View
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-foreground">
                                <Skeleton v-if="isLoading" class="h-7 w-12 mx-auto" />
                                <span v-else>{{ stats.users.total }}</span>
                            </div>
                            <div class="text-xs text-muted-foreground">Total Users</div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-center">
                            <div>
                                <div class="text-sm font-semibold text-green-600">{{ stats.users.active }}</div>
                                <div class="text-xs text-muted-foreground">Active</div>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-blue-600">{{ stats.users.new }}</div>
                                <div class="text-xs text-muted-foreground">New</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>