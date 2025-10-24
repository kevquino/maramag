<!-- resources/js/components/UserRoleBadge.vue -->
<script setup lang="ts">
import { Badge } from '@/components/ui/badge';

interface Props {
  role: string;
  size?: 'sm' | 'md';
}

withDefaults(defineProps<Props>(), {
  size: 'md'
});

const getRoleVariant = (role: string) => {
  switch (role) {
    case 'superadmin': return 'destructive';
    case 'admin': return 'destructive';
    case 'PIO Officer': return 'default';
    case 'PIO Staff': return 'secondary';
    default: return 'outline';
  }
};
</script>

<template>
  <Badge :variant="getRoleVariant(role)" :class="size === 'sm' ? 'text-xs' : 'text-sm'">
    {{ role }}
  </Badge>
</template>