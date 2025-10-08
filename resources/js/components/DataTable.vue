<script setup lang="ts">
import type {
  ColumnDef,
  ColumnFiltersState,
  SortingState,
  VisibilityState,
} from "@tanstack/vue-table"
import {
  FlexRender,
  getCoreRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useVueTable,
} from "@tanstack/vue-table"
import { h, ref, computed, watch } from "vue"

import { Button } from "@/components/ui/button"
import { Checkbox } from "@/components/ui/checkbox"
import { Input } from "@/components/ui/input"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"

interface DataTableProps<TData> {
  data: TData[]
  columns: ColumnDef<TData>[]
  loading?: boolean
  searchQuery?: string
  onSearch?: (value: string) => void
  searchPlaceholder?: string
  filters?: {
    search?: string
    [key: string]: any
  }
  enableRowSelection?: boolean
  enableColumnVisibility?: boolean
}

// Fix: Use string only for search
interface DataTableEmits {
  (e: 'update:searchQuery', value: string): void
}

const props = withDefaults(defineProps<DataTableProps<any>>(), {
  loading: false,
  searchQuery: '',
  searchPlaceholder: 'Search...',
  enableRowSelection: true,
  enableColumnVisibility: false, // Changed to false by default
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

const sorting = ref<SortingState>([])
const columnFilters = ref<ColumnFiltersState>([])
const columnVisibility = ref<VisibilityState>({})
const rowSelection = ref({})

// Local search state for debouncing
const localSearchQuery = ref(props.searchQuery)

// Debounce implementation
const debounce = (fn: Function, delay: number) => {
  let timeoutId: number
  return (...args: any[]) => {
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => fn(...args), delay)
  }
}

// Debounced search handler
const debouncedSearch = debounce((value: string) => {
  emit('update:searchQuery', value)
  if (props.onSearch) {
    props.onSearch(value)
  }
}, 500) // 500ms delay

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

const table = useVueTable({
  data: props.data,
  columns: props.columns,
  getCoreRowModel: getCoreRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  onSortingChange: (updaterOrValue) => valueUpdater(updaterOrValue, sorting),
  onColumnFiltersChange: (updaterOrValue) => valueUpdater(updaterOrValue, columnFilters),
  onColumnVisibilityChange: (updaterOrValue) => valueUpdater(updaterOrValue, columnVisibility),
  onRowSelectionChange: (updaterOrValue) => valueUpdater(updaterOrValue, rowSelection),
  state: {
    get sorting() { return sorting.value },
    get columnFilters() { return columnFilters.value },
    get columnVisibility() { return columnVisibility.value },
    get rowSelection() { return rowSelection.value },
  },
})
</script>

<template>
  <div class="w-full bg-card rounded-lg border shadow-sm">
    <!-- Search Input -->
    <div v-if="onSearch" class="p-4 border-b">
      <div class="relative">
        <Input
          v-model="localSearchQuery"
          type="text"
          :placeholder="searchPlaceholder"
          class="w-full sm:w-64 pr-10"
          :disabled="loading"
        />
        <div v-if="loading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary"></div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="rounded-md overflow-x-auto">
      <Table>
        <TableHeader>
          <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
            <TableHead v-for="header in headerGroup.headers" :key="header.id" class="whitespace-nowrap">
              <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="table.getRowModel().rows?.length">
            <TableRow
              v-for="row in table.getRowModel().rows"
              :key="row.id"
              :data-state="row.getIsSelected() && 'selected'"
              class="hover:bg-muted/50 transition-colors"
            >
              <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="whitespace-nowrap">
                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
              </TableCell>
            </TableRow>
          </template>

          <TableRow v-else-if="loading">
            <TableCell
              :colspan="columns.length"
              class="h-24 text-center"
            >
              <div class="flex items-center justify-center space-x-2">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                <span class="text-muted-foreground">Loading data...</span>
              </div>
            </TableCell>
          </TableRow>

          <TableRow v-else>
            <TableCell
              :colspan="columns.length"
              class="h-24 text-center text-muted-foreground"
            >
              No data found.
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Selection Info -->
    <div v-if="enableRowSelection" class="flex items-center justify-between p-4 border-t">
      <div class="flex-1 text-sm text-muted-foreground">
        {{ table.getFilteredSelectedRowModel().rows.length }} of
        {{ table.getFilteredRowModel().rows.length }} row(s) selected.
      </div>
    </div>
  </div>
</template>