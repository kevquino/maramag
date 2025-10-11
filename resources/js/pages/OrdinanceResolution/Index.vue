<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { type ColumnDef } from "@tanstack/vue-table"
import { Eye, Edit, Trash2, Star, FileText, Calendar, User, Users, Filter, Search, X, ChevronDown, Download, TrendingUp } from "lucide-vue-next"
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

export interface OrdinanceResolution {
  id: string
  title: string
  number: string
  type: string
  description: string | null
  date_approved: string
  date_effectivity: string | null
  sponsor: string | null
  co_sponsors: string[] | null
  status: string
  amendatory_to: string[] | null
  repealed_by: string[] | null
  categories: string[] | null
  file_path: string | null
  file_size: string | null
  file_type: string | null
  file_url: string | null
  is_featured: boolean
  is_active: boolean
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
  ordinanceResolutions: {
    data: any[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  filters?: {
    search?: string
    type?: string
    status?: string
    category?: string
  }
  typeOptions: Record<string, string>
  statusOptions: Record<string, string>
  categoryOptions: Record<string, string>
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
    title: 'Ordinance & Resolutions',
    href: '/ordinance-resolutions',
  },
];

// Use reactive data from props
const data = ref<OrdinanceResolution[]>(props.ordinanceResolutions.data || [])
const loading = ref(false)
const total = ref(props.ordinanceResolutions.total || 0)
const currentPage = ref(props.ordinanceResolutions.current_page || 1)
const pageSize = ref(props.ordinanceResolutions.per_page || 10)

const searchQuery = ref(props.filters?.search || '')
const typeFilter = ref(props.filters?.type || '')
const statusFilter = ref(props.filters?.status || '')
const categoryFilter = ref(props.filters?.category || '')

// Delete dialog state
const deleteDialogOpen = ref(false)
const ordinanceToDelete = ref<OrdinanceResolution | null>(null)
const deleting = ref(false)

// Search timeout reference
let searchTimeout: number | null = null

// Function to reload the entire page with current filters
const reloadPage = () => {
  const params: any = {
    page: currentPage.value
  }

  if (searchQuery.value) params.search = searchQuery.value
  if (typeFilter.value) params.type = typeFilter.value
  if (statusFilter.value) params.status = statusFilter.value
  if (categoryFilter.value) params.category = categoryFilter.value

  router.get('/ordinance-resolutions', params, {
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
const handleTypeFilter = (value: string) => {
  typeFilter.value = value
  currentPage.value = 1
  reloadPage()
}

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

const clearFilters = () => {
  searchQuery.value = ''
  typeFilter.value = ''
  statusFilter.value = ''
  categoryFilter.value = ''
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
const deleteOrdinance = () => {
  if (!ordinanceToDelete.value) return

  deleting.value = true
  
  router.delete(`/ordinance-resolutions/${ordinanceToDelete.value.id}`, {
    preserveScroll: true,
    preserveState: false,
    onSuccess: () => {
      deleteDialogOpen.value = false
      ordinanceToDelete.value = null
    },
    onError: (errors) => {
      console.error('Delete error:', errors)
      let errorMsg = 'Failed to delete ordinance/resolution'
      
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

const openDeleteDialog = (ordinance: OrdinanceResolution) => {
  ordinanceToDelete.value = ordinance
  deleteDialogOpen.value = true
}

// Feature toggle handler
const handleFeatureToggle = (ordinance: OrdinanceResolution) => {
  router.post(`/ordinance-resolutions/${ordinance.id}/toggle-featured`, {}, {
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
const handleStatusToggle = (ordinance: OrdinanceResolution) => {
  router.post(`/ordinance-resolutions/${ordinance.id}/toggle-status`, {}, {
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

// Download handler
const handleDownload = (ordinance: OrdinanceResolution) => {
  if (!ordinance.file_path) {
    toast.error('No file available for download')
    return
  }
  
  window.open(`/ordinance-resolutions/${ordinance.id}/download`, '_blank')
}

// Utility functions
const formatDate = (dateString: string | null) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getTypeBadgeVariant = (type: string) => {
  return type === 'ordinance' ? 'default' : 'secondary'
}

const getStatusBadgeVariant = (status: string) => {
  switch (status) {
    case 'active': return 'default'
    case 'amended': return 'outline'
    case 'repealed': return 'destructive'
    case 'pending': return 'secondary'
    default: return 'outline'
  }
}

const getCategoriesDisplay = (categories: string[] | null) => {
  if (!categories || categories.length === 0) return 'N/A'
  return categories.map(cat => props.categoryOptions[cat] || cat).join(', ')
}

// Event handler for input events with proper typing
const handleSearchInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target) {
    handleSearch(target.value);
  }
}

// Helper functions for dropdown filters
const getTypeDisplayText = () => {
  if (!typeFilter.value) return 'All Types'
  return props.typeOptions[typeFilter.value] || 'Select Type'
}

const getStatusDisplayText = () => {
  if (!statusFilter.value) return 'All Status'
  return props.statusOptions[statusFilter.value] || 'Select Status'
}

const getCategoryDisplayText = () => {
  if (!categoryFilter.value) return 'All Categories'
  return props.categoryOptions[categoryFilter.value] || 'Select Category'
}

const isTypeSelected = (value: string) => {
  return typeFilter.value === value
}

const isStatusSelected = (value: string) => {
  return statusFilter.value === value
}

const isCategorySelected = (value: string) => {
  return categoryFilter.value === value
}

// Columns definition
const columns: ColumnDef<OrdinanceResolution>[] = [
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
    accessorKey: "number",
    header: "Document Details",
    cell: ({ row }) => {
      const ordinance = row.original;
      return h("div", { class: "flex items-start space-x-3 min-w-[300px]" }, [
        h("div", { class: "flex-shrink-0 w-12 h-12 rounded-lg bg-muted flex items-center justify-center" }, [
          h(FileText, { class: "h-5 w-5 text-muted-foreground" })
        ]),
        h("div", { class: "min-w-0 flex-1" }, [
          h("div", { class: "flex items-center space-x-2" }, [
            ordinance.is_featured && h(Star, { class: "h-3 w-3 text-yellow-500 fill-yellow-500 flex-shrink-0" }),
            h("span", { class: `font-medium text-sm ${ordinance.is_featured ? "text-yellow-700" : "text-foreground"}` }, ordinance.number || 'No number'),
          ]),
          h("div", { class: "text-xs font-medium text-muted-foreground mt-1 truncate" }, ordinance.title),
          h("div", { class: "flex items-center space-x-1 text-xs text-muted-foreground mt-1" }, [
            h(User, { class: "h-3 w-3 flex-shrink-0" }),
            h("span", { class: "truncate" }, ordinance.sponsor || 'No sponsor')
          ])
        ])
      ])
    },
  },
  {
    accessorKey: "type",
    header: "Type",
    cell: ({ row }) => h("div", { class: "px-2" }, [
      h(Badge, { 
        variant: getTypeBadgeVariant(row.original.type),
        class: "text-xs"
      }, props.typeOptions[row.original.type] || row.original.type)
    ]),
  },
  {
    accessorKey: "categories",
    header: "Categories",
    cell: ({ row }) => h("div", { class: "px-2" }, [
      h("span", { class: "text-sm" }, getCategoriesDisplay(row.original.categories))
    ]),
  },
  {
    accessorKey: "date_approved",
    header: "Date Approved",
    cell: ({ row }) => h("div", { class: "flex items-center space-x-1 px-2" }, [
      h(Calendar, { class: "h-3 w-3 text-muted-foreground" }),
      h("span", { class: "text-sm" }, formatDate(row.original.date_approved))
    ]),
  },
  {
    accessorKey: "date_effectivity",
    header: "Date Effectivity",
    cell: ({ row }) => row.original.date_effectivity ? h("div", { class: "flex items-center space-x-1 px-2" }, [
      h(Calendar, { class: "h-3 w-3 text-muted-foreground" }),
      h("span", { class: "text-sm" }, formatDate(row.original.date_effectivity))
    ]) : h("span", { class: "text-sm text-muted-foreground px-2" }, "N/A"),
  },
  {
    accessorKey: "status",
    header: "Status",
    cell: ({ row }) => {
      const ordinance = row.original
      return h("div", { class: "flex flex-col space-y-1 px-2" }, [
        h(Badge, { 
          variant: getStatusBadgeVariant(ordinance.status),
          class: "text-xs"
        }, props.statusOptions[ordinance.status] || ordinance.status),
        ordinance.is_featured && h(Badge, { 
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
      const ordinance = row.original

      const handleView = () => {
        router.get(`/ordinance-resolutions/${ordinance.id}`)
      }

      const handleEdit = () => {
        router.get(`/ordinance-resolutions/${ordinance.id}/edit`)
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
            h(TooltipContent, {}, "View Document")
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
            h(TooltipContent, {}, "Edit Document")
          ]),
          ordinance.file_path && h(Tooltip, {}, [
            h(TooltipTrigger, { asChild: true }, [
              h(Button, {
                variant: "ghost",
                size: "sm",
                onClick: () => handleDownload(ordinance),
                class: "h-8 w-8 p-0 text-blue-600 hover:text-blue-700 hover:bg-accent"
              }, [
                h(Download, { class: "h-4 w-4" }),
              ])
            ]),
            h(TooltipContent, {}, "Download File")
          ]),
          h(Tooltip, {}, [
            h(TooltipTrigger, { asChild: true }, [
              h(Button, {
                variant: "ghost",
                size: "sm",
                onClick: () => handleFeatureToggle(ordinance),
                class: `h-8 w-8 p-0 hover:bg-accent ${getFeatureButtonClass(ordinance.is_featured)}`
              }, [
                h(Star, { class: `h-4 w-4 ${ordinance.is_featured ? 'fill-current' : ''}` }),
              ])
            ]),
            h(TooltipContent, {}, ordinance.is_featured ? "Remove from Featured" : "Mark as Featured")
          ]),
          h(Tooltip, {}, [
            h(TooltipTrigger, { asChild: true }, [
              h(Button, {
                variant: "ghost",
                size: "sm",
                onClick: () => handleStatusToggle(ordinance),
                class: `h-8 w-8 p-0 hover:bg-accent ${getStatusButtonClass(ordinance.is_active)}`
              }, [
                h(TrendingUp, { class: "h-4 w-4" }),
              ])
            ]),
            h(TooltipContent, {}, ordinance.is_active ? "Deactivate Document" : "Activate Document")
          ]),
          h(Tooltip, {}, [
            h(TooltipTrigger, { asChild: true }, [
              h(Button, {
                variant: "ghost",
                size: "sm",
                onClick: () => openDeleteDialog(ordinance),
                class: "h-8 w-8 p-0 text-destructive hover:text-destructive/90 hover:bg-accent"
              }, [
                h(Trash2, { class: "h-4 w-4" }),
              ])
            ]),
            h(TooltipContent, {}, "Delete Document")
          ]),
        ])
      ])
    },
    enableHiding: false,
  },
]

// Watchers to update reactive data when props change
watch(() => props.ordinanceResolutions, (newOrdinanceResolutions) => {
  data.value = newOrdinanceResolutions.data || []
  total.value = newOrdinanceResolutions.total || 0
  currentPage.value = newOrdinanceResolutions.current_page || 1
  pageSize.value = newOrdinanceResolutions.per_page || 10
}, { deep: true })

watch(() => props.filters, (newFilters) => {
  searchQuery.value = newFilters?.search || ''
  typeFilter.value = newFilters?.type || ''
  statusFilter.value = newFilters?.status || ''
  categoryFilter.value = newFilters?.category || ''
}, { deep: true })
</script>

<template>
  <Head title="Ordinance & Resolutions Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="text-center sm:text-left">
          <h1 class="text-3xl font-bold text-foreground">Ordinance & Resolutions</h1>
          <p class="text-muted-foreground mt-2">Manage municipal ordinances, resolutions, and legislative documents</p>
        </div>
        
        <Link href="/ordinance-resolutions/create" as="button" class="w-full sm:w-auto">
          <Button class="w-full sm:w-auto">
            <FileText class="h-4 w-4 mr-2" />
            Add New Document
          </Button>
        </Link>
      </div>

      <!-- Filters Section -->
      <div class="bg-card rounded-lg border p-4 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <!-- Search -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-foreground mb-2">Search</label>
            <div class="relative">
              <Input
                v-model="searchQuery"
                type="text"
                placeholder="Search titles, numbers, sponsors..."
                class="w-full pr-10"
                @input="handleSearchInput"
                :disabled="loading"
              />
              <div v-if="loading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary"></div>
              </div>
            </div>
          </div>

          <!-- Type Filter Dropdown -->
          <div>
            <label class="block text-sm font-medium text-foreground mb-2">Type</label>
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="outline" class="w-full justify-between" :disabled="loading">
                  <span class="truncate">
                    {{ getTypeDisplayText() }}
                  </span>
                  <ChevronDown class="h-4 w-4 opacity-50 ml-2 flex-shrink-0" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-56">
                <DropdownMenuLabel>Filter by Type</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                  :model-value="!typeFilter"
                  @update:model-value="() => handleTypeFilter('')"
                  :disabled="loading"
                >
                  All Types
                </DropdownMenuCheckboxItem>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                  v-for="(label, value) in typeOptions"
                  :key="value"
                  :model-value="isTypeSelected(value)"
                  @update:model-value="() => handleTypeFilter(value)"
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
        </div>

        <!-- Active Filters Display -->
        <div v-if="typeFilter || statusFilter || categoryFilter" class="mt-4 flex flex-wrap gap-2">
          <div 
            v-if="typeFilter" 
            class="inline-flex items-center gap-1 bg-primary/10 text-primary px-2 py-1 rounded-md text-sm"
          >
            Type: {{ typeOptions[typeFilter] }}
            <button 
              @click="handleTypeFilter('')"
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
        </div>

        <!-- Results Count -->
        <div class="flex justify-between items-center pt-4 border-t mt-4">
          <div class="text-sm text-muted-foreground">
            Showing {{ total }} document(s)
          </div>
          <Button
            @click="clearFilters"
            variant="outline"
            size="sm"
            :disabled="loading"
          >
            Clear All Filters
          </Button>
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
            This action cannot be undone. This will permanently delete the document
            "{{ ordinanceToDelete?.number }}" and remove it from our servers.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="flex flex-col sm:flex-row gap-2">
          <AlertDialogCancel :disabled="deleting" @click="deleteDialogOpen = false" class="w-full sm:w-auto">Cancel</AlertDialogCancel>
          <AlertDialogAction 
            @click="deleteOrdinance"
            class="bg-destructive text-destructive-foreground hover:bg-destructive/90 w-full sm:w-auto"
            :disabled="deleting"
          >
            <div v-if="deleting" class="flex items-center justify-center space-x-2">
              <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-white"></div>
              <span>Deleting...</span>
            </div>
            <span v-else>Delete Document</span>
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>