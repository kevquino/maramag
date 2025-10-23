<script setup lang="ts">
import type {
  ColumnDef,
  ColumnFiltersState,
  SortingState,
  VisibilityState,
  PaginationState,
} from "@tanstack/vue-table"
import {
  FlexRender,
  getCoreRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useVueTable,
} from "@tanstack/vue-table"
import { ref, computed, watch, onMounted, onUnmounted } from "vue"

import { Button } from "@/components/ui/button"
import { Checkbox } from "@/components/ui/checkbox"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import { Search, X, Columns, RotateCw, ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, Smartphone, Monitor, Eye, Edit, Trash2, UserX, UserCheck } from "lucide-vue-next"

// Define the interface for card view configuration
export interface CardViewConfig {
  enabled?: boolean
  breakpoint?: number
  fields: {
    [key: string]: {
      label?: string
      component?: any
      format?: (value: any) => string
      class?: string
    }
  }
  actions?: {
    view?: boolean
    edit?: boolean
    delete?: boolean
    status?: boolean
    custom?: Array<{
      label: string
      icon: any
      action: (row: any) => void
      variant?: "default" | "destructive" | "outline" | "secondary" | "ghost" | "link"
    }>
  }
}

interface DataTableProps<TData> {
  data: TData[]
  columns: ColumnDef<TData>[]
  loading?: boolean
  searchQuery?: string
  onSearch?: (value: string) => void
  searchPlaceholder?: string
  enableRowSelection?: boolean
  enableColumnVisibility?: boolean
  enablePagination?: boolean
  pageSize?: number
  pageSizeOptions?: number[]
  onRowClick?: (row: TData) => void
  title?: string
  // External pagination control
  currentPage?: number
  total?: number
  onPageChange?: (page: number) => void
  onPageSizeChange?: (size: number) => void
  // Responsive props
  responsiveBreakpoint?: number
  // Card view props
  cardView?: CardViewConfig
  // Custom actions
  onView?: (row: TData) => void
  onEdit?: (row: TData) => void
  onDelete?: (row: TData) => void
  onStatusToggle?: (row: TData) => void
}

interface DataTableEmits {
  (e: 'update:searchQuery', value: string): void
  (e: 'row-selection-change', selectedRows: any[]): void
  (e: 'page-change', page: number): void
  (e: 'page-size-change', size: number): void
}

const props = withDefaults(defineProps<DataTableProps<any>>(), {
  loading: false,
  searchQuery: '',
  searchPlaceholder: 'Search...',
  enableRowSelection: true,
  enableColumnVisibility: false,
  enablePagination: true,
  pageSize: 10,
  pageSizeOptions: () => [5, 10, 20, 50],
  title: '',
  currentPage: 1,
  total: 0,
  responsiveBreakpoint: 768,
  cardView: undefined,
})

const emit = defineEmits<DataTableEmits>()

// Responsive state
const screenWidth = ref(window.innerWidth)
const isMobile = computed(() => screenWidth.value < props.responsiveBreakpoint)
const showCardView = computed(() => props.cardView?.enabled && isMobile.value)

// Update screen width on resize
const updateScreenSize = () => {
  screenWidth.value = window.innerWidth
}

onMounted(() => {
  window.addEventListener('resize', updateScreenSize)
})

onUnmounted(() => {
  window.removeEventListener('resize', updateScreenSize)
})

// Utility function to handle value updates
function valueUpdater<T>(updaterOrValue: T | ((old: T) => T), ref: { value: T }) {
  if (typeof updaterOrValue === 'function') {
    ref.value = (updaterOrValue as (old: T) => T)(ref.value)
  } else {
    ref.value = updaterOrValue
  }
}

// Table state
const sorting = ref<SortingState>([])
const columnFilters = ref<ColumnFiltersState>([])
const columnVisibility = ref<VisibilityState>({})
const rowSelection = ref({})

// For internal pagination (when not using external control)
const internalPagination = ref<PaginationState>({
  pageIndex: 0,
  pageSize: props.pageSize,
})

