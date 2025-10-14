<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { computed } from 'vue'

interface Props {
  class?: HTMLAttributes['class']
  variant?: 'default' | 'secondary' | 'destructive' | 'outline' | 'success' | 'warning'
  shape?: 'auto' | 'circle' | 'rounded' | 'square'
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  shape: 'auto'
})

const variantClasses = {
  default: 'bg-blue-500 text-white',
  secondary: 'bg-gray-500 text-white',
  destructive: 'bg-red-500 text-white',
  outline: 'border border-gray-300 bg-white text-gray-700 dark:bg-gray-800 dark:text-gray-300',
  success: 'bg-green-500 text-white',
  warning: 'bg-yellow-500 text-black'
}

// Get the slot content to determine badge value
const slots = defineSlots<{
  default?: () => any
}>()

// Compute the badge value from slot content
const badgeValue = computed(() => {
  if (!slots.default) return ''
  const content = slots.default()
  return content?.[0]?.children || ''
})

// Auto-determine shape based on content length
const computedShape = computed(() => {
  if (props.shape !== 'auto') return props.shape
  
  const value = String(badgeValue.value)
  // If it's a number and less than 10, use circle for single digits
  if (/^\d+$/.test(value)) {
    const num = parseInt(value)
    return num <= 9 ? 'circle' : 'rounded'
  }
  
  // For text or larger numbers, use rounded square
  return value.length <= 2 ? 'circle' : 'rounded'
})

const shapeClasses = {
  circle: 'rounded-full min-w-5 h-5 px-0', // Perfect circle for single digits
  rounded: 'rounded-md min-w-[1.25rem] px-1.5', // Rounded rectangle
  square: 'rounded-none min-w-[1.25rem] px-1.5' // Square rectangle
}
</script>

<template>
  <div
    data-slot="sidebar-menu-badge"
    data-sidebar="menu-badge"
    :class="cn(
      'pointer-events-none absolute right-1 flex items-center justify-center text-xs font-medium tabular-nums select-none transition-all duration-200',
      'peer-hover/menu-button:opacity-80 peer-data-[active=true]/menu-button:opacity-90',
      'peer-data-[size=sm]/menu-button:top-1',
      'peer-data-[size=default]/menu-button:top-1.5',
      'peer-data-[size=lg]/menu-button:top-2.5',
      'group-data-[collapsible=icon]:hidden',
      // Dynamic styling
      'h-5', // Consistent height
      variantClasses[variant],
      shapeClasses[computedShape],
      // Auto width adjustment
      props.class,
    )"
  >
    <slot />
  </div>
</template>