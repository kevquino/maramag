<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { type ColumnDef } from "@tanstack/vue-table"
import { Eye, Edit, Trash2, Star, MapPin, Users, TrendingUp, DollarSign, Clock, Filter, Search, X, ChevronDown } from "lucide-vue-next"
import { h, ref, watch, nextTick } from "vue"
import { router, Head, Link, usePage } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'

import { Button } from "@/components/ui/button"
import { Checkbox } from "@/components/ui/checkbox"
import { Badge } from "@/components/ui/badge"
import { Input } from "@/components/ui/input"
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from "@/components/ui/tooltip"
import DataTable from '@/components/DataTable.vue'
import DataTablePagination from '@/components/DataTablePagination.vue'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog"

export interface TourismPackage {
  id: string
  title: string
  description: string
  location: string
  category: string
  price: number | null
  duration_days: number
  duration_nights: number
  inclusions: string[] | null
  exclusions: string[] | null
  itinerary: any[] | null
  difficulty_level: "easy" | "moderate" | "difficult"
  max_participants: number | null
  is_featured: boolean
  is_active: boolean
  featured_image: string | null
  gallery_images: string[] | null
  contact_person: string | null
  contact_email: string | null
  contact_phone: string | null
  user: {
    id: string
    name: string
    email: string
  }
  created_at: string
  updated_at: string
}

// Define flash message interface
interface FlashMessages {
  success?: string;
  error?: string;
  warning?: string;
  info?: string;
}

const props = defineProps<{
  packages: {
    data: any[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  filters?: {
    search?: string
    category?: string
    status?: string
    difficulty?: string
  }
  categoryOptions: Record<string, string>
  difficultyOptions: Record<string, string>
  statusOptions: Record<string, string>
}>();

const page = usePage();

// Watch for flash messages and show toasts with proper typing
watch(() => page.props.flash as FlashMessages | undefined, (newFlash, oldFlash) => {
  const currentFlash = newFlash as FlashMessages | undefined;
  const previousFlash = oldFlash as FlashMessages | undefined;
  
  if (currentFlash?.success && currentFlash.success !== previousFlash?.success) {
    nextTick(() => {
      toast.success(currentFlash.success!);
    });
  }
  if (currentFlash?.error && currentFlash.error !== previousFlash?.error) {
    nextTick(() => {
      toast.error(currentFlash.error!);
    });
  }
}, { deep: true, immediate: true });

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
  {
    title: 'Tourism',
    href: '/tourism',
  },
];

// Use reactive data from props
const data = ref<TourismPackage[]>(props.packages.data || [])
const loading = ref(false)
const total = ref(props.packages.total || 0)
const currentPage = ref(props.packages.current_page || 1)
const pageSize = ref(props.packages.per_page || 10)

const searchQuery = ref(props.filters?.search || '')
const categoryFilter = ref(props.filters?.category || '')
const statusFilter = ref(props.filters?.status || '')
const difficultyFilter = ref(props.filters?.difficulty || '')

// Delete dialog state
const deleteDialogOpen = ref(false)
const packageToDelete = ref<TourismPackage | null>(null)
const deleting = ref(false)

// Search timeout reference
let searchTimeout: number | null = null

// Function to reload the entire page with current filters
const reloadPage = () => {
  const params: any = {
    page: currentPage.value
  }

  if (searchQuery.value) params.search = searchQuery.value
  if (categoryFilter.value) params.category = categoryFilter.value
  if (statusFilter.value) params.status = statusFilter.value
  if (difficultyFilter.value) params.difficulty = difficultyFilter.value

  router.get('/tourism', params, {
    preserveState: false,
    preserveScroll: true,
    replace: true,
    onStart: () => loading.value = true,
    onFinish: () => loading.value = false,
  })
}

const handleSearch = (value: string) => {
  searchQuery.value = value
  
  // Clear existing timeout
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  
  // Reset to first page when searching
  currentPage.value = 1
  
  // Set new timeout for debounced search
  searchTimeout = setTimeout(() => {
    reloadPage()
  }, 500)
}

// Filter handlers
const handleStatusFilter = (value: string) => {
  statusFilter.value = value
  currentPage.value = 1
  reloadPage()
}

const handleCategoryFilter = (value: string) => {
  categoryFilter.value = value
  currentPage.value = 1
  reloadPage()
}

const handleDifficultyFilter = (value: string) => {
  difficultyFilter.value = value
  currentPage.value = 1
  reloadPage()
}

const clearFilters = () => {
  searchQuery.value = ''
  categoryFilter.value = ''
  statusFilter.value = ''
  difficultyFilter.value = ''
  currentPage.value = 1
  reloadPage()
}

const handlePreviousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    reloadPage()
  }
}

