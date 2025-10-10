<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { type ColumnDef } from "@tanstack/vue-table"
import { Eye, Edit, Trash2, Star, User, Users, Building2, MapPin, Phone, Mail, Calendar, Filter, Search, X, ChevronDown, GripVertical, Award, TrendingUp } from "lucide-vue-next"
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

export interface SangguniangBayanMember {
  id: string
  name: string
  position: string
  position_type: string
  bio: string | null
  email: string | null
  phone: string | null
  photo: string | null
  order: number
  is_active: boolean
  is_featured: boolean
  committees: string[] | null
  district: string | null
  term_start: string | null
  term_end: string | null
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
  members: {
    data: any[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  filters?: {
    search?: string
    position_type?: string
    status?: string
  }
  positionTypeOptions: Record<string, string>
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
  if (currentFlash?.info && currentFlash.info !== previousFlash?.info) {
    nextTick(() => {
      toast.info(currentFlash.info!);
    });
  }
}, { deep: true, immediate: true });

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
  {
    title: 'Sangguniang Bayan',
    href: '/sangguniang-bayan',
  },
];

// Use reactive data from props
const data = ref<SangguniangBayanMember[]>(props.members.data || [])
const loading = ref(false)
const total = ref(props.members.total || 0)
const currentPage = ref(props.members.current_page || 1)
const pageSize = ref(props.members.per_page || 10)

const searchQuery = ref(props.filters?.search || '')
const positionTypeFilter = ref(props.filters?.position_type || '')
const statusFilter = ref(props.filters?.status || '')

// Delete dialog state
const deleteDialogOpen = ref(false)
const memberToDelete = ref<SangguniangBayanMember | null>(null)
const deleting = ref(false)

// Drag and drop state
const dragState = ref({
  isDragging: false,
  draggedId: null as string | null,
  dragOverId: null as string | null
})

// Search timeout reference
let searchTimeout: number | null = null

