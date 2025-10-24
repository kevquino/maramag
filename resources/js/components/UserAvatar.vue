<!-- components/UserAvatar.vue -->
<script setup lang="ts">
import { User } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
  user: {
    name: string;
    avatar?: string | null;
  };
  size?: 'sm' | 'md' | 'lg' | 'xl';
  showTooltip?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  showTooltip: false
});

const sizeClasses = {
  sm: 'w-8 h-8 text-xs',
  md: 'w-10 h-10 text-sm',
  lg: 'w-12 h-12 text-base',
  xl: 'w-16 h-16 text-lg'
};

const getInitials = (name: string): string => {
  if (!name) return '?';
  return name
    .split(' ')
    .map(part => part.charAt(0).toUpperCase())
    .slice(0, 2)
    .join('');
};

const getAvatarUrl = (avatar: string | null | undefined): string | undefined => {
  if (!avatar) return undefined;
  if (avatar.startsWith('http')) return avatar;
  if (avatar.startsWith('/images/')) return avatar;
  return `/storage/${avatar}`;
};

const avatarUrl = computed(() => getAvatarUrl(props.user.avatar));
const initials = computed(() => getInitials(props.user.name));
</script>

<template>
  <div class="relative">
    <div
      v-if="avatarUrl"
      class="rounded-full bg-cover bg-center border-2 border-border shadow-sm"
      :class="sizeClasses[size]"
      :style="{ backgroundImage: `url(${avatarUrl})` }"
    />
    <div
      v-else
      class="rounded-full bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center text-white font-semibold border-2 border-border shadow-sm"
      :class="sizeClasses[size]"
    >
      {{ initials }}
    </div>
  </div>
</template>