<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import WelcomeCard from './shared/WelcomeCard.vue';
import type { BadgeCounts, User } from '@/types';

interface Props {
  user: User;
  badgeCounts: BadgeCounts;
}

const props = defineProps<Props>();

const navigateTo = (route: string) => {
  router.visit(route);
};

// Staff specific modules based on permissions
const staffModules = computed(() => {
  const modules = [];
  
  if (props.user?.permissions?.includes('news')) {
    modules.push({
      title: 'News Management',
      description: 'Create and manage news articles',
      icon: '📰',
      route: '/news',
      color: 'from-blue-500 to-blue-600',
      stats: 'Manage'
    });
  }
  
  if (props.user?.permissions?.includes('tourism')) {
    modules.push({
      title: 'Tourism',
      description: 'Promote local attractions',
      icon: '🏞️',
      route: '/tourism',
      color: 'from-orange-500 to-orange-600',
      stats: 'Promote'
    });
  }

  if (props.user?.permissions?.includes('business_permit')) {
    modules.push({
      title: 'Business Permits',
      description: 'Process business applications',
      icon: '🏢',
      route: '/business-permit',
      color: 'from-purple-500 to-purple-600',
      stats: 'Process'
    });
  }

  return modules;
});

const pendingTasks = computed(() => [
  {
    id: 1,
    title: 'Review News Articles',
    description: '2 articles pending review',
    icon: '📰',
    route: '/news',
    priority: 'high'
  },
  {
    id: 2,
    title: 'Process Permit Applications',
    description: '5 applications waiting',
    icon: '🏢',
    route: '/business-permit',
    priority: 'medium'
  },
]);
</script>

<template>
  <div class="flex h-full flex-1 flex-col gap-6 p-6">
    <!-- Welcome Card -->
    <WelcomeCard :user="user" />

    <!-- Pending Tasks -->
    <Card>
      <CardHeader>
        <CardTitle class="flex items-center gap-2">
          <span class="text-lg">📋</span>
          Pending Tasks
        </CardTitle>
        <CardDescription>
          Tasks requiring your attention
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div class="space-y-4">
          <div 
            v-for="task in pendingTasks" 
            :key="task.id"
            class="flex items-center justify-between p-4 rounded-lg border group cursor-pointer hover:bg-muted/50 transition-colors"
            @click="navigateTo(task.route)"
          >
            <div class="flex items-center gap-4">
              <div class="h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                <span class="text-lg">{{ task.icon }}</span>
              </div>
              <div>
                <p class="font-medium">{{ task.title }}</p>
                <p class="text-sm text-muted-foreground">{{ task.description }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <Badge :variant="task.priority === 'high' ? 'destructive' : 'secondary'">
                {{ task.priority }}
              </Badge>
              <Button variant="ghost" size="sm">
                View →
              </Button>
            </div>
          </div>
          
          <div v-if="pendingTasks.length === 0" class="text-center py-8 text-muted-foreground">
            <div class="h-12 w-12 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center mx-auto mb-3">
              <span class="text-lg">✅</span>
            </div>
            <p class="font-medium">All caught up!</p>
            <p class="text-sm">No pending tasks at the moment.</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Staff Modules -->
    <div>
      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-2xl font-bold">Your Tools</h2>
          <p class="text-muted-foreground">Access your assigned modules</p>
        </div>
        <Badge variant="outline" class="text-sm">
          {{ staffModules.length }} available
        </Badge>
      </div>
      
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <Card 
          v-for="module in staffModules" 
          :key="module.title"
          class="group cursor-pointer transition-all duration-300 hover:shadow-lg border-2 hover:border-blue-300 dark:hover:border-blue-600"
          @click="navigateTo(module.route)"
        >
          <CardHeader class="pb-3">
            <div class="flex items-center justify-between">
              <div class="h-12 w-12 rounded-xl bg-gradient-to-br flex items-center justify-center text-white text-xl" :class="module.color">
                {{ module.icon }}
              </div>
              <Badge variant="secondary">
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
            <div class="mt-4">
              <Button variant="ghost" size="sm" class="group-hover:text-blue-600 transition-colors">
                Open Tool →
              </Button>
            </div>
          </CardContent>
        </Card>
        
        <!-- Empty State -->
        <Card 
          v-if="staffModules.length === 0"
          class="border-dashed border-2 text-center py-12 col-span-full"
        >
          <CardContent>
            <div class="h-16 w-16 rounded-full bg-muted flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl">🔒</span>
            </div>
            <h3 class="font-semibold mb-2">No Tools Assigned</h3>
            <p class="text-sm text-muted-foreground mb-4">
              Contact administrator to get access to modules.
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