// Function to reload the entire page with current filters
const reloadPage = () => {
  const params: any = {
    page: currentPage.value
  }

  if (searchQuery.value) params.search = searchQuery.value
  if (positionTypeFilter.value) params.position_type = positionTypeFilter.value
  if (statusFilter.value) params.status = statusFilter.value

  router.get('/sangguniang-bayan', params, {
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

const handlePositionTypeFilter = (value: string) => {
  positionTypeFilter.value = value
  currentPage.value = 1
  reloadPage()
}

const clearFilters = () => {
  searchQuery.value = ''
  positionTypeFilter.value = ''
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
const deleteMember = () => {
  if (!memberToDelete.value) return

  deleting.value = true
  
  router.delete(`/sangguniang-bayan/${memberToDelete.value.id}`, {
    preserveScroll: true,
    preserveState: false,
    onSuccess: () => {
      deleteDialogOpen.value = false
      memberToDelete.value = null
    },
    onError: (errors) => {
      console.error('Delete error:', errors)
      let errorMsg = 'Failed to delete member'
      
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

const openDeleteDialog = (member: SangguniangBayanMember) => {
  memberToDelete.value = member
  deleteDialogOpen.value = true
}

// Feature toggle handler
const handleFeatureToggle = (member: SangguniangBayanMember) => {
  router.post(`/sangguniang-bayan/${member.id}/toggle-featured`, {}, {
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
const handleStatusToggle = (member: SangguniangBayanMember) => {
  router.post(`/sangguniang-bayan/${member.id}/toggle-status`, {}, {
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

// Simple drag and drop handlers
const startDrag = (member: SangguniangBayanMember) => {
  dragState.value = {
    isDragging: true,
    draggedId: member.id,
    dragOverId: null
  }
}

const handleDragOver = (event: DragEvent, member: SangguniangBayanMember) => {
  event.preventDefault()
  if (dragState.value.draggedId !== member.id) {
    dragState.value.dragOverId = member.id
  }
}

const handleDragLeave = () => {
  dragState.value.dragOverId = null
}

const handleDrop = async (event: DragEvent, targetMember: SangguniangBayanMember) => {
  event.preventDefault()
  
  if (!dragState.value.draggedId || dragState.value.draggedId === targetMember.id) {
    resetDragState()
    return
  }

  try {
    const draggedMember = data.value.find(m => m.id === dragState.value.draggedId)
    if (!draggedMember) {
      resetDragState()
      return
    }

    // Update order locally for immediate feedback
    const updatedData = [...data.value]
    const draggedIndex = updatedData.findIndex(m => m.id === dragState.value.draggedId)
    const targetIndex = updatedData.findIndex(m => m.id === targetMember.id)
    
    if (draggedIndex === -1 || targetIndex === -1) {
      resetDragState()
      return
    }

    // Remove dragged item and insert at target position
    const [movedItem] = updatedData.splice(draggedIndex, 1)
    updatedData.splice(targetIndex, 0, movedItem)
    
    // Update order numbers
    updatedData.forEach((member, index) => {
      member.order = index + 1
    })
    
    // Update the reactive data - this should trigger table refresh
    data.value = updatedData

    // Send update to server - use preserveState: false to force refresh
    await handleOrderUpdate(draggedMember.id, targetIndex + 1)
    
  } catch (error) {
    console.error('Drop error:', error)
    toast.error('Failed to update member order')
    reloadPage()
  } finally {
    resetDragState()
  }
}

const resetDragState = () => {
  dragState.value = {
    isDragging: false,
    draggedId: null,
    dragOverId: null
  }
}

// Enhanced order update handler - force refresh after update
const handleOrderUpdate = async (memberId: string, newOrder: number) => {
  try {
    await router.post(`/sangguniang-bayan/${memberId}/update-order`, {
      order: newOrder
    }, {
      preserveScroll: true,
      preserveState: false, // Changed to false to force refresh
      onSuccess: () => {
        // The page will refresh automatically due to preserveState: false
        // Success message will be shown via flash message watcher
      },
      onError: (errors) => {
        console.error('Order update error:', errors)
        let errorMsg = 'Failed to update member order'
        if (typeof errors === 'string') {
          errorMsg = errors
        } else if (errors && typeof errors === 'object' && 'message' in errors) {
          errorMsg = (errors as any).message
        } else if (errors && typeof errors === 'object' && 'error' in errors) {
          errorMsg = (errors as any).error
        }
        toast.error(errorMsg)
        reloadPage()
      }
    })
  } catch (error) {
    console.error('Order update failed:', error)
    toast.error('Failed to update member order')
    reloadPage()
  }
}

// Utility functions
const getImageUrl = (imagePath: string | null) => {
  if (!imagePath) return '/images/default-avatar.png'
  return `/storage/${imagePath}`
}

const getPositionTypeIcon = (positionType: string) => {
  switch (positionType) {
    case 'regular': return User
    case 'sk_president': return Users
    case 'liga_president': return Building2
    case 'ip_representative': return Award
    default: return User
  }
}

const getPositionTypeBadgeVariant = (positionType: string) => {
  switch (positionType) {
    case 'sk_president': return 'secondary'
    case 'liga_president': return 'default'
    case 'ip_representative': return 'outline'
    default: return 'secondary'
  }
}

// Event handler for input events with proper typing
const handleSearchInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target) {
    handleSearch(target.value)
  }
}

// Helper functions for dropdown filters
const getPositionTypeDisplayText = () => {
  if (!positionTypeFilter.value) return 'All Positions'
  return props.positionTypeOptions[positionTypeFilter.value] || 'Select Position'
}

const getStatusDisplayText = () => {
  if (!statusFilter.value) return 'All Status'
  return props.statusOptions[statusFilter.value] || 'Select Status'
}

const isPositionTypeSelected = (value: string) => {
  return positionTypeFilter.value === value
}

const isStatusSelected = (value: string) => {
  return statusFilter.value === value
}

// Columns definition
const columns: ColumnDef<SangguniangBayanMember>[] = [
  {
    accessorKey: "order",
    header: "Order",
    cell: ({ row }) => {
      const member = row.original
      const isBeingDragged = dragState.value.draggedId === member.id
      const isDragOver = dragState.value.dragOverId === member.id
      
      return h("div", { 
        class: `flex items-center justify-center w-8 cursor-grab active:cursor-grabbing transition-all duration-200 ${
          isBeingDragged ? 'opacity-50' : 
          isDragOver ? 'bg-blue-100 rounded' : 
          'opacity-100'
        }`,
        draggable: true,
        onDragstart: () => startDrag(member),
        onDragend: resetDragState,
      }, [
        h(GripVertical, { 
          class: `h-4 w-4 transition-colors ${
            isBeingDragged ? 'text-primary' : 
            isDragOver ? 'text-blue-600' : 
            'text-muted-foreground'
          }` 
        }),
      ])
    },
  },
  {
    accessorKey: "name",
    header: "Member Details",
    cell: ({ row }) => {
      const member = row.original
      const isDragOver = dragState.value.dragOverId === member.id
      
      return h("div", { 
        class: `flex items-start space-x-3 min-w-[300px] transition-all duration-200 ${
          isDragOver ? 'bg-blue-50 border-l-4 border-blue-400 pl-2' : ''
        }`,
        onDragover: (event: DragEvent) => handleDragOver(event, member),
        onDragleave: handleDragLeave,
        onDrop: (event: DragEvent) => handleDrop(event, member)
      }, [
        h("div", { class: "flex-shrink-0" }, [
          h("img", {
            src: getImageUrl(member.photo),
            alt: member.name,
            class: "w-12 h-12 rounded-full object-cover border"
          })
        ]),
        h("div", { class: "min-w-0 flex-1" }, [
          h("div", { class: "flex items-center space-x-2" }, [
            member.is_featured && h(Star, { class: "h-3 w-3 text-yellow-500 fill-yellow-500 flex-shrink-0" }),
            h("span", { class: `font-medium text-sm ${member.is_featured ? "text-yellow-700" : "text-foreground"}` }, member.name),
          ]),
          h("div", { class: "text-sm text-muted-foreground mt-1" }, [
            h("span", {}, member.position)
          ]),
          member.bio && h("div", { class: "text-xs text-muted-foreground mt-1 line-clamp-2" }, [
            h("span", {}, member.bio)
          ])
        ])
      ])
    },
  },
  {
    accessorKey: "position_type",
    header: "Position Type",
    cell: ({ row }) => {
      const member = row.original;
      const PositionIcon = getPositionTypeIcon(member.position_type);
      return h("div", { class: "flex items-center space-x-2 px-2" }, [
        h(PositionIcon, { class: "h-4 w-4 text-muted-foreground" }),
        h(Badge, { 
          variant: getPositionTypeBadgeVariant(member.position_type),
          class: "text-xs"
        }, props.positionTypeOptions[member.position_type] || member.position_type)
      ])
    },
  },
  {
    accessorKey: "committees",
    header: "Committees",
    cell: ({ row }) => {
      const committees = row.original.committees;
      if (!committees || committees.length === 0) {
        return h("span", { class: "text-sm text-muted-foreground px-2" }, "No committees");
      }
      return h("div", { class: "px-2" }, [
        h("div", { class: "flex flex-wrap gap-1" }, 
          committees.slice(0, 2).map((committee: string) => 
            h(Badge, { 
              variant: "outline",
              class: "text-xs"
            }, committee)
          )
        ),
        committees.length > 2 && h("div", { class: "text-xs text-muted-foreground mt-1" }, 
          `+${committees.length - 2} more`
        )
      ])
    },
  },
  {
    accessorKey: "status",
    header: "Status",
    cell: ({ row }) => {
      const member = row.original
      return h("div", { class: "flex flex-col space-y-1 px-2" }, [
        h(Badge, { 
          variant: member.is_active ? "default" : "outline",
          class: "text-xs"
        }, member.is_active ? "Active" : "Inactive"),
        member.is_featured && h(Badge, { 
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
      const member = row.original

      const handleView = () => {
        router.get(`/sangguniang-bayan/${member.id}`)
      }

      const handleEdit = () => {
        router.get(`/sangguniang-bayan/${member.id}/edit`)
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
            h(TooltipContent, {}, "View Member")
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
            h(TooltipContent, {}, "Edit Member")
          ]),
          h(Tooltip, {}, [
            h(TooltipTrigger, { asChild: true }, [
              h(Button, {
                variant: "ghost",
                size: "sm",
                onClick: () => handleFeatureToggle(member),
                class: `h-8 w-8 p-0 hover:bg-accent ${getFeatureButtonClass(member.is_featured)}`
              }, [
                h(Star, { class: `h-4 w-4 ${member.is_featured ? 'fill-current' : ''}` }),
              ])
            ]),
            h(TooltipContent, {}, member.is_featured ? "Remove from Featured" : "Mark as Featured")
          ]),
          h(Tooltip, {}, [
            h(TooltipTrigger, { asChild: true }, [
              h(Button, {
                variant: "ghost",
                size: "sm",
                onClick: () => handleStatusToggle(member),
                class: `h-8 w-8 p-0 hover:bg-accent ${getStatusButtonClass(member.is_active)}`
              }, [
                h(TrendingUp, { class: "h-4 w-4" }),
              ])
            ]),
            h(TooltipContent, {}, member.is_active ? "Deactivate Member" : "Activate Member")
          ]),
          h(Tooltip, {}, [
            h(TooltipTrigger, { asChild: true }, [
              h(Button, {
                variant: "ghost",
                size: "sm",
                onClick: () => openDeleteDialog(member),
                class: "h-8 w-8 p-0 text-destructive hover:text-destructive/90 hover:bg-accent"
              }, [
                h(Trash2, { class: "h-4 w-4" }),
              ])
            ]),
            h(TooltipContent, {}, "Delete Member")
          ]),
        ])
      ])
    },
    enableHiding: false,
  },
]

// Watchers to update reactive data when props change
watch(() => props.members, (newMembers) => {
  data.value = newMembers.data || []
  total.value = newMembers.total || 0
  currentPage.value = newMembers.current_page || 1
  pageSize.value = newMembers.per_page || 10
}, { deep: true })

watch(() => props.filters, (newFilters) => {
  searchQuery.value = newFilters?.search || ''
  positionTypeFilter.value = newFilters?.position_type || ''
  statusFilter.value = newFilters?.status || ''
}, { deep: true })
</script>

<template>
  <Head title="Sangguniang Bayan Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="text-center sm:text-left">
          <h1 class="text-3xl font-bold text-foreground">Sangguniang Bayan</h1>
          <p class="text-muted-foreground mt-2">Manage municipal council members and their information</p>
        </div>
        
        <Link href="/sangguniang-bayan/create" as="button" class="w-full sm:w-auto">
          <Button class="w-full sm:w-auto">
            <User class="h-4 w-4 mr-2" />
            Add New Member
          </Button>
        </Link>
      </div>

      <!-- Drag and Drop Instructions -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center space-x-2 text-blue-800">
          <GripVertical class="h-4 w-4" />
          <p class="text-sm font-medium">Drag and drop members using the grip icon to reorder them</p>
        </div>
      </div>

      <!-- Filters Section -->
      <div class="bg-card rounded-lg border p-4 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-foreground mb-2">Search</label>
            <div class="relative">
              <Input
                v-model="searchQuery"
                type="text"
                placeholder="Search members, positions, committees..."
                class="w-full pr-10"
                @input="handleSearchInput"
                :disabled="loading"
              />
              <div v-if="loading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary"></div>
              </div>
            </div>
          </div>

          <!-- Position Type Filter Dropdown -->
          <div>
            <label class="block text-sm font-medium text-foreground mb-2">Position Type</label>
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="outline" class="w-full justify-between" :disabled="loading">
                  <span class="truncate">
                    {{ getPositionTypeDisplayText() }}
                  </span>
                  <ChevronDown class="h-4 w-4 opacity-50 ml-2 flex-shrink-0" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-56">
                <DropdownMenuLabel>Filter by Position Type</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                  :model-value="!positionTypeFilter"
                  @update:model-value="() => handlePositionTypeFilter('')"
                  :disabled="loading"
                >
                  All Positions
                </DropdownMenuCheckboxItem>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                  v-for="(label, value) in positionTypeOptions"
                  :key="value"
                  :model-value="isPositionTypeSelected(value)"
                  @update:model-value="() => handlePositionTypeFilter(value)"
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
        <div v-if="positionTypeFilter || statusFilter" class="mt-4 flex flex-wrap gap-2">
          <div 
            v-if="positionTypeFilter" 
            class="inline-flex items-center gap-1 bg-primary/10 text-primary px-2 py-1 rounded-md text-sm"
          >
            Position: {{ positionTypeOptions[positionTypeFilter] }}
            <button 
              @click="handlePositionTypeFilter('')"
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
            Showing {{ total }} member(s)
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
        :enable-row-selection="false"
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
            This action cannot be undone. This will permanently delete the member
            "{{ memberToDelete?.name }}" and remove it from our servers.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="flex flex-col sm:flex-row gap-2">
          <AlertDialogCancel :disabled="deleting" @click="deleteDialogOpen = false" class="w-full sm:w-auto">Cancel</AlertDialogCancel>
          <AlertDialogAction 
            @click="deleteMember"
            class="bg-destructive text-destructive-foreground hover:bg-destructive/90 w-full sm:w-auto"
            :disabled="deleting"
          >
            <div v-if="deleting" class="flex items-center justify-center space-x-2">
              <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-white"></div>
              <span>Deleting...</span>
            </div>
            <span v-else>Delete Member</span>
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>