// Local search state for debouncing
const localSearchQuery = ref(props.searchQuery)

// Fixed debounce implementation with proper typing
const debounce = (fn: (value: string) => void, delay: number) => {
  let timeoutId: number
  return (value: string) => {
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => fn(value), delay)
  }
}

// Debounced search handler
const debouncedSearch = debounce((value: string) => {
  emit('update:searchQuery', value)
  if (props.onSearch) {
    props.onSearch(value)
  }
}, 500)

// Watch local search and trigger debounced search
watch(localSearchQuery, (newValue) => {
  debouncedSearch(newValue)
})

// Watch external searchQuery changes to sync with local state
watch(() => props.searchQuery, (newValue) => {
  if (newValue !== localSearchQuery.value) {
    localSearchQuery.value = newValue
  }
})

// Watch row selection changes
watch(rowSelection, (newSelection) => {
  const selectedRows = table.getFilteredSelectedRowModel().rows.map(row => row.original)
  emit('row-selection-change', selectedRows)
})

// Determine if we're using external pagination control
const usingExternalPagination = computed(() => 
  props.enablePagination && props.onPageChange && props.onPageSizeChange
)

const table = useVueTable({
  data: props.data,
  columns: props.columns,
  getCoreRowModel: getCoreRowModel(),
  getPaginationRowModel: props.enablePagination && !usingExternalPagination.value ? getPaginationRowModel() : undefined,
  getSortedRowModel: getSortedRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  onSortingChange: (updaterOrValue) => valueUpdater(updaterOrValue, sorting),
  onColumnFiltersChange: (updaterOrValue) => valueUpdater(updaterOrValue, columnFilters),
  onColumnVisibilityChange: (updaterOrValue) => valueUpdater(updaterOrValue, columnVisibility),
  onRowSelectionChange: (updaterOrValue) => valueUpdater(updaterOrValue, rowSelection),
  onPaginationChange: props.enablePagination && !usingExternalPagination.value ? (updaterOrValue) => valueUpdater(updaterOrValue, internalPagination) : undefined,
  enableRowSelection: props.enableRowSelection,
  state: {
    get sorting() { return sorting.value },
    get columnFilters() { return columnFilters.value },
    get columnVisibility() { return columnVisibility.value },
    get rowSelection() { return rowSelection.value },
    get pagination() { return usingExternalPagination.value ? undefined : internalPagination.value },
  },
})

// Computed properties
const selectedRowCount = computed(() => 
  table.getFilteredSelectedRowModel().rows.length
)

const totalRowCount = computed(() => 
  usingExternalPagination.value ? props.total : table.getFilteredRowModel().rows.length
)

const pageCount = computed(() => 
  usingExternalPagination.value ? Math.ceil(props.total / props.pageSize) : table.getPageCount()
)

const currentPage = computed(() => 
  usingExternalPagination.value ? props.currentPage : table.getState().pagination.pageIndex + 1
)

const currentPageSize = computed(() => 
  usingExternalPagination.value ? props.pageSize : table.getState().pagination.pageSize
)

// Pagination calculations
const totalPages = computed(() => Math.ceil(totalRowCount.value / currentPageSize.value))
const startItem = computed(() => (currentPage.value - 1) * currentPageSize.value + 1)
const endItem = computed(() => Math.min(currentPage.value * currentPageSize.value, totalRowCount.value))

