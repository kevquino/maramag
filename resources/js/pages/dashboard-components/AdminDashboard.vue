<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import WelcomeCard from './shared/WelcomeCard.vue';
import type { BadgeCounts, DashboardModule, QuickStat, User } from '@/types';

interface Props {
  user: User;
  badgeCounts: BadgeCounts;
}

const props = defineProps<Props>();

const navigateTo = (route: string) => {
  router.visit(route);
};

// Admin specific modules based on permissions
const adminModules = computed((): DashboardModule[] => {
  const modules: DashboardModule[] = [];
  
  if (props.user?.permissions?.includes('news')) {
    modules.push({
      title: 'News Management',
      description: 'Create and manage news articles',
      icon: '📰',
      route: '/news',
      color: 'from-blue-500 to-blue-600',
      bgColor: 'bg-gradient-to-r from-blue-500 to-blue-600',
      stats: `${props.badgeCounts.news?.total || 0} Articles`
    });
  }
  
  if (props.user?.permissions?.includes('bids_awards')) {
    modules.push({
      title: 'Bids & Awards',
      description: 'Manage procurement processes',
      icon: '⚖️',
      route: '/bids-awards',
      color: 'from-green-500 to-green-600',
      bgColor: 'bg-gradient-to-r from-green-500 to-green-600',
      stats: `${props.badgeCounts.bids_awards?.total || 0} Bids`
    });
  }
  
  if (props.user?.permissions?.includes('tourism')) {
    modules.push({
      title: 'Tourism',
      description: 'Promote local attractions',
      icon: '🏞️',
      route: '/tourism',
      color: 'from-orange-500 to-orange-600',
      bgColor: 'bg-gradient-to-r from-orange-500 to-orange-600',
      stats: `${props.badgeCounts.tourism?.total || 0} Packages`
    });
  }

  if (props.user?.permissions?.includes('business_permit')) {
    modules.push({
      title: 'Business Permits',
      description: 'Process business applications',
      icon: '🏢',
      route: '/business-permit',
      color: 'from-purple-500 to-purple-600',
      bgColor: 'bg-gradient-to-r from-purple-500 to-purple-600',
      stats: 'Applications'
    });
  }

  if (props.user?.permissions?.includes('user_management')) {
    modules.push({
      title: 'User Management',
      description: 'Manage system users',
      icon: '👤',
      route: '/user-management',
      color: 'from-pink-500 to-pink-600',
      bgColor: 'bg-gradient-to-r from-pink-500 to-pink-600',
      stats: `${props.badgeCounts.users?.total || 0} Users`
    });
  }

  return modules;
});

const quickStats = computed((): QuickStat[] => {
  const stats: QuickStat[] = [];
  
  if (props.user?.permissions?.includes('news')) {
    stats.push({
      label: 'News Articles',
      value: (props.badgeCounts.news?.total || 0).toString(),
      change: `${props.badgeCounts.news?.draft || 0} drafts`,
      icon: '📰',
      color: 'text-blue-600',
      bgColor: 'bg-blue-100 dark:bg-blue-900'
    });
  }
  
  if (props.user?.permissions?.includes('bids_awards')) {
    stats.push({
      label: 'Active Bids',
      value: (props.badgeCounts.bids_awards?.active || 0).toString(),
      change: `${props.badgeCounts.bids_awards?.total || 0} total`,
      icon: '⚖️',
      color: 'text-green-600',
      bgColor: 'bg-green-100 dark:bg-green-900'
    });
  }
  
  if (props.user?.permissions?.includes('tourism')) {
    stats.push({
      label: 'Tourism Packages',
      value: (props.badgeCounts.tourism?.total || 0).toString(),
      change: `${props.badgeCounts.tourism?.featured || 0} featured`,
      icon: '🏞️',
      color: 'text-orange-600',
      bgColor: 'bg-orange-100 dark:bg-orange-900'
    });
  }

  if (props.user?.permissions?.includes('user_management')) {
    stats.push({
      label: 'System Users',
      value: (props.badgeCounts.users?.total || 0).toString(),
      change: `${props.badgeCounts.users?.active || 0} active`,
      icon: '👥',
      color: 'text-pink-600',
      bgColor: 'bg-pink-100 dark:bg-pink-900'
    });
  }
  
  return stats;
});
</script>

<template>
  <div class="flex h-full flex-1 flex-col gap-6 p-6">
    <!-- Welcome Card with Admin action -->
    <WelcomeCard :user="user">
      <template #welcome-action>
        <Button 
          class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700"
          @click="navigateTo(adminModules[0]?.route || '/news/create')"
          v-if="adminModules.length > 0"
        >
          📝 Quick Start
        </Button>
        <Button variant="outline" class="w-full" v-else>
          👤 View Profile
        </Button>
      </template>
    </WelcomeCard>

    <!-- Admin Quick Stats -->
    <div class="grid gap-6 lg:grid-cols-4">
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

      <!-- Empty state if no stats -->
      <Card v-if="quickStats.length === 0" class="text-center">
        <CardContent class="pt-6">
          <div class="h-12 w-12 rounded-full mx-auto flex items-center justify-center text-xl mb-3 bg-gray-100">
            📊
          </div>
          <div class="text-2xl font-bold mb-1">0</div>
          <div class="text-sm font-medium text-muted-foreground mb-1">No Data</div>
          <div class="text-xs text-muted-foreground">No permissions granted</div>
        </CardContent>
      </Card>
    </div>

    <!-- Admin Modules -->
    <div>
      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-2xl font-bold">Management Modules</h2>
          <p class="text-muted-foreground">Your authorized management tools</p>
        </div>
        <Badge variant="outline" class="text-sm">
          {{ adminModules.length }} available
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
        
        <!-- Empty State -->
        <Card 
          v-if="adminModules.length === 0"
          class="border-dashed border-2 text-center py-12"
        >
          <CardContent>
            <div class="h-16 w-16 rounded-full bg-muted flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl">🔒</span>
            </div>
            <h3 class="font-semibold mb-2">No Modules Available</h3>
            <p class="text-sm text-muted-foreground mb-4">
              You don't have access to any modules yet.
            </p>
            <Button variant="outline">
              Request Access
            </Button>
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>