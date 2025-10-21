<script setup lang="ts">
import {
  Pagination,
  PaginationContent,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from "@/components/ui/pagination"
import { Button } from "@/components/ui/button"
import { computed } from "vue"

interface Props {
  currentPage: number
  total: number
  pageSize: number
  loading?: boolean
  onPageChange?: (page: number) => void
  onPrevious?: () => void
  onNext?: () => void
}

const props = defineProps<Props>()

const totalPages = computed(() => Math.ceil(props.total / props.pageSize))

const handlePageChange = (page: number) => {
  if (props.onPageChange && page >= 1 && page <= totalPages.value) {
    props.onPageChange(page)
  }
}

const handlePrevious = () => {
  if (props.onPrevious) {
    props.onPrevious()
  } else if (props.currentPage > 1) {
    handlePageChange(props.currentPage - 1)
  }
}

const handleNext = () => {
  if (props.onNext) {
    props.onNext()
  } else if (props.currentPage < totalPages.value) {
    handlePageChange(props.currentPage + 1)
  }
}

// Generate visible page numbers
const visiblePages = computed(() => {
  const pages: (number | string)[] = []
  const current = props.currentPage
  const total = totalPages.value
  
  if (total <= 7) {
    // Show all pages if total pages is small
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    // Always show first page
    pages.push(1)
    
    // Calculate range around current page
    let start = Math.max(2, current - 1)
    let end = Math.min(total - 1, current + 1)
    
    // Add ellipsis after first page if needed
    if (start > 2) {
      pages.push('...')
    }
    
    // Add pages around current page
    for (let i = start; i <= end; i++) {
      if (i > 1 && i < total) {
        pages.push(i)
      }
    }
    
    // Add ellipsis before last page if needed
    if (end < total - 1) {
      pages.push('...')
    }
    
    // Always show last page
    if (total > 1) {
      pages.push(total)
    }
  }
  
  return pages
})
</script>

<template>
  <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 border-t">
    <!-- Page info -->
    <div class="text-sm text-muted-foreground text-center sm:text-left">
      Page {{ currentPage }} of {{ totalPages }} ({{ total }} total items)
    </div>

    <!-- Pagination controls -->
    <div class="flex items-center space-x-1">
      <!-- Previous button -->
      <Button
        variant="outline"
        size="sm"
        :disabled="currentPage <= 1 || loading"
        @click="handlePrevious"
        class="w-20 sm:w-auto"
      >
        Previous
      </Button>

      <!-- Page numbers -->
      <div class="flex items-center space-x-1 mx-2">
        <Button
          v-for="(page, index) in visiblePages"
          :key="index"
          variant="ghost"
          size="sm"
          class="h-9 w-9 p-0 hidden sm:inline-flex"
          :class="[
            page === currentPage 
              ? 'bg-primary text-primary-foreground hover:bg-primary/90' 
              : 'hover:bg-accent hover:text-accent-foreground'
          ]"
          @click="page !== '...' ? handlePageChange(page as number) : null"
          :disabled="loading || page === '...'"
        >
          {{ page }}
        </Button>
      </div>

      <!-- Next button -->
      <Button
        variant="outline"
        size="sm"
        :disabled="currentPage >= totalPages || loading"
        @click="handleNext"
        class="w-20 sm:w-auto"
      >
        Next
      </Button>
    </div>
  </div>
</template>