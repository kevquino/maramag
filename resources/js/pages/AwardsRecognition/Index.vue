<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { type ColumnDef } from "@tanstack/vue-table"
import { Eye, Edit, Trash2, Star, Award, Calendar, MapPin, Users, TrendingUp, Filter, Search, X, ChevronDown, Building2, User, Users as UsersIcon } from "lucide-vue-next"
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

export interface AwardsRecognition {
  id: string
  title: string
  description: string
  awarding_body: string
  category: string
  award_date: string
  received_date: string | null
  location: string | null
  award_type: string
  scope: string
  significance: string | null
  criteria: string | null
  recipient_type: string
  recipient_name: string
  contact_person: string | null
  contact_email: string | null
  contact_phone: string | null
  supporting_documents: string[] | null
  featured_image: string | null
  gallery_images: string[] | null
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
  awards: {
    data: any[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  filters?: {
    search?: string
    category?: string
    award_type?: string
    scope?: string
    status?: string
  }
  categoryOptions: Record<string, string>
  awardTypeOptions: Record<string, string>
  scopeOptions: Record<string, string>
  recipientTypeOptions: Record<string, string>
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
    title: 'Awards & Recognition',
    href: '/awards-recognition',
  },
];

// Use reactive data from props
const data = ref<AwardsRecognition[]>(props.awards.data || [])
const loading = ref(false)
const total = ref(props.awards.total || 0)
const currentPage = ref(props.awards.current_page || 1)
const pageSize = ref(props.awards.per_page || 10)

const searchQuery = ref(props.filters?.search || '')
const categoryFilter = ref(props.filters?.category || '')
const awardTypeFilter = ref(props.filters?.award_type || '')
const scopeFilter = ref(props.filters?.scope || '')
const statusFilter = ref(props.filters?.status || '')

// Delete dialog state
const deleteDialogOpen = ref(false)
const awardToDelete = ref<AwardsRecognition | null>(null)
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
  if (awardTypeFilter.value) params.award_type = awardTypeFilter.value
  if (scopeFilter.value) params.scope = scopeFilter.value
  if (statusFilter.value) params.status = statusFilter.value

