<script setup lang="ts">
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from "lucide-vue-next"
import { computed } from "vue"

import { Button } from "@/components/ui/button"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"

interface Props {
  currentPage: number
  total: number
  pageSize: number
  loading?: boolean
  onPageChange?: (page: number) => void
  onPageSizeChange?: (size: number) => void
  showPageSizeOptions?: boolean
  pageSizeOptions?: number[]
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  showPageSizeOptions: true,
  pageSizeOptions: () => [10, 20, 50, 100]
})

const emit = defineEmits<{
  'page-change': [page: number]
  'page-size-change': [size: number]
}>()

// Pagination calculations
const totalPages = computed(() => Math.ceil(props.total / props.pageSize))
const startItem = computed(() => (props.currentPage - 1) * props.pageSize + 1)
const endItem = computed(() => Math.min(props.currentPage * props.pageSize, props.total))

// Generate page numbers for pagination
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

// Event handlers
const handlePageChange = (page: number) => {
  if (!props.loading) {
    emit('page-change', page)
  }
}

// Fix for Select component - handle string values properly
const handlePageSizeChange = (value: string | null) => {
  if (!props.loading && value) {
    const size = parseInt(value, 10)
    emit('page-size-change', size)
  }
}

const handleFirstPage = () => {
  if (props.currentPage > 1 && !props.loading) {
    handlePageChange(1)
  }
}

const handlePreviousPage = () => {
  if (props.currentPage > 1 && !props.loading) {
    handlePageChange(props.currentPage - 1)
  }
}

const handleNextPage = () => {
  if (props.currentPage < totalPages.value && !props.loading) {
    handlePageChange(props.currentPage + 1)
  }
}

const handleLastPage = () => {
  if (props.currentPage < totalPages.value && !props.loading) {
    handlePageChange(totalPages.value)
  }
}
</script>

<template>
  <div class="bg-card rounded-lg border shadow-sm">
    <div class="flex flex-col sm:flex-row items-center justify-between p-4 gap-4">
      <!-- Page Info -->
      <div class="text-sm text-muted-foreground">
        Showing {{ startItem }} to {{ endItem }} of {{ total }} entries
      </div>

      <!-- Pagination Controls -->
      <div class="flex items-center space-x-2">
        <!-- Rows per page -->
        <div v-if="showPageSizeOptions" class="flex items-center space-x-2">
          <span class="text-sm text-muted-foreground whitespace-nowrap">Rows per page:</span>
          <Select 
            :model-value="pageSize.toString()" 
            @update:model-value="handlePageSizeChange"
            :disabled="loading"
          >
            <SelectTrigger class="w-20 h-8">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem 
                v-for="option in pageSizeOptions" 
                :key="option" 
                :value="option.toString()"
              >
                {{ option }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Page Navigation -->
        <div class="flex items-center space-x-1">
          <!-- First Page -->
          <Button
            variant="outline"
            size="sm"
            class="h-8 w-8 p-0"
            :disabled="currentPage <= 1 || loading"
            @click="handleFirstPage"
          >
            <ChevronsLeft class="h-4 w-4" />
          </Button>

          <!-- Previous Page -->
          <Button
            variant="outline"
            size="sm"
            class="h-8 w-8 p-0"
            :disabled="currentPage <= 1 || loading"
            @click="handlePreviousPage"
          >
            <ChevronLeft class="h-4 w-4" />
          </Button>

          <!-- Page Numbers -->
          <div class="flex items-center space-x-1 mx-2">
            <Button
              v-for="(pageNum, index) in visiblePages"
              :key="index"
              variant="ghost"
              size="sm"
              class="h-8 w-8 p-0 text-xs"
              :class="[
                pageNum === currentPage 
                  ? 'bg-primary text-primary-foreground hover:bg-primary/90' 
                  : 'hover:bg-accent hover:text-accent-foreground'
              ]"
              @click="pageNum !== '...' ? handlePageChange(pageNum as number) : null"
              :disabled="loading || pageNum === '...'"
            >
              {{ pageNum }}
            </Button>
          </div>

          <!-- Next Page -->
          <Button
            variant="outline"
            size="sm"
            class="h-8 w-8 p-0"
            :disabled="currentPage >= totalPages || loading"
            @click="handleNextPage"
          >
            <ChevronRight class="h-4 w-4" />
          </Button>

          <!-- Last Page -->
          <Button
            variant="outline"
            size="sm"
            class="h-8 w-8 p-0"
            :disabled="currentPage >= totalPages || loading"
            @click="handleLastPage"
          >
            <ChevronsRight class="h-4 w-4" />
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>