// Generate page numbers for pagination
const visiblePages = computed(() => {
  const pages: (number | string)[] = []
  const current = currentPage.value
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

// Check if search is active
const hasSearchQuery = computed(() => {
  return !!localSearchQuery.value
})

// Methods
const handleRowClick = (row: any) => {
  if (props.onRowClick) {
    props.onRowClick(row.original)
  }
}

const clearSearch = () => {
  localSearchQuery.value = ''
  emit('update:searchQuery', '')
  if (props.onSearch) {
    props.onSearch('')
  }
}

// Pagination handlers
const handlePageChange = (page: number) => {
  if (usingExternalPagination.value) {
    emit('page-change', page)
  } else {
    table.setPageIndex(page - 1)
  }
}

const handlePageSizeChange = (value: string) => {
  if (!props.loading && value) {
    const size = parseInt(value, 10)
    if (usingExternalPagination.value) {
      emit('page-size-change', size)
    } else {
      table.setPageSize(size)
    }
  }
}

const handleFirstPage = () => {
  if (currentPage.value > 1 && !props.loading) {
    handlePageChange(1)
  }
}

const handlePreviousPage = () => {
  if (currentPage.value > 1 && !props.loading) {
    handlePageChange(currentPage.value - 1)
  }
}

const handleNextPage = () => {
  if (currentPage.value < totalPages.value && !props.loading) {
    handlePageChange(currentPage.value + 1)
  }
}

const handleLastPage = () => {
  if (currentPage.value < totalPages.value && !props.loading) {
    handlePageChange(totalPages.value)
  }
}

// Card view handlers
const handleCardView = (row: any, e: Event) => {
  e.stopPropagation()
  if (props.onView) {
    props.onView(row)
  } else if (props.onRowClick) {
    props.onRowClick(row)
  }
}

const handleCardEdit = (row: any, e: Event) => {
  e.stopPropagation()
  if (props.onEdit) {
    props.onEdit(row)
  }
}

const handleCardDelete = (row: any, e: Event) => {
  e.stopPropagation()
  if (props.onDelete) {
    props.onDelete(row)
  }
}

const handleCardStatusToggle = (row: any, e: Event) => {
  e.stopPropagation()
  if (props.onStatusToggle) {
    props.onStatusToggle(row)
  }
}

const handleCustomAction = (action: any, row: any, e: Event) => {
  e.stopPropagation()
  action.action(row)
}

// Format value for card view
const formatCardValue = (row: any, field: string) => {
  const fieldConfig = props.cardView?.fields[field]
  const value = row[field]
  
  if (fieldConfig?.format) {
    return fieldConfig.format(value)
  }
  
  return value
}
</script>

<template>
  <div class="w-full bg-card rounded-lg border shadow-sm data-table-container">
    <!-- Combined Header with Search, Title, and Controls -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 ">
      <!-- Left side: Title and Search -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-1 min-w-0">
        <!-- Title -->
        <h3 v-if="title" class="text-lg font-semibold text-foreground whitespace-nowrap">
          {{ title }}
        </h3>
        
        <!-- Search Input -->
        <div v-if="onSearch" class="flex-1 min-w-0 max-w-md">
          <div class="relative">
            <Input
              v-model="localSearchQuery"
              type="text"
              :placeholder="searchPlaceholder"
              class="w-full pl-10 pr-10 data-table-search"
              :disabled="loading"
            />
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <Search class="h-4 w-4 text-muted-foreground" />
            </div>
            <!-- Clear search button -->
            <button
              v-if="hasSearchQuery && !loading"
              @click="clearSearch"
              class="absolute right-3 top-1/2 transform -translate-y-1/2 hover:bg-muted rounded-full p-1 transition-colors"
            >
              <X class="h-3 w-3 text-muted-foreground" />
            </button>
            <!-- Loading indicator -->
            <div v-if="loading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary"></div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Right side: Controls -->
      <div class="flex items-center gap-2 flex-shrink-0">
        <!-- Responsive Indicator -->
       

        <!-- Column Visibility Toggle -->
        <DropdownMenu v-if="enableColumnVisibility && !showCardView">
          <DropdownMenuTrigger as-child>
            <Button variant="outline" size="sm" class="flex items-center gap-2">
              <Columns class="h-4 w-4" />
              <span class="hidden sm:inline">Columns</span>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="column-visibility-dropdown">
            <DropdownMenuCheckboxItem
              v-for="column in table.getAllColumns().filter(col => col.getCanHide())"
              :key="column.id"
              class="capitalize"
              :checked="column.getIsVisible()"
              @update:checked="(value: boolean) => column.toggleVisibility(!!value)"
            >
              {{ column.id }}
            </DropdownMenuCheckboxItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </div>

    <!-- Desktop Table View -->
    <div v-if="!showCardView" class="responsive-table-container">
      <Table>
        <TableHeader>
          <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id" class="bg-muted/50">
            <TableHead 
              v-for="header in headerGroup.headers" 
              :key="header.id" 
              class="whitespace-nowrap px-3 py-3 text-sm font-medium text-foreground text-center"
              :class="{
                'cursor-pointer select-none hover:bg-muted transition-colors': header.column.getCanSort(),
                'responsive-cell': isMobile,
              }"
              @click="header.column.getCanSort() ? header.column.toggleSorting() : null"
            >
              <div class="flex items-center justify-center">
                <FlexRender 
                  v-if="!header.isPlaceholder" 
                  :render="header.column.columnDef.header" 
                  :props="header.getContext()" 
                />
                <span 
                  v-if="header.column.getCanSort()" 
                  class="ml-1 text-muted-foreground text-xs"
                >
                  {{ 
                    header.column.getIsSorted() === 'asc' ? '↑' : 
                    header.column.getIsSorted() === 'desc' ? '↓' : '↕'
                  }}
                </span>
              </div>
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="table.getRowModel().rows?.length">
            <TableRow
              v-for="row in table.getRowModel().rows"
              :key="row.id"
              :data-state="row.getIsSelected() && 'selected'"
              class="border-b transition-colors hover:bg-muted/50 table-row-hover"
              :class="{ 
                'cursor-pointer': onRowClick,
                'bg-muted/30': row.getIsSelected()
              }"
              @click="handleRowClick(row)"
            >
              <TableCell 
                v-for="cell in row.getVisibleCells()" 
                :key="cell.id" 
                class="whitespace-nowrap px-3 py-3 responsive-cell"
                :class="{
                  'text-xs': isMobile,
                  'min-w-[120px]': !isMobile
                }"
              >
                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
              </TableCell>
            </TableRow>
          </template>

          <TableRow v-else-if="loading">
            <TableCell
              :colspan="columns.length"
              class="h-32 text-center"
            >
              <div class="flex flex-col items-center justify-center space-y-3">
                <RotateCw class="h-8 w-8 animate-spin text-primary" />
                <span class="text-muted-foreground text-sm">Loading data...</span>
              </div>
            </TableCell>
          </TableRow>

          <TableRow v-else>
            <TableCell
              :colspan="columns.length"
              class="h-32 text-center text-muted-foreground"
            >
              <div class="flex flex-col items-center justify-center space-y-2">
                <Search class="h-12 w-12 text-muted-foreground/50" />
                <div>
                  <p class="font-medium">No data found</p>
                  <p class="text-sm" v-if="hasSearchQuery">
                    No results for "{{ localSearchQuery }}". 
                    <button @click="clearSearch" class="text-primary hover:underline">Clear search</button>
                  </p>
                  <p class="text-sm" v-else>No records available</p>
                </div>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Mobile Card View -->
    <div v-if="showCardView" class="card-view-container p-4 space-y-4">
      <template v-if="table.getRowModel().rows?.length">
        <div
          v-for="row in table.getRowModel().rows"
          :key="row.id"
          class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-4 shadow-sm transition-all duration-300 card-hover"
        >
          <!-- Card Header -->
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center space-x-3 flex-1 min-w-0">
              <slot name="card-header" :row="row.original">
                <!-- Default card header content -->
                <div class="flex-1 min-w-0">
                  <h4 class="font-semibold text-gray-900 dark:text-white truncate">{{ row.original.name || row.original.title || 'Untitled' }}</h4>
                  <p v-if="row.original.email" class="text-sm text-gray-600 dark:text-gray-300 truncate">{{ row.original.email }}</p>
                </div>
              </slot>
            </div>
            <slot name="card-badge" :row="row.original">
              <!-- Default badge -->
              <Badge v-if="row.original.status !== undefined" variant="secondary" class="text-xs whitespace-nowrap">
                {{ row.original.status }}
              </Badge>
            </slot>
          </div>
          
          <!-- Card Content -->
          <div class="grid grid-cols-1 gap-2 text-sm mb-4">
            <template v-for="(fieldConfig, fieldName) in cardView?.fields" :key="fieldName">
              <div v-if="row.original[fieldName] !== undefined" class="flex items-center justify-between">
                <span class="text-gray-600 dark:text-gray-300 font-medium">{{ fieldConfig.label || fieldName }}:</span>
                <span class="text-gray-900 dark:text-white text-right font-medium truncate ml-2">
                  {{ formatCardValue(row.original, fieldName) }}
                </span>
              </div>
            </template>
          </div>

          <!-- Card Actions -->
          <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-200 dark:border-gray-600">
            <!-- View Action -->
            <Button
              v-if="cardView?.actions?.view !== false && (onView || onRowClick)"
              variant="secondary"
              size="sm"
              @click="handleCardView(row.original, $event)"
              class="h-8 px-3 action-button flex items-center gap-1 bg-blue-50 text-blue-700 hover:bg-blue-100 hover:text-blue-800 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/30"
            >
              <Eye class="h-3.5 w-3.5" />
              <span class="text-xs font-medium">View</span>
            </Button>
            
            <!-- Edit Action -->
            <Button
              v-if="cardView?.actions?.edit !== false && onEdit"
              variant="secondary"
              size="sm"
              @click="handleCardEdit(row.original, $event)"
              class="h-8 px-3 action-button flex items-center gap-1 bg-green-50 text-green-700 hover:bg-green-100 hover:text-green-800 dark:bg-green-900/20 dark:text-green-300 dark:hover:bg-green-900/30"
            >
              <Edit class="h-3.5 w-3.5" />
              <span class="text-xs font-medium">Edit</span>
            </Button>
            
            <!-- Status Toggle Action -->
            <Button
              v-if="cardView?.actions?.status !== false && onStatusToggle"
              variant="secondary"
              size="sm"
              @click="handleCardStatusToggle(row.original, $event)"
              class="h-8 px-3 action-button flex items-center gap-1"
              :class="row.original.is_active 
                ? 'bg-orange-50 text-orange-700 hover:bg-orange-100 hover:text-orange-800 dark:bg-orange-900/20 dark:text-orange-300 dark:hover:bg-orange-900/30' 
                : 'bg-green-50 text-green-700 hover:bg-green-100 hover:text-green-800 dark:bg-green-900/20 dark:text-green-300 dark:hover:bg-green-900/30'"
            >
              <component :is="row.original.is_active ? UserX : UserCheck" class="h-3.5 w-3.5" />
              <span class="text-xs font-medium">{{ row.original.is_active ? 'Deactivate' : 'Activate' }}</span>
            </Button>
            
            <!-- Delete Action -->
            <Button
              v-if="cardView?.actions?.delete !== false && onDelete"
              variant="secondary"
              size="sm"
              @click="handleCardDelete(row.original, $event)"
              class="h-8 px-3 action-button flex items-center gap-1 bg-red-50 text-red-700 hover:bg-red-100 hover:text-red-800 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30"
            >
              <Trash2 class="h-3.5 w-3.5" />
              <span class="text-xs font-medium">Delete</span>
            </Button>

            <!-- Custom Actions -->
            <template v-if="cardView?.actions?.custom">
              <Button
                v-for="(action, index) in cardView.actions.custom"
                :key="`custom-${index}`"
                :variant="action.variant || 'secondary'"
                size="sm"
                @click="handleCustomAction(action, row.original, $event)"
                class="h-8 px-3 action-button flex items-center gap-1 bg-gray-50 text-gray-700 hover:bg-gray-100 hover:text-gray-800 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
              >
                <component :is="action.icon" class="h-3.5 w-3.5" />
                <span class="text-xs font-medium">{{ action.label }}</span>
              </Button>
            </template>
          </div>
        </div>
      </template>

      <!-- Loading State for Cards -->
      <div v-else-if="loading" class="flex flex-col items-center justify-center space-y-3 py-8">
        <RotateCw class="h-8 w-8 animate-spin text-primary" />
        <span class="text-muted-foreground text-sm">Loading data...</span>
      </div>

      <!-- Empty State for Cards -->
      <div v-else class="flex flex-col items-center justify-center space-y-2 py-8 text-center">
        <Search class="h-12 w-12 text-muted-foreground/50" />
        <div>
          <p class="font-medium text-gray-900 dark:text-white">No data found</p>
          <p class="text-sm text-gray-600 dark:text-gray-300" v-if="hasSearchQuery">
            No results for "{{ localSearchQuery }}". 
            <button @click="clearSearch" class="text-primary hover:underline">Clear search</button>
          </p>
          <p class="text-sm text-gray-600 dark:text-gray-300" v-else>No records available</p>
        </div>
      </div>
    </div>

    <!-- Footer with Selection Info and Pagination -->
    <div v-if="enablePagination || enableRowSelection" class="border-t">
      <!-- Selection Info -->
      <div v-if="enableRowSelection && selectedRowCount > 0" class="flex items-center gap-2 p-4 border-b">
        <Badge variant="secondary" class="px-2 py-1 text-xs">
          {{ selectedRowCount }} selected
        </Badge>
        <span class="text-sm text-muted-foreground">
          of {{ totalRowCount }} total row(s)
        </span>
      </div>

      <!-- Pagination -->
      <div v-if="enablePagination" class="flex flex-col sm:flex-row items-center justify-between p-4 gap-4">
        <!-- Page Info -->
        <div class="text-sm text-muted-foreground text-center sm:text-left">
          Showing {{ startItem }} to {{ endItem }} of {{ totalRowCount }} entries
        </div>

        <!-- Pagination Controls -->
        <div class="flex items-center space-x-2">

          <!-- Page Navigation -->
          <div class="flex items-center space-x-1">
            <!-- First Page -->
            <Button
              variant="outline"
              size="sm"
              class="h-8 w-8 p-0 pagination-button"
              :disabled="currentPage <= 1 || loading"
              @click="handleFirstPage"
            >
              <ChevronsLeft class="h-4 w-4" />
            </Button>

            <!-- Previous Page -->
            <Button
              variant="outline"
              size="sm"
              class="h-8 w-8 p-0 pagination-button"
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
                class="h-8 min-w-8 p-0 text-xs pagination-button"
                :class="[
                  pageNum === currentPage 
                    ? 'bg-blue-600 text-white hover:bg-blue-700 active-page-highlight cursor-default' 
                    : 'hover:bg-accent hover:text-accent-foreground'
                ]"
                @click="pageNum !== '...' && pageNum !== currentPage ? handlePageChange(pageNum as number) : null"
                :disabled="loading || pageNum === '...' || pageNum === currentPage"
              >
                {{ pageNum }}
              </Button>
            </div>

            <!-- Next Page -->
            <Button
              variant="outline"
              size="sm"
              class="h-8 w-8 p-0 pagination-button"
              :disabled="currentPage >= totalPages || loading"
              @click="handleNextPage"
            >
              <ChevronRight class="h-4 w-4" />
            </Button>

            <!-- Last Page -->
            <Button
              variant="outline"
              size="sm"
              class="h-8 w-8 p-0 pagination-button"
              :disabled="currentPage >= totalPages || loading"
              @click="handleLastPage"
            >
              <ChevronsRight class="h-4 w-4" />
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Simple footer when no pagination but selection info needed -->
    <div v-else-if="enableRowSelection" class="flex flex-col sm:flex-row items-center justify-between p-4 border-t gap-4">
      <div class="flex-1">
        <span class="text-sm text-muted-foreground">
          {{ totalRowCount }} row(s) total
        </span>
      </div>
    </div>
  </div>
</template>