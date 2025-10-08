<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { type ColumnDef } from "@tanstack/vue-table"
import { Eye, Edit, Trash2, Star, Calendar, FileText } from "lucide-vue-next"
import { h, ref, watch, nextTick } from "vue"
import { router, Head, Link, usePage } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'

import { Button } from "@/components/ui/button"
import { Checkbox } from "@/components/ui/checkbox"
import { Badge } from "@/components/ui/badge"
import DataTable from '@/components/DataTable.vue'
import DataTablePagination from '@/components/DataTablePagination.vue'
import DataTableFilters from '@/components/DataTableFilters.vue'
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

export interface BidsAward {
  id: string
  title: string
  description: string
  reference_number: string
  bid_type: "open_tender" | "closed_tender" | "quotation" | "rfp"
  estimated_budget: number | null
  bid_opening_date: string | null
  bid_closing_date: string
  award_date: string | null
  status: "draft" | "published" | "opened" | "evaluated" | "awarded" | "cancelled"
  is_featured: boolean
  awarded_to: string | null
  awarded_amount: number | null
  award_remarks: string | null
  documents: any[] | null
  user: {
    id: string
    name: string
    email: string
  }
  created_at: string
  updated_at: string
}

const props = defineProps<{
  bidsAwards: {
    data: any[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  filters?: {
    search?: string
    status?: string
    bid_type?: string
  }
  statusOptions: Record<string, string>
  bidTypeOptions: Record<string, string>
}>();

const page = usePage();

// Watch for flash messages and show toasts
watch(() => page.props.flash, (newFlash, oldFlash) => {
  if (newFlash?.success && newFlash.success !== oldFlash?.success) {
    nextTick(() => {
      toast.success(newFlash.success);
    });
  }
  if (newFlash?.error && newFlash.error !== oldFlash?.error) {
    nextTick(() => {
      toast.error(newFlash.error);
    });
  }
}, { deep: true, immediate: true });

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
  {
    title: 'Bids & Awards',
    href: '/bids-awards',
  },
];

// Use reactive data from props
const data = ref<BidsAward[]>(props.bidsAwards.data || [])
const loading = ref(false)
const total = ref(props.bidsAwards.total || 0)
const currentPage = ref(props.bidsAwards.current_page || 1)
const pageSize = ref(props.bidsAwards.per_page || 10)

const searchQuery = ref(props.filters?.search || '')
const statusFilter = ref(props.filters?.status || '')
const bidTypeFilter = ref(props.filters?.bid_type || '')

// Delete dialog state
const deleteDialogOpen = ref(false)
const bidToDelete = ref<BidsAward | null>(null)
const deleting = ref(false)

// Search timeout reference
let searchTimeout: number | null = null

// Function to reload the entire page with current filters
const reloadPage = () => {
  const params: any = {
    page: currentPage.value
  }

  if (searchQuery.value) params.search = searchQuery.value
  if (statusFilter.value) params.status = statusFilter.value
  if (bidTypeFilter.value) params.bid_type = bidTypeFilter.value

  router.get('/bids-awards', params, {
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

const handleStatusFilter = (value: string) => {
  statusFilter.value = value
  currentPage.value = 1
  reloadPage()
}

const handleBidTypeFilter = (value: string) => {
  bidTypeFilter.value = value
  currentPage.value = 1
  reloadPage()
}

const clearFilters = () => {
  searchQuery.value = ''
  statusFilter.value = ''
  bidTypeFilter.value = ''
  currentPage.value = 1
  router.get('/bids-awards', {}, {
    preserveState: false,
    preserveScroll: true,
    onStart: () => loading.value = true,
    onFinish: () => loading.value = false,
  })
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
const deleteBid = () => {
  if (!bidToDelete.value) return

  deleting.value = true
  
  router.delete(`/bids-awards/${bidToDelete.value.id}`, {
    preserveScroll: true,
    preserveState: false,
    onSuccess: () => {
      deleteDialogOpen.value = false
      bidToDelete.value = null
      // Success toast will be shown from server flash message
    },
    onError: (errors) => {
      console.error('Delete error:', errors)
      let errorMsg = 'Failed to delete bid/award'
      
      if (typeof errors === 'string') {
        errorMsg = errors
      } else if (errors?.message) {
        errorMsg = errors.message
      } else if (errors?.error) {
        errorMsg = errors.error
      }
      
      toast.error(errorMsg)
    },
    onFinish: () => {
      deleting.value = false
    }
  })
}

const openDeleteDialog = (bid: BidsAward) => {
  bidToDelete.value = bid
  deleteDialogOpen.value = true
}

// Feature toggle handler
const handleFeatureToggle = (bid: BidsAward) => {
  router.post(`/bids-awards/${bid.id}/toggle-featured`, {}, {
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
      } else if (errors?.message) {
        errorMsg = errors.message
      } else if (errors?.error) {
        errorMsg = errors.error
      }
      toast.error(errorMsg)
    }
  })
}

// Utility functions
const formatCurrency = (amount: number | null) => {
  if (!amount) return 'N/A'
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount)
}

const formatDate = (dateString: string | null) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-PH')
}

// Columns definition
const columns: ColumnDef<BidsAward>[] = [
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
    header: "Title & Reference",
    cell: ({ row }) => h("div", { class: "font-medium max-w-xs" }, [
      h("div", { class: "flex items-center space-x-2" }, [
        row.original.is_featured && h(Star, { class: "h-3 w-3 text-yellow-500 fill-yellow-500" }),
        h("span", { class: `truncate ${row.original.is_featured ? "font-bold" : ""}` }, row.getValue("title") || 'No title'),
      ]),
      h("div", { class: "text-sm text-muted-foreground mt-1" }, `Ref: ${row.original.reference_number}`)
    ]),
  },
  {
    accessorKey: "bid_type",
    header: "Type & Budget",
    cell: ({ row }) => h("div", { class: "" }, [
      h("div", { class: "font-medium" }, props.bidTypeOptions[row.original.bid_type] || row.original.bid_type),
      row.original.estimated_budget && h("div", { class: "text-sm text-muted-foreground mt-1" }, 
        formatCurrency(row.original.estimated_budget)
      )
    ]),
  },
  {
    accessorKey: "bid_closing_date",
    header: "Dates",
    cell: ({ row }) => h("div", { class: "" }, [
      h("div", { class: "flex items-center space-x-1 text-sm" }, [
        h(Calendar, { class: "h-3 w-3" }),
        h("span", `Close: ${formatDate(row.original.bid_closing_date)}`)
      ]),
      row.original.award_date && h("div", { class: "flex items-center space-x-1 text-sm text-muted-foreground mt-1" }, [
        h(FileText, { class: "h-3 w-3" }),
        h("span", `Award: ${formatDate(row.original.award_date)}`)
      ])
    ]),
  },
  {
    accessorKey: "status",
    header: "Status",
    cell: ({ row }) => {
      const status = row.getValue("status") as string
      const statusConfig: Record<string, { variant: "default" | "secondary" | "destructive" | "outline", label: string }> = {
        draft: { variant: "outline", label: "Draft" },
        published: { variant: "default", label: "Published" },
        opened: { variant: "secondary", label: "Opened" },
        evaluated: { variant: "default", label: "Evaluated" },
        awarded: { variant: "default", label: "Awarded" },
        cancelled: { variant: "destructive", label: "Cancelled" }
      }
      const config = statusConfig[status] || statusConfig.draft
      return h("div", { class: "space-y-1" }, [
        h(Badge, { 
          variant: config.variant
        }, config.label),
        row.original.awarded_to && h("div", { class: "text-xs text-muted-foreground truncate max-w-[120px]" }, 
          row.original.awarded_to
        )
      ])
    },
  },
  {
    id: "actions",
    enableHiding: false,
    cell: ({ row }) => {
      const bid = row.original

      const handleView = () => {
        router.get(`/bids-awards/${bid.id}`)
      }

      const handleEdit = () => {
        router.get(`/bids-awards/${bid.id}/edit`)
      }

      const getFeatureButtonClass = (isFeatured: boolean) => {
        return isFeatured 
          ? 'h-8 w-8 p-0 text-yellow-600 hover:text-yellow-700' 
          : 'h-8 w-8 p-0 text-gray-600 hover:text-gray-700'
      }

      return h("div", { class: "flex space-x-2" }, [
        h(Button, {
          variant: "ghost",
          size: "sm",
          onClick: handleView,
          class: "h-8 w-8 p-0"
        }, [
          h(Eye, { class: "h-4 w-4" }),
          h("span", { class: "sr-only" }, "View")
        ]),
        h(Button, {
          variant: "ghost",
          size: "sm",
          onClick: handleEdit,
          class: "h-8 w-8 p-0"
        }, [
          h(Edit, { class: "h-4 w-4" }),
          h("span", { class: "sr-only" }, "Edit")
        ]),
        h(Button, {
          variant: "ghost",
          size: "sm",
          onClick: () => handleFeatureToggle(bid),
          class: getFeatureButtonClass(bid.is_featured)
        }, [
          h(Star, { class: `h-4 w-4 ${bid.is_featured ? 'fill-current' : ''}` }),
          h("span", { class: "sr-only" }, bid.is_featured ? "Unfeature" : "Feature")
        ]),
        h(Button, {
          variant: "ghost",
          size: "sm",
          onClick: () => openDeleteDialog(bid),
          class: "h-8 w-8 p-0 text-destructive hover:text-destructive/90"
        }, [
          h(Trash2, { class: "h-4 w-4" }),
          h("span", { class: "sr-only" }, "Delete")
        ]),
      ])
    },
  },
]

