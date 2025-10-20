<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { router } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import WelcomeCard from './shared/WelcomeCard.vue';
import type { BadgeCounts, SystemStats, RecentActivity, DashboardModule, QuickStat, User } from '@/types';

interface Props {
  user: User;
  badgeCounts: BadgeCounts;
}

const props = defineProps<Props>();

const systemStats = ref<SystemStats | null>(null);
const recentActivities = ref<RecentActivity[]>([]);
const loading = ref(true);

const navigateTo = (route: string) => {
  router.visit(route);
};

const fetchSystemStats = async () => {
  try {
    const response = await fetch('/api/dashboard/system-stats');
    const data = await response.json();
    systemStats.value = data;
  } catch (error) {
    console.error('Failed to fetch system stats:', error);
  }
};

const fetchRecentActivities = async () => {
  try {
    const response = await fetch('/api/dashboard/recent-activities?limit=10');
    const data = await response.json();
    recentActivities.value = data;
  } catch (error) {
    console.error('Failed to fetch recent activities:', error);
  }
};

onMounted(async () => {
  await Promise.all([fetchSystemStats(), fetchRecentActivities()]);
  loading.value = false;
});

// SuperAdmin specific modules
const adminModules = computed((): DashboardModule[] => [
  {
    title: 'User Management',
    description: 'Manage all system users and permissions',
    icon: '👥',
    route: '/user-management',
    color: 'from-purple-500 to-pink-600',
    bgColor: 'bg-gradient-to-r from-purple-500 to-pink-600',
    stats: `${props.badgeCounts.users?.total || 0} Users`
  },
  {
    title: 'System Analytics',
    description: 'Comprehensive system usage reports',
    icon: '📊',
    route: '/analytics',
    color: 'from-blue-500 to-cyan-600',
    bgColor: 'bg-gradient-to-r from-blue-500 to-cyan-600',
    stats: 'Reports'
  },
  {
    title: 'Audit Logs',
    description: 'Monitor all system activities',
    icon: '📋',
    route: '/activity-logs',
    color: 'from-orange-500 to-red-600',
    bgColor: 'bg-gradient-to-r from-orange-500 to-red-600',
    stats: `${props.badgeCounts.activity_logs || 0} Logs`
  },
  {
    title: 'System Settings',
    description: 'Configure system-wide settings',
    icon: '⚙️',
    route: '/settings',
    color: 'from-gray-500 to-gray-700',
    bgColor: 'bg-gradient-to-r from-gray-500 to-gray-700',
    stats: 'Settings'
  },
  {
    title: 'News Management',
    description: 'Manage all news articles',
    icon: '📰',
    route: '/news',
    color: 'from-blue-500 to-blue-600',
    bgColor: 'bg-gradient-to-r from-blue-500 to-blue-600',
    stats: `${props.badgeCounts.news?.total || 0} Articles`
  },
  {
    title: 'Bids & Awards',
    description: 'Oversee procurement processes',
    icon: '⚖️',
    route: '/bids-awards',
    color: 'from-green-500 to-green-600',
    bgColor: 'bg-gradient-to-r from-green-500 to-green-600',
    stats: `${props.badgeCounts.bids_awards?.total || 0} Bids`
  },
]);

const quickStats = computed((): QuickStat[] => [
  {
    label: 'Total Users',
    value: systemStats.value?.users?.total?.toString() || '0',
    change: '+5 this week',
    icon: '👥',
    color: 'text-blue-600',
    bgColor: 'bg-blue-100 dark:bg-blue-900'
  },
  {
    label: 'Active Modules',
    value: '12',
    change: 'All systems',
    icon: '⚙️',
    color: 'text-green-600',
    bgColor: 'bg-green-100 dark:bg-green-900'
  },
  {
    label: 'System Health',
    value: 'Optimal',
    change: 'No issues',
    icon: '💚',
    color: 'text-emerald-600',
    bgColor: 'bg-emerald-100 dark:bg-emerald-900'
  },
  {
    label: 'Storage',
    value: '2.3GB',
    change: '65% used',
    icon: '💾',
    color: 'text-purple-600',
    bgColor: 'bg-purple-100 dark:bg-purple-900'
  },
]);
</script>