const handleNextPage = () => {
  if (currentPage.value < Math.ceil(total.value / pageSize.value)) {
    currentPage.value++
    reloadPage()
  }
}

// Delete handler
const deletePackage = () => {
  if (!packageToDelete.value) return

  deleting.value = true
  
  router.delete(`/tourism/${packageToDelete.value.id}`, {
    preserveScroll: true,
    preserveState: false,
    onSuccess: () => {
      deleteDialogOpen.value = false
      packageToDelete.value = null
    },
    onError: (errors) => {
      console.error('Delete error:', errors)
      let errorMsg = 'Failed to delete tourism package'
      
      if (typeof errors === 'string') {
        errorMsg = errors
      } else if (errors && typeof errors === 'object' && 'message' in errors) {
        errorMsg = (errors as any).message
      } else if (errors && typeof errors === 'object' && 'error' in errors) {
        errorMsg = (errors as any).error
      }
      
      toast.error(errorMsg)
    },
    onFinish: () => {
      deleting.value = false
    }
  })
}

const openDeleteDialog = (pkg: TourismPackage) => {
  packageToDelete.value = pkg
  deleteDialogOpen.value = true
}

// Feature toggle handler
const handleFeatureToggle = (pkg: TourismPackage) => {
  router.post(`/tourism/${pkg.id}/toggle-featured`, {}, {
    preserveScroll: true,
    preserveState: false,
    onSuccess: () => {
      // Success toast will be shown from server flash message
    },
    onError: (errors) => {
      console.error('Feature toggle error:', errors)
      let errorMsg = 'Failed to toggle feature'
      if (typeof errors === 'string') {
        errorMsg = errors
      } else if (errors && typeof errors === 'object' && 'message' in errors) {
        errorMsg = (errors as any).message
      } else if (errors && typeof errors === 'object' && 'error' in errors) {
        errorMsg = (errors as any).error
      }
      toast.error(errorMsg)
    }
  })
}

// Status toggle handler
const handleStatusToggle = (pkg: TourismPackage) => {
  router.post(`/tourism/${pkg.id}/toggle-status`, {}, {
    preserveScroll: true,
    preserveState: false,
    onSuccess: () => {
      // Success toast will be shown from server flash message
    },
    onError: (errors) => {
      console.error('Status toggle error:', errors)
      let errorMsg = 'Failed to toggle status'
      if (typeof errors === 'string') {
        errorMsg = errors
      } else if (errors && typeof errors === 'object' && 'message' in errors) {
        errorMsg = (errors as any).message
      } else if (errors && typeof errors === 'object' && 'error' in errors) {
        errorMsg = (errors as any).error
      }
      toast.error(errorMsg)
    }
  })
}

// Utility functions
const formatCurrency = (amount: number | null) => {
  if (!amount) return 'Free'
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount)
}

const formatDuration = (days: number, nights: number) => {
  const dayText = days > 0 ? `${days} day${days > 1 ? 's' : ''}` : ''
  const nightText = nights > 0 ? `${nights} night${nights > 1 ? 's' : ''}` : ''
  return [dayText, nightText].filter(Boolean).join(' / ')
}

const getImageUrl = (imagePath: string | null) => {
  if (!imagePath) return null;
  return `/storage/${imagePath}`;
}

// Event handler for input events with proper typing
const handleSearchInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target) {
    handleSearch(target.value);
  }
}

