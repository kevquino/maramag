<script setup lang="ts">
import { Button } from "@/components/ui/button"

interface Props {
  currentPage: number
  total: number
  pageSize: number
  loading?: boolean
  onPrevious: () => void
  onNext: () => void
}

defineProps<Props>()
</script>

<template>
  <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 border-t">
    <div class="text-sm text-muted-foreground text-center sm:text-left">
      Page {{ currentPage }} of {{ Math.ceil(total / pageSize) }}
    </div>
    <div class="flex items-center space-x-2">
      <Button
        variant="outline"
        size="sm"
        :disabled="currentPage <= 1 || loading"
        @click="onPrevious"
        class="w-20 sm:w-auto"
      >
        Previous
      </Button>
      <Button
        variant="outline"
        size="sm"
        :disabled="currentPage >= Math.ceil(total / pageSize) || loading"
        @click="onNext"
        class="w-20 sm:w-auto"
      >
        Next
      </Button>
    </div>
  </div>
</template>