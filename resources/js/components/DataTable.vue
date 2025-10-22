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
import { ref, computed, watch } from "vue"

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
import Paginator from '@/components/Paginator.vue'
import { Search, X, Columns, RotateCw } from "lucide-vue-next"

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
  // New props for external pagination control
  currentPage?: number
  total?: number
  onPageChange?: (page: number) => void
  onPageSizeChange?: (size: number) => void
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
})

const emit = defineEmits<DataTableEmits>()

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

// Pagination handlers for external control
const handlePageChange = (page: number) => {
  if (usingExternalPagination.value) {
    emit('page-change', page)
  } else {
    table.setPageIndex(page - 1)
  }
}

// Fix the parameter type for handlePageSizeChange
const handlePageSizeChange = (value: string | number) => {
  const size = typeof value === 'string' ? parseInt(value, 10) : value
  if (usingExternalPagination.value) {
    emit('page-size-change', size)
  } else {
    table.setPageSize(size)
  }
}
</script>

<template>
  <div class="w-full bg-card rounded-lg border shadow-sm">
    <!-- Combined Header with Search, Title, and Controls -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between  gap-4">
      <!-- Left side: Title and Search -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-1 min-w-0">
        <!-- Title -->
        <h3 v-if="title" class="text-lg font-semibold text-foreground whitespace-nowrap ">
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
        <!-- Column Visibility Toggle -->
        <DropdownMenu v-if="enableColumnVisibility">
          <DropdownMenuTrigger as-child>
            <Button variant="outline" size="sm" class="flex items-center gap-2">
              <Columns class="h-4 w-4" />
              Columns
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="column-visibility-dropdown">
            <DropdownMenuCheckboxItem
              v-for="column in table.getAllColumns().filter(col => col.getCanHide())"
              :key="column.id"
              class="capitalize"
              :checked="column.getIsVisible()"
              @update:checked="(value) => column.toggleVisibility(!!value)"
            >
              {{ column.id }}
            </DropdownMenuCheckboxItem>
          </DropdownMenuContent>
        </DropdownMenu>

        
      </div>
    </div>

    <!-- Table Container -->
    <div class="rounded-md overflow-x-auto table-container">
      <Table>
        <TableHeader>
          <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id" class="bg-muted/50">
            <TableHead 
              v-for="header in headerGroup.headers" 
              :key="header.id" 
              class="whitespace-nowrap px-4 py-3 text-sm font-medium text-foreground text-center"
              :class="{
                'cursor-pointer select-none hover:bg-muted transition-colors': header.column.getCanSort(),
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
                  class="ml-2 text-muted-foreground"
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
              class="border-b transition-colors hover:bg-muted/50"
              :class="{ 
                'cursor-pointer': onRowClick,
                'bg-muted/30': row.getIsSelected()
              }"
              @click="handleRowClick(row)"
            >
              <TableCell 
                v-for="cell in row.getVisibleCells()" 
                :key="cell.id" 
                class="whitespace-nowrap px-4 py-3"
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

    <!-- Footer with Selection Info and Pagination -->
    <div v-if="enablePagination" class="border-t">
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
      <Paginator
        :current-page="currentPage"
        :total="totalRowCount"
        :page-size="currentPageSize"
        :loading="loading"
        :page-size-options="pageSizeOptions"
        @page-change="handlePageChange"
        @page-size-change="handlePageSizeChange"
      />
    </div>

    <!-- Simple footer when no pagination -->
    <div v-else class="flex flex-col sm:flex-row items-center justify-between p-4 border-t gap-4">
      <!-- Selection Info -->
      <div v-if="enableRowSelection && selectedRowCount > 0" class="flex-1">
        <div class="flex items-center gap-2">
          <Badge variant="secondary" class="px-2 py-1 text-xs">
            {{ selectedRowCount }} selected
          </Badge>
          <span class="text-sm text-muted-foreground">
            of {{ totalRowCount }} total row(s)
          </span>
        </div>
      </div>
      
      <div v-else class="flex-1">
        <span class="text-sm text-muted-foreground">
          {{ totalRowCount }} row(s) total
        </span>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Enhanced smooth transitions */
.table-row-enter-active,
.table-row-leave-active {
  transition: all 0.3s ease;
}

.table-row-enter-from,
.table-row-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Custom scrollbar for table container */
.table-container::-webkit-scrollbar {
  height: 8px;
}

.table-container::-webkit-scrollbar-track {
  background: transparent;
}

.table-container::-webkit-scrollbar-thumb {
  background: hsl(var(--muted-foreground) / 0.3);
  border-radius: 4px;
}

.table-container::-webkit-scrollbar-thumb:hover {
  background: hsl(var(--muted-foreground) / 0.5);
}

/* Data table search styles */
.data-table-search:focus {
  border-color: hsl(var(--ring));
  box-shadow: 0 0 0 2px hsl(var(--ring) / 10%);
}

/* Column visibility dropdown */
.column-visibility-dropdown {
  background: hsl(var(--background));
  border: 1px solid hsl(var(--border));
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style>