// Helper functions for dropdown filters
const getCategoryDisplayText = () => {
  if (!categoryFilter.value) return 'All Categories'
  return props.categoryOptions[categoryFilter.value] || 'Select Category'
}

const getStatusDisplayText = () => {
  if (!statusFilter.value) return 'All Status'
  return props.statusOptions[statusFilter.value] || 'Select Status'
}

const getDifficultyDisplayText = () => {
  if (!difficultyFilter.value) return 'All Levels'
  return props.difficultyOptions[difficultyFilter.value] || 'Select Difficulty'
}

const isCategorySelected = (value: string) => {
  return categoryFilter.value === value
}

const isStatusSelected = (value: string) => {
  return statusFilter.value === value
}

const isDifficultySelected = (value: string) => {
  return difficultyFilter.value === value
}

// Columns definition
const columns: ColumnDef<TourismPackage>[] = [
  {
    id: "select",
    header: ({ table }) => h(Checkbox, {
      "checked": table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && "indeterminate"),
      "onUpdate:checked": (value: boolean | "indeterminate") => table.toggleAllPageRowsSelected(!!value),
      "ariaLabel": "Select all",
    }),
    cell: ({ row }) => h(Checkbox, {
      "checked": row.getIsSelected(),
      "onUpdate:checked": (value: boolean | "indeterminate") => row.toggleSelected(!!value),
      "ariaLabel": "Select row",
    }),
    enableSorting: false,
    enableHiding: false,
  },
  {
    accessorKey: "title",
    header: "Package Details",
    cell: ({ row }) => {
      const pkg = row.original;
      return h("div", { class: "flex items-start space-x-3 min-w-[250px]" }, [
        pkg.featured_image ? h("div", { class: "flex-shrink-0" }, [
          h("img", {
            src: getImageUrl(pkg.featured_image),
            alt: pkg.title,
            class: "w-12 h-12 rounded-lg object-cover border"
          })
        ]) : h("div", { class: "flex-shrink-0 w-12 h-12 rounded-lg bg-muted flex items-center justify-center" }, [
          h(MapPin, { class: "h-5 w-5 text-muted-foreground" })
        ]),
        h("div", { class: "min-w-0 flex-1" }, [
          h("div", { class: "flex items-center space-x-2" }, [
            pkg.is_featured && h(Star, { class: "h-3 w-3 text-yellow-500 fill-yellow-500 flex-shrink-0" }),
            h("span", { class: `font-medium text-sm ${pkg.is_featured ? "text-yellow-700" : "text-foreground"}` }, pkg.title || 'No title'),
          ]),
          h("div", { class: "flex items-center space-x-1 text-xs text-muted-foreground mt-1" }, [
            h(MapPin, { class: "h-3 w-3 flex-shrink-0" }),
            h("span", { class: "truncate" }, pkg.location)
          ])
        ])
      ])
    },
  },
  {
    accessorKey: "category",
    header: "Category",
    cell: ({ row }) => h("div", { class: "px-2" }, [
      h(Badge, { 
        variant: "secondary",
        class: "text-xs"
      }, props.categoryOptions[row.original.category] || row.original.category)
    ]),
  },
  {
    accessorKey: "price",
    header: "Price",
    cell: ({ row }) => h("div", { class: "flex items-center space-x-1 px-2" }, [
      h(DollarSign, { class: "h-3 w-3 text-muted-foreground" }),
      h("span", { class: "text-sm font-medium" }, formatCurrency(row.original.price))
    ]),
  },
  {
    accessorKey: "duration_days",
    header: "Duration",
    cell: ({ row }) => h("div", { class: "flex items-center space-x-1 px-2" }, [
      h(Clock, { class: "h-3 w-3 text-muted-foreground" }),
      h("span", { class: "text-sm" }, formatDuration(row.original.duration_days, row.original.duration_nights))
    ]),
  },
  {
    accessorKey: "difficulty_level",
    header: "Difficulty",
    cell: ({ row }) => {
      const difficulty = row.original.difficulty_level;
      const variant = difficulty === 'easy' ? 'default' : 
                     difficulty === 'moderate' ? 'secondary' : 'destructive';
      return h("div", { class: "px-2" }, [
        h(Badge, { 
          variant,
          class: "text-xs"
        }, props.difficultyOptions[difficulty] || difficulty)
      ])
    },
  },
  {
    accessorKey: "max_participants",
    header: "Capacity",
    cell: ({ row }) => h("div", { class: "flex items-center space-x-1 px-2" }, [
      h(Users, { class: "h-3 w-3 text-muted-foreground" }),
      h("span", { class: "text-sm" }, row.original.max_participants ? `Max ${row.original.max_participants}` : 'Unlimited')
    ]),
  },
  {
    accessorKey: "status",
    header: "Status",
    cell: ({ row }) => {
      const pkg = row.original
      return h("div", { class: "flex flex-col space-y-1 px-2" }, [
        h(Badge, { 
          variant: pkg.is_active ? "default" : "outline",
          class: "text-xs"
        }, pkg.is_active ? "Active" : "Inactive"),
        pkg.is_featured && h(Badge, { 
          variant: "secondary",
          class: "text-xs"
        }, "Featured")
      ])
    },
  },
  {
    id: "actions",
    header: "Actions",
    cell: ({ row }) => {
      const pkg = row.original

      const handleView = () => {
        router.get(`/tourism/${pkg.id}`)
      }

      const handleEdit = () => {
        router.get(`/tourism/${pkg.id}/edit`)
      }

      const getFeatureButtonClass = (isFeatured: boolean) => {
        return isFeatured 
          ? 'h-8 w-8 p-0 text-yellow-600 hover:text-yellow-700' 
          : 'h-8 w-8 p-0 text-muted-foreground hover:text-foreground'
      }

      const getStatusButtonClass = (isActive: boolean) => {
        return isActive 
          ? 'h-8 w-8 p-0 text-green-600 hover:text-green-700' 
          : 'h-8 w-8 p-0 text-muted-foreground hover:text-foreground'
      }

      return h(TooltipProvider, {}, [
        h("div", { class: "flex space-x-1 justify-center" }, [
          h(Tooltip, {}, [
            h(TooltipTrigger, { asChild: true }, [
              h(Button, {
                variant: "ghost",
                size: "sm",
                onClick: handleView,
                class: "h-8 w-8 p-0 hover:bg-accent"
              }, [
                h(Eye, { class: "h-4 w-4" }),
              ])
            ]),
            h(TooltipContent, {}, "View Package")
          ]),
          h(Tooltip, {}, [
            h(TooltipTrigger, { asChild: true }, [
              h(Button, {
                variant: "ghost",
                size: "sm",
                onClick: handleEdit,
                class: "h-8 w-8 p-0 hover:bg-accent"
              }, [
                h(Edit, { class: "h-4 w-4" }),
              ])
            ]),
            h(TooltipContent, {}, "Edit Package")
          ]),
          h(Tooltip, {}, [
            h(TooltipTrigger, { asChild: true }, [
              h(Button, {
                variant: "ghost",
                size: "sm",
                onClick: () => handleFeatureToggle(pkg),
                class: `h-8 w-8 p-0 hover:bg-accent ${getFeatureButtonClass(pkg.is_featured)}`
              }, [
                h(Star, { class: `h-4 w-4 ${pkg.is_featured ? 'fill-current' : ''}` }),
              ])
            ]),
            h(TooltipContent, {}, pkg.is_featured ? "Remove from Featured" : "Mark as Featured")
          ]),
          h(Tooltip, {}, [
            h(TooltipTrigger, { asChild: true }, [
              h(Button, {
                variant: "ghost",
                size: "sm",
                onClick: () => handleStatusToggle(pkg),
                class: `h-8 w-8 p-0 hover:bg-accent ${getStatusButtonClass(pkg.is_active)}`
              }, [
                h(TrendingUp, { class: "h-4 w-4" }),
              ])
            ]),
            h(TooltipContent, {}, pkg.is_active ? "Deactivate Package" : "Activate Package")
          ]),
          h(Tooltip, {}, [
            h(TooltipTrigger, { asChild: true }, [
              h(Button, {
                variant: "ghost",
                size: "sm",
                onClick: () => openDeleteDialog(pkg),
                class: "h-8 w-8 p-0 text-destructive hover:text-destructive/90 hover:bg-accent"
              }, [
                h(Trash2, { class: "h-4 w-4" }),
              ])
            ]),
            h(TooltipContent, {}, "Delete Package")
          ]),
        ])
      ])
    },
    enableHiding: false,
  },
]

