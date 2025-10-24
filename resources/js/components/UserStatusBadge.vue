<!-- components/UserStatusBadge.vue -->
<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { CheckCircle, XCircle } from 'lucide-vue-next';

interface Props {
  isActive: boolean;
  size?: 'sm' | 'md';
  showIcon?: boolean;
}

withDefaults(defineProps<Props>(), {
  size: 'md',
  showIcon: true
});

const getStatusVariant = (isActive: boolean) => {
  return isActive ? 'default' : 'secondary';
};
</script>

<template>
  <Badge :variant="getStatusVariant(isActive)" :class="size === 'sm' ? 'text-xs' : 'text-sm'">
    <component 
      :is="isActive ? CheckCircle : XCircle" 
      v-if="showIcon"
      class="h-3 w-3 mr-1" 
    />
    {{ isActive ? 'Active' : 'Inactive' }}
  </Badge>
</template>