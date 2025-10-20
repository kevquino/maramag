<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { router } from '@inertiajs/vue3';
import WelcomeCard from './shared/WelcomeCard.vue';
import type { User } from '@/types';

interface Props {
  user: User;
}

const props = defineProps<Props>();

const navigateTo = (route: string) => {
  router.visit(route);
};

const userResources = [
  {
    title: 'Public News',
    description: 'Browse latest municipal news and announcements',
    icon: '📰',
    route: '/news',
    color: 'from-blue-500 to-blue-600'
  },
  {
    title: 'Tourism Information',
    description: 'Discover local attractions and travel guides',
    icon: '🏞️',
    route: '/tourism',
    color: 'from-orange-500 to-orange-600'
  },
  {
    title: 'Bids & Awards',
    description: 'View current procurement opportunities',
    icon: '⚖️',
    route: '/bids-awards',
    color: 'from-green-500 to-green-600'
  },
  {
    title: 'Public Documents',
    description: 'Access full disclosure documents',
    icon: '📋',
    route: '/full-disclosure',
    color: 'from-indigo-500 to-indigo-600'
  },
];

const quickLinks = [
  {
    title: 'Download Forms',
    description: 'Get application forms and templates',
    icon: '📄',
    route: '/forms'
  },
  {
    title: 'Contact Offices',
    description: 'Find department contact information',
    icon: '📞',
    route: '/contacts'
  },
  {
    title: 'Municipal Services',
    description: 'Browse available government services',
    icon: '🏛️',
    route: '/services'
  },
];
</script>

<template>
  <div class="flex h-full flex-1 flex-col gap-6 p-6">
    <!-- Welcome Card -->
    <WelcomeCard :user="user">
      <template #welcome-action>
        <Button variant="outline" class="w-full" @click="navigateTo('/profile')">
          👤 Update Profile
        </Button>
      </template>
    </WelcomeCard>

    <!-- Public Resources -->
    <div>
      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-2xl font-bold">Public Resources</h2>
          <p class="text-muted-foreground">Access municipal information and services</p>
        </div>
        <Badge variant="outline" class="text-sm">
          {{ userResources.length }} resources
        </Badge>
      </div>
      
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <Card 
          v-for="resource in userResources" 
          :key="resource.title"
          class="group cursor-pointer transition-all duration-300 hover:shadow-lg border-2 hover:border-blue-300 dark:hover:border-blue-600"
          @click="navigateTo(resource.route)"
        >
          <CardHeader class="pb-3">
            <div class="h-12 w-12 rounded-xl bg-gradient-to-br flex items-center justify-center text-white text-xl" :class="resource.color">
              {{ resource.icon }}
            </div>
          </CardHeader>
          <CardContent>
            <CardTitle class="text-lg mb-2 group-hover:text-blue-600 transition-colors">
              {{ resource.title }}
            </CardTitle>
            <CardDescription class="text-sm leading-relaxed">
              {{ resource.description }}
            </CardDescription>
            <div class="mt-4">
              <Button variant="ghost" size="sm" class="group-hover:text-blue-600 transition-colors">
                Browse →
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Quick Links & Information -->
    <div class="grid gap-6 lg:grid-cols-2">
      <!-- Quick Links -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <span class="text-lg">🔗</span>
            Quick Links
          </CardTitle>
          <CardDescription>
            Helpful resources and contacts
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-3">
            <div 
              v-for="link in quickLinks" 
              :key="link.title"
              class="flex items-center gap-4 p-3 rounded-lg border group cursor-pointer hover:bg-muted/50 transition-colors"
              @click="navigateTo(link.route)"
            >
              <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                <span class="text-lg">{{ link.icon }}</span>
              </div>
              <div class="flex-1">
                <p class="font-medium text-sm">{{ link.title }}</p>
                <p class="text-xs text-muted-foreground">{{ link.description }}</p>
              </div>
              <Button variant="ghost" size="sm">
                →
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- User Information -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <span class="text-lg">ℹ️</span>
            Your Information
          </CardTitle>
          <CardDescription>
            Account details and status
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
              <span class="text-sm font-medium">Account Status</span>
              <Badge variant="outline" class="bg-green-100 text-green-700">
                Active
              </Badge>
            </div>
            
            <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
              <span class="text-sm font-medium">Member Since</span>
              <span class="text-sm text-muted-foreground">{{ new Date().toLocaleDateString() }}</span>
            </div>
            
            <div class="flex items-center justify-between p-3 rounded-lg bg-muted/50">
              <span class="text-sm font-medium">Last Login</span>
              <span class="text-sm text-muted-foreground">Just now</span>
            </div>
            
            <Button class="w-full" @click="navigateTo('/profile')">
              Manage Account
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>