// Watchers to update reactive data when props change
watch(() => props.packages, (newPackages) => {
  data.value = newPackages.data || []
  total.value = newPackages.total || 0
  currentPage.value = newPackages.current_page || 1
  pageSize.value = newPackages.per_page || 10
}, { deep: true })

watch(() => props.filters, (newFilters) => {
  searchQuery.value = newFilters?.search || ''
  categoryFilter.value = newFilters?.category || ''
  statusFilter.value = newFilters?.status || ''
  difficultyFilter.value = newFilters?.difficulty || ''
}, { deep: true })
</script>

<template>
  <Head title="Tourism Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="text-center sm:text-left">
          <h1 class="text-3xl font-bold text-foreground">Tourism Management</h1>
          <p class="text-muted-foreground mt-2">Manage tourism packages, tours, and travel experiences</p>
        </div>
        
        <Link href="/tourism/create" as="button" class="w-full sm:w-auto">
          <Button class="w-full sm:w-auto">
            <span class="mr-2">+</span>
            Add New Package
          </Button>
        </Link>
      </div>

      <!-- Filters Section - Updated to use DropdownMenu like DataTableFilters -->
      <div class="bg-card rounded-lg border p-4 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-foreground mb-2">Search</label>
            <div class="relative">
              <Input
                v-model="searchQuery"
                type="text"
                placeholder="Search packages, locations..."
                class="w-full pr-10"
                @input="handleSearchInput"
                :disabled="loading"
              />
              <div v-if="loading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary"></div>
              </div>
            </div>
          </div>

          <!-- Category Filter Dropdown -->
          <div>
            <label class="block text-sm font-medium text-foreground mb-2">Category</label>
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="outline" class="w-full justify-between" :disabled="loading">
                  <span class="truncate">
                    {{ getCategoryDisplayText() }}
                  </span>
                  <ChevronDown class="h-4 w-4 opacity-50 ml-2 flex-shrink-0" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-56">
                <DropdownMenuLabel>Filter by Category</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                  :model-value="!categoryFilter"
                  @update:model-value="() => handleCategoryFilter('')"
                  :disabled="loading"
                >
                  All Categories
                </DropdownMenuCheckboxItem>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                  v-for="(label, value) in categoryOptions"
                  :key="value"
                  :model-value="isCategorySelected(value)"
                  @update:model-value="() => handleCategoryFilter(value)"
                  :disabled="loading"
                >
                  {{ label }}
                </DropdownMenuCheckboxItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>

          <!-- Status Filter Dropdown -->
          <div>
            <label class="block text-sm font-medium text-foreground mb-2">Status</label>
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="outline" class="w-full justify-between" :disabled="loading">
                  <span class="truncate">
                    {{ getStatusDisplayText() }}
                  </span>
                  <ChevronDown class="h-4 w-4 opacity-50 ml-2 flex-shrink-0" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-56">
                <DropdownMenuLabel>Filter by Status</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                  :model-value="!statusFilter"
                  @update:model-value="() => handleStatusFilter('')"
                  :disabled="loading"
                >
                  All Status
                </DropdownMenuCheckboxItem>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                  v-for="(label, value) in statusOptions"
                  :key="value"
                  :model-value="isStatusSelected(value)"
                  @update:model-value="() => handleStatusFilter(value)"
                  :disabled="loading"
                >
                  {{ label }}
                </DropdownMenuCheckboxItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>

          <!-- Difficulty Filter Dropdown -->
          <div>
            <label class="block text-sm font-medium text-foreground mb-2">Difficulty</label>
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="outline" class="w-full justify-between" :disabled="loading">
                  <span class="truncate">
                    {{ getDifficultyDisplayText() }}
                  </span>
                  <ChevronDown class="h-4 w-4 opacity-50 ml-2 flex-shrink-0" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-56">
                <DropdownMenuLabel>Filter by Difficulty</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                  :model-value="!difficultyFilter"
                  @update:model-value="() => handleDifficultyFilter('')"
                  :disabled="loading"
                >
                  All Levels
                </DropdownMenuCheckboxItem>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                  v-for="(label, value) in difficultyOptions"
                  :key="value"
                  :model-value="isDifficultySelected(value)"
                  @update:model-value="() => handleDifficultyFilter(value)"
                  :disabled="loading"
                >
                  {{ label }}
                </DropdownMenuCheckboxItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>

          <!-- Clear Filters -->
          <div class="flex items-end">
            <Button
              @click="clearFilters"
              variant="outline"
              class="w-full"
              :disabled="loading"
            >
              Clear Filters
            </Button>
          </div>
        </div>

        <!-- Active Filters Display -->
        <div v-if="categoryFilter || statusFilter || difficultyFilter" class="mt-4 flex flex-wrap gap-2">
          <div 
            v-if="categoryFilter" 
            class="inline-flex items-center gap-1 bg-primary/10 text-primary px-2 py-1 rounded-md text-sm"
          >
            Category: {{ categoryOptions[categoryFilter] }}
            <button 
              @click="handleCategoryFilter('')"
              class="hover:bg-primary/20 rounded-full p-0.5"
              :disabled="loading"
            >
              ×
            </button>
          </div>
          <div 
            v-if="statusFilter" 
            class="inline-flex items-center gap-1 bg-primary/10 text-primary px-2 py-1 rounded-md text-sm"
          >
            Status: {{ statusOptions[statusFilter] }}
            <button 
              @click="handleStatusFilter('')"
              class="hover:bg-primary/20 rounded-full p-0.5"
              :disabled="loading"
            >
              ×
            </button>
          </div>
          <div 
            v-if="difficultyFilter" 
            class="inline-flex items-center gap-1 bg-primary/10 text-primary px-2 py-1 rounded-md text-sm"
          >
            Difficulty: {{ difficultyOptions[difficultyFilter] }}
            <button 
              @click="handleDifficultyFilter('')"
              class="hover:bg-primary/20 rounded-full p-0.5"
              :disabled="loading"
            >
              ×
            </button>
          </div>
        </div>

        <!-- Results Count -->
        <div class="flex justify-between items-center pt-4 border-t mt-4">
          <div class="text-sm text-muted-foreground">
            Showing {{ total }} package(s)
          </div>
        </div>
      </div>

      <!-- Table using reusable component -->
      <DataTable
        :data="data"
        :columns="columns"
        :loading="loading"
        :search-query="searchQuery"
        :enable-row-selection="true"
        :enable-column-visibility="false"
      />

      <!-- Pagination using reusable component -->
      <DataTablePagination
        :current-page="currentPage"
        :total="total"
        :page-size="pageSize"
        :loading="loading"
        @previous="handlePreviousPage"
        @next="handleNextPage"
      />
    </div>

    <!-- Delete Confirmation Dialog -->
    <AlertDialog v-model:open="deleteDialogOpen">
      <AlertDialogContent class="max-w-[95vw] sm:max-w-md">
        <AlertDialogHeader>
          <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
          <AlertDialogDescription>
            This action cannot be undone. This will permanently delete the tourism package
            "{{ packageToDelete?.title }}" and remove it from our servers.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="flex flex-col sm:flex-row gap-2">
          <AlertDialogCancel :disabled="deleting" @click="deleteDialogOpen = false" class="w-full sm:w-auto">Cancel</AlertDialogCancel>
          <AlertDialogAction 
            @click="deletePackage"
            class="bg-destructive text-destructive-foreground hover:bg-destructive/90 w-full sm:w-auto"
            :disabled="deleting"
          >
            <div v-if="deleting" class="flex items-center justify-center space-x-2">
              <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-white"></div>
              <span>Deleting...</span>
            </div>
            <span v-else>Delete Package</span>
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>