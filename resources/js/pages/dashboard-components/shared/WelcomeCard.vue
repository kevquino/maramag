<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import type { User } from '@/types';

interface Props {
  user: User;
}

const props = defineProps<Props>();

const currentTime = ref(new Date());
const greeting = computed(() => {
  const hour = currentTime.value.getHours();
  if (hour < 12) return 'Good morning';
  if (hour < 18) return 'Good afternoon';
  return 'Good evening';
});

const navigateTo = (route: string) => {
  router.visit(route);
};

onMounted(() => {
  setInterval(() => {
    currentTime.value = new Date();
  }, 60000);
});
</script>

<template>
  <Card class="lg:col-span-2 relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-950 dark:to-indigo-900 opacity-50"></div>
    <CardHeader class="relative">
      <CardTitle class="text-3xl font-bold">
        {{ greeting }}, {{ user?.name }}!
      </CardTitle>
      <CardDescription class="text-lg">
        Welcome to Municipal Management System
      </CardDescription>
    </CardHeader>
    <CardContent class="relative">
      <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-3">
          <div class="flex items-center gap-3">
            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
              <span class="text-white text-lg">👋</span>
            </div>
            <div>
              <p class="font-semibold">{{ user?.role }}</p>
              <p class="text-sm text-muted-foreground">{{ user?.office }}</p>
            </div>
          </div>
          <p class="text-sm text-muted-foreground">
            Ready to manage municipal operations efficiently.
          </p>
        </div>
        <div class="space-y-2">
          <div class="flex items-center justify-between p-3 rounded-lg bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm">
            <div class="flex items-center gap-2">
              <span class="text-lg">🕒</span>
              <span class="text-sm font-medium">Local Time</span>
            </div>
            <div class="text-right">
              <div class="font-mono font-semibold">
                {{ currentTime.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) }}
              </div>
              <div class="text-xs text-muted-foreground">
                {{ currentTime.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }) }}
              </div>
            </div>
          </div>
          <slot name="welcome-action">
            <!-- Default fallback content -->
            <Button variant="outline" class="w-full" @click="navigateTo('/profile')">
              👤 View Profile
            </Button>
          </slot>
        </div>
      </div>
    </CardContent>
  </Card>
</template>