<template>
  <div class="flex h-full flex-1 flex-col gap-6 p-6">
    <!-- Welcome Card with SuperAdmin action -->
    <WelcomeCard :user="user">
      <template #welcome-action>
        <Button 
          class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700"
          @click="navigateTo('/user-management')"
        >
          👥 Manage System Users
        </Button>
      </template>
    </WelcomeCard>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- SuperAdmin Quick Stats -->
    <div v-else class="grid gap-6 lg:grid-cols-4">
      <Card 
        v-for="stat in quickStats" 
        :key="stat.label"
        class="text-center group cursor-pointer hover:shadow-lg transition-shadow"
      >
        <CardContent class="pt-6">
          <div class="h-12 w-12 rounded-full mx-auto flex items-center justify-center text-xl mb-3" :class="stat.bgColor">
            {{ stat.icon }}
          </div>
          <div class="text-2xl font-bold mb-1">{{ stat.value }}</div>
          <div class="text-sm font-medium text-muted-foreground mb-1">{{ stat.label }}</div>
          <div class="text-xs text-green-600">{{ stat.change }}</div>
        </CardContent>
      </Card>
    </div>

    <!-- SuperAdmin Modules -->
    <div>
      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-2xl font-bold">System Administration</h2>
          <p class="text-muted-foreground">Full system control and management</p>
        </div>
        <Badge variant="outline" class="text-sm">
          {{ adminModules.length }} modules
        </Badge>
      </div>
      
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <Card 
          v-for="module in adminModules" 
          :key="module.title"
          class="group cursor-pointer transition-all duration-300 hover:shadow-lg hover:scale-105 border-2 hover:border-blue-300 dark:hover:border-blue-600"
          @click="navigateTo(module.route)"
        >
          <CardHeader class="pb-3">
            <div class="flex items-center justify-between">
              <div class="h-12 w-12 rounded-xl bg-gradient-to-br flex items-center justify-center text-white text-xl" :class="module.color">
                {{ module.icon }}
              </div>
              <Badge class="text-white" :class="module.bgColor">
                {{ module.stats }}
              </Badge>
            </div>
          </CardHeader>
          <CardContent>
            <CardTitle class="text-lg mb-2 group-hover:text-blue-600 transition-colors">
              {{ module.title }}
            </CardTitle>
            <CardDescription class="text-sm leading-relaxed">
              {{ module.description }}
            </CardDescription>
            <div class="mt-4 flex items-center justify-between">
              <Button variant="ghost" size="sm" class="group-hover:text-blue-600 transition-colors">
                Access →
              </Button>
              <div class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Recent Activities -->
    <div class="grid gap-6 lg:grid-cols-2">
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <span class="text-lg">📋</span>
            Recent System Activities
          </CardTitle>
          <CardDescription>
            Latest actions across the system
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div 
              v-for="activity in recentActivities" 
              :key="activity.id"
              class="flex items-start gap-4 p-3 rounded-lg border"
            >
              <div class="h-10 w-10 rounded-full bg-muted flex items-center justify-center flex-shrink-0">
                <span class="text-lg">{{ activity.icon }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between">
                  <div>
                    <p class="font-medium text-sm">{{ activity.action }}</p>
                    <p class="text-sm text-muted-foreground">{{ activity.description }}</p>
                    <p class="text-xs text-muted-foreground mt-1">by {{ activity.user }}</p>
                  </div>
                  <Badge variant="outline" class="text-xs flex-shrink-0 ml-2">
                    {{ activity.time }}
                  </Badge>
                </div>
              </div>
            </div>
            
            <div v-if="recentActivities.length === 0" class="text-center py-6 text-muted-foreground">
              <p class="text-sm">No recent activities found</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- System Overview -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <span class="text-lg">⚙️</span>
            System Overview
          </CardTitle>
          <CardDescription>
            Current system status and metrics
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div class="flex items-center justify-between p-3 rounded-lg bg-green-50 dark:bg-green-900/20">
              <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-green-100 dark:bg-green-800 flex items-center justify-center">
                  <span class="text-green-600">✅</span>
                </div>
                <div>
                  <p class="text-sm font-medium">System Status</p>
                  <p class="text-xs text-muted-foreground">All systems operational</p>
                </div>
              </div>
              <Badge variant="outline" class="bg-green-100 text-green-700">
                Optimal
              </Badge>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div class="text-center p-4 rounded-lg border">
                <div class="text-2xl font-bold text-blue-600">{{ systemStats?.users?.total || 0 }}</div>
                <div class="text-xs text-muted-foreground">Total Users</div>
              </div>
              <div class="text-center p-4 rounded-lg border">
                <div class="text-2xl font-bold text-green-600">{{ systemStats?.content?.published_news || 0 }}</div>
                <div class="text-xs text-muted-foreground">Published News</div>
              </div>
              <div class="text-center p-4 rounded-lg border">
                <div class="text-2xl font-bold text-purple-600">{{ systemStats?.content?.bids_awards || 0 }}</div>
                <div class="text-xs text-muted-foreground">Active Bids</div>
              </div>
              <div class="text-center p-4 rounded-lg border">
                <div class="text-2xl font-bold text-orange-600">{{ systemStats?.system?.activities || 0 }}</div>
                <div class="text-xs text-muted-foreground">Activities</div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>