// Watchers to update reactive data when props change
watch(() => props.bidsAwards, (newBidsAwards) => {
  data.value = newBidsAwards.data || []
  total.value = newBidsAwards.total || 0
  currentPage.value = newBidsAwards.current_page || 1
  pageSize.value = newBidsAwards.per_page || 10
}, { deep: true })

watch(() => props.filters, (newFilters) => {
  searchQuery.value = newFilters?.search || ''
  statusFilter.value = newFilters?.status || ''
  bidTypeFilter.value = newFilters?.bid_type || ''
}, { deep: true })
</script>

<template>
  <Head title="Bids & Awards Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="text-center sm:text-left">
          <h1 class="text-2xl font-bold text-foreground">Bids & Awards Management</h1>
          <p class="text-muted-foreground mt-1">Manage bids, tenders, and contract awards</p>
        </div>
        
        <Link href="/bids-awards/create" as="button" class="w-full sm:w-auto">
          <Button class="w-full sm:w-auto">
            <span class="mr-2">+</span>
            Add New Bid/Award
          </Button>
        </Link>
      </div>

      <!-- Filters using reusable component -->
      <DataTableFilters
        :search-query="searchQuery"
        :status-filter="statusFilter"
        :bid-type-filter="bidTypeFilter"
        :status-options="statusOptions"
        :bid-type-options="bidTypeOptions"
        :search-placeholder="'Search by title, reference...'"
        :loading="loading"
        @update:search-query="handleSearch"
        @update:status-filter="handleStatusFilter"
        @update:bid-type-filter="handleBidTypeFilter"
        @clear-filters="clearFilters"
      />

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
            This action cannot be undone. This will permanently delete the bid/award
            "{{ bidToDelete?.title }}" and remove it from our servers.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="flex flex-col sm:flex-row gap-2">
          <AlertDialogCancel :disabled="deleting" @click="deleteDialogOpen = false" class="w-full sm:w-auto">Cancel</AlertDialogCancel>
          <AlertDialogAction 
            @click="deleteBid"
            class="bg-destructive text-destructive-foreground hover:bg-destructive/90 w-full sm:w-auto"
            :disabled="deleting"
          >
            <div v-if="deleting" class="flex items-center justify-center space-x-2">
              <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-white"></div>
              <span>Deleting...</span>
            </div>
            <span v-else>Delete Bid/Award</span>
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>