  router.get('/awards-recognition', params, {
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

const handleAwardTypeFilter = (value: string) => {
  awardTypeFilter.value = value
  currentPage.value = 1
  reloadPage()
}

const handleScopeFilter = (value: string) => {
  scopeFilter.value = value
  currentPage.value = 1
  reloadPage()
}

const clearFilters = () => {
  searchQuery.value = ''
  categoryFilter.value = ''
  awardTypeFilter.value = ''
  scopeFilter.value = ''
  statusFilter.value = ''
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
const deleteAward = () => {
  if (!awardToDelete.value) return

  deleting.value = true
  
  router.delete(`/awards-recognition/${awardToDelete.value.id}`, {
    preserveScroll: true,
    preserveState: false,
    onSuccess: () => {
      deleteDialogOpen.value = false
      awardToDelete.value = null
    },
    onError: (errors) => {
      console.error('Delete error:', errors)
      let errorMsg = 'Failed to delete award'
      
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

const openDeleteDialog = (award: AwardsRecognition) => {
  awardToDelete.value = award
  deleteDialogOpen.value = true
}

// Feature toggle handler
const handleFeatureToggle = (award: AwardsRecognition) => {
  router.post(`/awards-recognition/${award.id}/toggle-featured`, {}, {
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
const handleStatusToggle = (award: AwardsRecognition) => {
  router.post(`/awards-recognition/${award.id}/toggle-status`, {}, {
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
const formatDate = (dateString: string | null) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getImageUrl = (imagePath: string | null) => {
  if (!imagePath) return null;
  return `/storage/${imagePath}`;
}

const getRecipientIcon = (recipientType: string) => {
  switch (recipientType) {
    case 'individual': return User
    case 'team': return UsersIcon
    case 'department': return Building2
    case 'organization': return Building2
    default: return User
  }
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

const getAwardTypeDisplayText = () => {
  if (!awardTypeFilter.value) return 'All Types'
  return props.awardTypeOptions[awardTypeFilter.value] || 'Select Type'
}

const getScopeDisplayText = () => {
  if (!scopeFilter.value) return 'All Scopes'
  return props.scopeOptions[scopeFilter.value] || 'Select Scope'
}

const getStatusDisplayText = () => {
  if (!statusFilter.value) return 'All Status'
  return props.statusOptions[statusFilter.value] || 'Select Status'
}

const isCategorySelected = (value: string) => {
  return categoryFilter.value === value
}

const isAwardTypeSelected = (value: string) => {
  return awardTypeFilter.value === value
}

const isScopeSelected = (value: string) => {
  return scopeFilter.value === value
}

const isStatusSelected = (value: string) => {
  return statusFilter.value === value
}

// Columns definition
const columns: ColumnDef<AwardsRecognition>[] = [
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
    header: "Award Details",
    cell: ({ row }) => {
      const award = row.original;
      const RecipientIcon = getRecipientIcon(award.recipient_type);
      return h("div", { class: "flex items-start space-x-3 min-w-[300px]" }, [
        award.featured_image ? h("div", { class: "flex-shrink-0" }, [
          h("img", {
            src: getImageUrl(award.featured_image),
            alt: award.title,
            class: "w-12 h-12 rounded-lg object-cover border"
          })
        ]) : h("div", { class: "flex-shrink-0 w-12 h-12 rounded-lg bg-muted flex items-center justify-center" }, [
          h(Award, { class: "h-5 w-5 text-muted-foreground" })
        ]),
        h("div", { class: "min-w-0 flex-1" }, [
          h("div", { class: "flex items-center space-x-2" }, [
            award.is_featured && h(Star, { class: "h-3 w-3 text-yellow-500 fill-yellow-500 flex-shrink-0" }),
            h("span", { class: `font-medium text-sm ${award.is_featured ? "text-yellow-700" : "text-foreground"}` }, award.title || 'No title'),
          ]),
          h("div", { class: "flex items-center space-x-1 text-xs text-muted-foreground mt-1" }, [
            h(RecipientIcon, { class: "h-3 w-3 flex-shrink-0" }),
            h("span", { class: "truncate" }, award.recipient_name)
          ]),
          h("div", { class: "text-xs text-muted-foreground mt-1" }, [
            h("span", {}, `By: ${award.awarding_body}`)
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
    accessorKey: "award_type",
    header: "Type",
    cell: ({ row }) => h("div", { class: "px-2" }, [
      h(Badge, { 
        variant: "outline",
        class: "text-xs"
      }, props.awardTypeOptions[row.original.award_type] || row.original.award_type)
    ]),
  },
  {
    accessorKey: "scope",
    header: "Scope",
    cell: ({ row }) => h("div", { class: "px-2" }, [
      h(Badge, { 
        variant: "secondary",
        class: "text-xs"
      }, props.scopeOptions[row.original.scope] || row.original.scope)
    ]),
  },
  {
    accessorKey: "award_date",
    header: "Award Date",
    cell: ({ row }) => h("div", { class: "flex items-center space-x-1 px-2" }, [
      h(Calendar, { class: "h-3 w-3 text-muted-foreground" }),
      h("span", { class: "text-sm" }, formatDate(row.original.award_date))
    ]),
  },
  {
    accessorKey: "location",
    header: "Location",
    cell: ({ row }) => row.original.location ? h("div", { class: "flex items-center space-x-1 px-2" }, [
      h(MapPin, { class: "h-3 w-3 text-muted-foreground" }),
      h("span", { class: "text-sm truncate" }, row.original.location)
    ]) : h("span", { class: "text-sm text-muted-foreground px-2" }, "N/A"),
  },
  {
    accessorKey: "status",
    header: "Status",
    cell: ({ row }) => {
      const award = row.original
      return h("div", { class: "flex flex-col space-y-1 px-2" }, [
        h(Badge, { 
          variant: award.is_active ? "default" : "outline",
          class: "text-xs"
        }, award.is_active ? "Active" : "Inactive"),
        award.is_featured && h(Badge, { 
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
      const award = row.original

      const handleView = () => {
        router.get(`/awards-recognition/${award.id}`)
      }

      const handleEdit = () => {
        router.get(`/awards-recognition/${award.id}/edit`)
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
            h(TooltipContent, {}, "View Award")
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
            h(TooltipContent, {}, "Edit Award")
          ]),
          h(Tooltip, {}, [
            h(TooltipTrigger, { asChild: true }, [
              h(Button, {
                variant: "ghost",
                size: "sm",
                onClick: () => handleFeatureToggle(award),
                class: `h-8 w-8 p-0 hover:bg-accent ${getFeatureButtonClass(award.is_featured)}`
              }, [
                h(Star, { class: `h-4 w-4 ${award.is_featured ? 'fill-current' : ''}` }),
              ])
            ]),
            h(TooltipContent, {}, award.is_featured ? "Remove from Featured" : "Mark as Featured")
          ]),
          h(Tooltip, {}, [
            h(TooltipTrigger, { asChild: true }, [
              h(Button, {
                variant: "ghost",
                size: "sm",
                onClick: () => handleStatusToggle(award),
                class: `h-8 w-8 p-0 hover:bg-accent ${getStatusButtonClass(award.is_active)}`
              }, [
                h(TrendingUp, { class: "h-4 w-4" }),
              ])
            ]),
            h(TooltipContent, {}, award.is_active ? "Deactivate Award" : "Activate Award")
          ]),
          h(Tooltip, {}, [
            h(TooltipTrigger, { asChild: true }, [
              h(Button, {
                variant: "ghost",
                size: "sm",
                onClick: () => openDeleteDialog(award),
                class: "h-8 w-8 p-0 text-destructive hover:text-destructive/90 hover:bg-accent"
              }, [
                h(Trash2, { class: "h-4 w-4" }),
              ])
            ]),
            h(TooltipContent, {}, "Delete Award")
          ]),
        ])
      ])
    },
    enableHiding: false,
  },
]

// Watchers to update reactive data when props change
watch(() => props.awards, (newAwards) => {
  data.value = newAwards.data || []
  total.value = newAwards.total || 0
  currentPage.value = newAwards.current_page || 1
  pageSize.value = newAwards.per_page || 10
}, { deep: true })

watch(() => props.filters, (newFilters) => {
  searchQuery.value = newFilters?.search || ''
  categoryFilter.value = newFilters?.category || ''
  awardTypeFilter.value = newFilters?.award_type || ''
  scopeFilter.value = newFilters?.scope || ''
  statusFilter.value = newFilters?.status || ''
}, { deep: true })
</script>

<template>
  <Head title="Awards & Recognition Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="text-center sm:text-left">
          <h1 class="text-3xl font-bold text-foreground">Awards & Recognition</h1>
          <p class="text-muted-foreground mt-2">Manage awards, honors, and recognition received by the municipality</p>
        </div>
        
        <Link href="/awards-recognition/create" as="button" class="w-full sm:w-auto">
          <Button class="w-full sm:w-auto">
            <Award class="h-4 w-4 mr-2" />
            Add New Award
          </Button>
        </Link>
      </div>

      <!-- Filters Section -->
      <div class="bg-card rounded-lg border p-4 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
          <!-- Search -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-foreground mb-2">Search</label>
            <div class="relative">
              <Input
                v-model="searchQuery"
                type="text"
                placeholder="Search awards, recipients, awarding bodies..."
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

          <!-- Award Type Filter Dropdown -->
          <div>
            <label class="block text-sm font-medium text-foreground mb-2">Award Type</label>
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="outline" class="w-full justify-between" :disabled="loading">
                  <span class="truncate">
                    {{ getAwardTypeDisplayText() }}
                  </span>
                  <ChevronDown class="h-4 w-4 opacity-50 ml-2 flex-shrink-0" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-56">
                <DropdownMenuLabel>Filter by Type</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                  :model-value="!awardTypeFilter"
                  @update:model-value="() => handleAwardTypeFilter('')"
                  :disabled="loading"
                >
                  All Types
                </DropdownMenuCheckboxItem>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                  v-for="(label, value) in awardTypeOptions"
                  :key="value"
                  :model-value="isAwardTypeSelected(value)"
                  @update:model-value="() => handleAwardTypeFilter(value)"
                  :disabled="loading"
                >
                  {{ label }}
                </DropdownMenuCheckboxItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>

          <!-- Scope Filter Dropdown -->
          <div>
            <label class="block text-sm font-medium text-foreground mb-2">Scope</label>
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="outline" class="w-full justify-between" :disabled="loading">
                  <span class="truncate">
                    {{ getScopeDisplayText() }}
                  </span>
                  <ChevronDown class="h-4 w-4 opacity-50 ml-2 flex-shrink-0" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-56">
                <DropdownMenuLabel>Filter by Scope</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                  :model-value="!scopeFilter"
                  @update:model-value="() => handleScopeFilter('')"
                  :disabled="loading"
                >
                  All Scopes
                </DropdownMenuCheckboxItem>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                  v-for="(label, value) in scopeOptions"
                  :key="value"
                  :model-value="isScopeSelected(value)"
                  @update:model-value="() => handleScopeFilter(value)"
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
        </div>

        <!-- Active Filters Display -->
        <div v-if="categoryFilter || awardTypeFilter || scopeFilter || statusFilter" class="mt-4 flex flex-wrap gap-2">
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
            v-if="awardTypeFilter" 
            class="inline-flex items-center gap-1 bg-primary/10 text-primary px-2 py-1 rounded-md text-sm"
          >
            Type: {{ awardTypeOptions[awardTypeFilter] }}
            <button 
              @click="handleAwardTypeFilter('')"
              class="hover:bg-primary/20 rounded-full p-0.5"
              :disabled="loading"
            >
              ×
            </button>
          </div>
          <div 
            v-if="scopeFilter" 
            class="inline-flex items-center gap-1 bg-primary/10 text-primary px-2 py-1 rounded-md text-sm"
          >
            Scope: {{ scopeOptions[scopeFilter] }}
            <button 
              @click="handleScopeFilter('')"
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
        </div>

        <!-- Results Count -->
        <div class="flex justify-between items-center pt-4 border-t mt-4">
          <div class="text-sm text-muted-foreground">
            Showing {{ total }} award(s)
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
            This action cannot be undone. This will permanently delete the award
            "{{ awardToDelete?.title }}" and remove it from our servers.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="flex flex-col sm:flex-row gap-2">
          <AlertDialogCancel :disabled="deleting" @click="deleteDialogOpen = false" class="w-full sm:w-auto">Cancel</AlertDialogCancel>
          <AlertDialogAction 
            @click="deleteAward"
            class="bg-destructive text-destructive-foreground hover:bg-destructive/90 w-full sm:w-auto"
            :disabled="deleting"
          >
            <div v-if="deleting" class="flex items-center justify-center space-x-2">
              <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-white"></div>
              <span>Deleting...</span>
            </div>
            <span v-else>Delete Award</span>
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>