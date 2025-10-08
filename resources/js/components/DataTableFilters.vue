<script setup lang="ts">
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { ChevronDown } from "lucide-vue-next"
import { computed, ref, watch } from "vue"

interface FilterOption {
  label: string
  value: string
}

interface Props {
  searchQuery: string
  statusFilter: string
  bidTypeFilter: string
  statusOptions: FilterOption[] | Record<string, string>
  bidTypeOptions: FilterOption[] | Record<string, string>
  onSearch: (value: string) => void
  onStatusFilter: (value: string) => void
  onBidTypeFilter: (value: string) => void
  onClearFilters: () => void
  searchPlaceholder?: string
  loading?: boolean
}

// Fix: Use only string for events since we're only dealing with strings
interface Emits {
  (e: 'update:searchQuery', value: string): void
  (e: 'update:statusFilter', value: string): void
  (e: 'update:bidTypeFilter', value: string): void
}

const props = withDefaults(defineProps<Props>(), {
  searchPlaceholder: 'Search...',
  loading: false
})

const emit = defineEmits<Emits>()

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
  props.onSearch(value)
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

// Convert options to array format for easier handling
const statusOptionsArray = computed(() => {
  return Array.isArray(props.statusOptions) 
    ? props.statusOptions 
    : Object.entries(props.statusOptions).map(([value, label]) => ({ value, label }))
})

const bidTypeOptionsArray = computed(() => {
  return Array.isArray(props.bidTypeOptions) 
    ? props.bidTypeOptions 
    : Object.entries(props.bidTypeOptions).map(([value, label]) => ({ value, label }))
})

// Handle status filter selection
const handleStatusSelect = (value: string) => {
  const newValue = props.statusFilter === value ? '' : value
  emit('update:statusFilter', newValue)
  props.onStatusFilter(newValue)
}

// Handle bid type filter selection
const handleBidTypeSelect = (value: string) => {
  const newValue = props.bidTypeFilter === value ? '' : value
  emit('update:bidTypeFilter', newValue)
  props.onBidTypeFilter(newValue)
}

// Check if an option is selected
const isStatusSelected = (value: string) => {
  return props.statusFilter === value
}

const isBidTypeSelected = (value: string) => {
  return props.bidTypeFilter === value
}

// Get display text for dropdown triggers
const statusDisplayText = computed(() => {
  if (!props.statusFilter) return 'All Status'
  const option = statusOptionsArray.value.find((opt: FilterOption) => opt.value === props.statusFilter)
  return option?.label || 'Select Status'
})

const bidTypeDisplayText = computed(() => {
  if (!props.bidTypeFilter) return 'All Types'
  const option = bidTypeOptionsArray.value.find((opt: FilterOption) => opt.value === props.bidTypeFilter)
  return option?.label || 'Select Type'
})
</script>

<template>
  <div class="bg-card rounded-lg border p-4 shadow-sm">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <!-- Search -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
        <div class="relative">
          <Input
            v-model="localSearchQuery"
            type="text"
            :placeholder="searchPlaceholder"
            class="w-full pr-10"
            :disabled="loading"
          />
          <div v-if="loading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary"></div>
          </div>
        </div>
      </div>

      <!-- Status Filter Dropdown -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="outline" class="w-full justify-between" :disabled="loading">
              <span class="truncate">
                {{ statusDisplayText }}
              </span>
              <ChevronDown class="h-4 w-4 opacity-50 ml-2 flex-shrink-0" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent class="w-56">
            <DropdownMenuLabel>Filter by Status</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuCheckboxItem
              :model-value="!statusFilter"
              @update:model-value="() => handleStatusSelect('')"
              :disabled="loading"
            >
              All Status
            </DropdownMenuCheckboxItem>
            <DropdownMenuSeparator />
            <DropdownMenuCheckboxItem
              v-for="option in statusOptionsArray"
              :key="option.value"
              :model-value="isStatusSelected(option.value)"
              @update:model-value="() => handleStatusSelect(option.value)"
              :disabled="loading"
            >
              {{ option.label }}
            </DropdownMenuCheckboxItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>

      <!-- Bid Type Filter Dropdown -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Bid Type</label>
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="outline" class="w-full justify-between" :disabled="loading">
              <span class="truncate">
                {{ bidTypeDisplayText }}
              </span>
              <ChevronDown class="h-4 w-4 opacity-50 ml-2 flex-shrink-0" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent class="w-56">
            <DropdownMenuLabel>Filter by Bid Type</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuCheckboxItem
              :model-value="!bidTypeFilter"
              @update:model-value="() => handleBidTypeSelect('')"
              :disabled="loading"
            >
              All Types
            </DropdownMenuCheckboxItem>
            <DropdownMenuSeparator />
            <DropdownMenuCheckboxItem
              v-for="option in bidTypeOptionsArray"
              :key="option.value"
              :model-value="isBidTypeSelected(option.value)"
              @update:model-value="() => handleBidTypeSelect(option.value)"
              :disabled="loading"
            >
              {{ option.label }}
            </DropdownMenuCheckboxItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>

      <!-- Clear Filters -->
      <div class="flex items-end">
        <Button
          @click="onClearFilters"
          variant="outline"
          class="w-full"
          :disabled="loading"
        >
          Clear Filters
        </Button>
      </div>
    </div>

    <!-- Active Filters Display -->
    <div v-if="statusFilter || bidTypeFilter" class="mt-4 flex flex-wrap gap-2">
      <div 
        v-if="statusFilter" 
        class="inline-flex items-center gap-1 bg-primary/10 text-primary px-2 py-1 rounded-md text-sm"
      >
        Status: {{ statusOptionsArray.find((opt: FilterOption) => opt.value === statusFilter)?.label }}
        <button 
          @click="handleStatusSelect('')"
          class="hover:bg-primary/20 rounded-full p-0.5"
          :disabled="loading"
        >
          ×
        </button>
      </div>
      <div 
        v-if="bidTypeFilter" 
        class="inline-flex items-center gap-1 bg-primary/10 text-primary px-2 py-1 rounded-md text-sm"
      >
        Type: {{ bidTypeOptionsArray.find((opt: FilterOption) => opt.value === bidTypeFilter)?.label }}
        <button 
          @click="handleBidTypeSelect('')"
          class="hover:bg-primary/20 rounded-full p-0.5"
          :disabled="loading"
        >
          ×
        </button>
      </div>
    </div>
  </div>
</template>