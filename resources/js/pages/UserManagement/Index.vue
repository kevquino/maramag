<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { type ColumnDef } from "@tanstack/vue-table"
import { Eye, Edit, Trash2, UserX, UserCheck, Mail, Building, Filter, Search, X, ChevronDown, Plus, CheckCircle, XCircle } from "lucide-vue-next"
import { h, ref, watch, nextTick, onMounted, computed } from "vue"
import { router, Head, Link, usePage } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'

import { Button } from "@/components/ui/button"
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
import DataTable, { type CardViewConfig } from '@/components/DataTable.vue'
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

export interface User {
  id: string
  name: string
  email: string
  role: string
  office: string
  is_active: boolean
  avatar: string | null
  last_login_at: string | null
  email_verified_at: string | null
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

interface Props {
  users: {
    data: User[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  filters?: {
    search?: string
    role?: string
    office?: string
    status?: string
  }
  roleOptions: Record<string, string>
  officeOptions: Record<string, string>
  statusOptions: Record<string, string>
}

const props = defineProps<Props>();

const page = usePage();

// Track shown flash messages to prevent duplicates
const shownFlashMessages = ref<Set<string>>(new Set());

// Function to show toast and track it
const showToast = (message: string, type: 'success' | 'error' | 'warning' | 'info') => {
  const messageKey = `${type}:${message}`;
  
  if (!shownFlashMessages.value.has(messageKey)) {
    shownFlashMessages.value.add(messageKey);
    
    nextTick(() => {
      switch (type) {
        case 'success':
          toast.success(message);
          break;
        case 'error':
          toast.error(message);
          break;
        case 'warning':
          toast.warning(message);
          break;
        case 'info':
          toast.info(message);
          break;
      }
      
      // Remove from tracking after a delay to allow same message to show again later
      setTimeout(() => {
        shownFlashMessages.value.delete(messageKey);
      }, 1000);
    });
  }
};

// Watch for flash messages and show toasts with proper typing
watch(() => page.props.flash as FlashMessages | undefined, (newFlash, oldFlash) => {
  const currentFlash = newFlash as FlashMessages | undefined;
  
  if (currentFlash?.success) {
    showToast(currentFlash.success, 'success');
  }
  if (currentFlash?.error) {
    showToast(currentFlash.error, 'error');
  }
  if (currentFlash?.warning) {
    showToast(currentFlash.warning, 'warning');
  }
  if (currentFlash?.info) {
    showToast(currentFlash.info, 'info');
  }
}, { deep: true, immediate: true });

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
  {
    title: 'User Management',
    href: '/user-management',
  },
];

// Use reactive data from props
const data = ref<User[]>(props.users.data || [])
const loading = ref(false)
const total = ref(props.users.total || 0)
const currentPage = ref(props.users.current_page || 1)
const pageSize = ref(props.users.per_page || 10)

const searchQuery = ref(props.filters?.search || '')
const roleFilter = ref(props.filters?.role || '')
const officeFilter = ref(props.filters?.office || '')
const statusFilter = ref(props.filters?.status || '')

// Delete dialog state
const deleteDialogOpen = ref(false)
const userToDelete = ref<User | null>(null)
const deleting = ref(false)

// Search timeout reference
let searchTimeout: number | null = null

// Card view configuration - UPDATED with status action
const cardViewConfig: CardViewConfig = {
  enabled: true,
  breakpoint: 768,
  fields: {
    role: {
      label: 'Role',
      format: (value: string) => props.roleOptions[value] || value
    },
    office: {
      label: 'Office',
      format: (value: string) => value || 'Not assigned'
    },
    is_active: {
      label: 'Status',
      format: (value: boolean) => value ? 'Active' : 'Inactive'
    },
    email_verified_at: {
      label: 'Email Verified',
      format: (value: string | null) => value ? 'Verified' : 'Unverified'
    }
  },
  actions: {
    view: true,
    edit: true,
    delete: true,
    status: true  // Added status toggle action
  }
}

// Function to reload the entire page with current filters
const reloadPage = () => {
  const params: any = {
    page: currentPage.value,
    per_page: pageSize.value
  }

  if (searchQuery.value) params.search = searchQuery.value
  if (roleFilter.value) params.role = roleFilter.value
  if (officeFilter.value) params.office = officeFilter.value
  if (statusFilter.value) params.status = statusFilter.value

  router.get('/user-management', params, {
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
const handleRoleFilter = (value: string) => {
  roleFilter.value = value
  currentPage.value = 1
  reloadPage()
}

const handleOfficeFilter = (value: string) => {
  officeFilter.value = value
  currentPage.value = 1
  reloadPage()
}

const handleStatusFilter = (value: string) => {
  statusFilter.value = value
  currentPage.value = 1
  reloadPage()
}

const clearFilters = () => {
  searchQuery.value = ''
  roleFilter.value = ''
  officeFilter.value = ''
  statusFilter.value = ''
  currentPage.value = 1
  reloadPage()
}

// Pagination handlers
const handlePageChange = (page: number) => {
  currentPage.value = page
  reloadPage()
}

const handlePageSizeChange = (size: number) => {
  pageSize.value = size
  currentPage.value = 1
  reloadPage()
}

// Delete handler
const deleteUser = () => {
  if (!userToDelete.value) return

  deleting.value = true
  
  router.delete(`/user-management/${userToDelete.value.id}`, {
    preserveScroll: true,
    preserveState: false,
    onSuccess: () => {
      deleteDialogOpen.value = false
      userToDelete.value = null
      // Don't show toast here - let the server flash message handle it
    },
    onError: (errors) => {
      console.error('Delete error:', errors)
      let errorMsg = 'Failed to delete user'
      
      if (typeof errors === 'string') {
        errorMsg = errors
      } else if (errors && typeof errors === 'object' && 'message' in errors) {
        errorMsg = (errors as any).message
      } else if (errors && typeof errors === 'object' && 'error' in errors) {
        errorMsg = (errors as any).error
      }
      
      showToast(errorMsg, 'error')
    },
    onFinish: () => {
      deleting.value = false
    }
  })
}

const openDeleteDialog = (user: User) => {
  userToDelete.value = user
  deleteDialogOpen.value = true
}

// Status toggle handler
const handleStatusToggle = (user: User) => {
  // Prevent users from deactivating themselves
  if (user.id === (page.props.auth.user as any).id) {
    showToast('You cannot deactivate your own account.', 'error')
    return
  }

  router.post(`/user-management/${user.id}/toggle-status`, {}, {
    preserveScroll: true,
    preserveState: false,
    onSuccess: () => {
      // Success toast will be shown from server flash message
      reloadPage(); // Refresh the page to show updated status
    },
    onError: (errors) => {
      console.error('Status toggle error:', errors)
      let errorMsg = 'Failed to toggle user status'
      if (typeof errors === 'string') {
        errorMsg = errors
      } else if (errors && typeof errors === 'object' && 'message' in errors) {
        errorMsg = (errors as any).message
      } else if (errors && typeof errors === 'object' && 'error' in errors) {
        errorMsg = (errors as any).error
      }
      showToast(errorMsg, 'error')
    }
  })
}

// Row click handler to navigate to Show.vue
const handleRowClick = (user: User) => {
  router.get(`/user-management/${user.id}`)
}

// Action handlers for card view
const handleView = (user: User) => {
  router.get(`/user-management/${user.id}`)
}

const handleEdit = (user: User) => {
  router.get(`/user-management/${user.id}/edit`)
}

const handleDelete = (user: User) => {
  openDeleteDialog(user)
}

// Status toggle handler for card view
const handleStatusToggleCard = (user: User) => {
  handleStatusToggle(user)
}

// Utility functions
const formatDate = (dateString: string | null) => {
  if (!dateString) return 'Never'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const isEmailVerified = (emailVerifiedAt: string | null) => {
  return !!emailVerifiedAt
}

// Avatar utility functions
const getInitials = (name: string): string => {
  if (!name) return '?'
  return name
    .split(' ')
    .map(part => part.charAt(0).toUpperCase())
    .slice(0, 2)
    .join('')
}

const getAvatarUrl = (avatar: string | null): string | null => {
  if (!avatar) return null
  // If avatar is already a full URL, return it
  if (avatar.startsWith('http')) return avatar
  // If avatar starts with /images/, return as is (for predefined avatars)
  if (avatar.startsWith('/images/')) return avatar
  // Otherwise, assume it's a relative path from storage
  return `/storage/${avatar}`
}

// Event handler for input events with proper typing
const handleSearchInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target) {
    handleSearch(target.value);
  }
}

// Helper functions for dropdown filters
const getRoleDisplayText = () => {
  if (!roleFilter.value) return 'All Roles'
  return props.roleOptions[roleFilter.value] || 'Select Role'
}

const getOfficeDisplayText = () => {
  if (!officeFilter.value) return 'All Offices'
  return props.officeOptions[officeFilter.value] || 'Select Office'
}

const getStatusDisplayText = () => {
  if (!statusFilter.value) return 'All Status'
  return props.statusOptions[statusFilter.value] || 'Select Status'
}

const isRoleSelected = (value: string) => {
  return roleFilter.value === value
}

const isOfficeSelected = (value: string) => {
  return officeFilter.value === value
}

const isStatusSelected = (value: string) => {
  return statusFilter.value === value
}

// Get badge variant based on role - UPDATED with user role
const getRoleBadgeVariant = (role: string) => {
  switch (role) {
    case 'superadmin': return 'destructive'
    case 'admin': return 'default'
    case 'staff': return 'secondary'
    case 'user': return 'outline' // Added user role
    default: return 'outline'
  }
}

// Get badge variant based on status
const getStatusBadgeVariant = (isActive: boolean) => {
  return isActive ? 'default' : 'secondary'
}

// Get badge variant for email verification
const getVerificationBadgeVariant = (isVerified: boolean) => {
  return isVerified ? 'default' : 'outline'
}

// Columns definition with proper alignment - REMOVED Last Login and Created columns
const columns: ColumnDef<User>[] = [
  {
    accessorKey: "name",
    header: () => h("div", { class: "text-left font-semibold" }, "User Details"),
    cell: ({ row }) => {
      const user = row.original;
      const avatarUrl = getAvatarUrl(user.avatar);
      const initials = getInitials(user.name);
      
      return h("div", { class: "flex items-start space-x-3" }, [
        // Avatar with image or initials
        h("div", { class: "flex-shrink-0 relative" }, [
          avatarUrl 
            ? h("img", {
                src: avatarUrl,
                alt: user.name,
                class: "w-10 h-10 rounded-full object-cover border-2 border-border shadow-sm",
                onError: (e: any) => {
                  // If image fails to load, fall back to initials
                  e.target.style.display = 'none';
                  const parent = e.target.parentElement;
                  const fallback = document.createElement('div');
                  fallback.className = 'w-10 h-10 rounded-full bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center text-white font-semibold text-sm border-2 border-border shadow-sm';
                  fallback.textContent = initials;
                  parent.appendChild(fallback);
                }
              })
            : h("div", { 
                class: "w-10 h-10 rounded-full bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center text-white font-semibold text-sm border-2 border-border shadow-sm"
              }, [
                h("span", { class: "text-sm font-medium" }, initials)
              ])
        ]),
        h("div", { class: "min-w-0 flex-1" }, [
          h("div", { class: "flex items-center space-x-2" }, [
            h("span", { class: "font-medium text-sm text-foreground" }, user.name || 'No name'),
          ]),
          h("div", { class: "flex items-center space-x-1 text-xs text-muted-foreground mt-1" }, [
            h(Mail, { class: "h-3 w-3 flex-shrink-0" }),
            h("span", { class: "truncate" }, user.email)
          ])
        ])
      ])
    },
  },
  {
    accessorKey: "role",
    header: () => h("div", { class: "text-center font-semibold" }, "Role"),
    cell: ({ row }) => h("div", { class: "flex justify-center" }, [
      h(Badge, { 
        variant: getRoleBadgeVariant(row.original.role),
        class: "text-xs"
      }, props.roleOptions[row.original.role] || row.original.role)
    ]),
  },
  {
    accessorKey: "office",
    header: () => h("div", { class: "text-left font-semibold" }, "Office"),
    cell: ({ row }) => h("div", { class: "flex items-center space-x-1" }, [
      h(Building, { class: "h-3 w-3 text-muted-foreground flex-shrink-0" }),
      h("span", { class: "text-sm truncate" }, row.original.office || 'Not assigned')
    ]),
  },
  {
    accessorKey: "is_active",
    header: () => h("div", { class: "text-center font-semibold" }, "Status"),
    cell: ({ row }) => {
      const user = row.original
      return h("div", { class: "flex justify-center" }, [
        h(Badge, { 
          variant: getStatusBadgeVariant(user.is_active),
          class: "text-xs"
        }, user.is_active ? "Active" : "Inactive")
      ])
    },
  },
  {
    accessorKey: "email_verified_at",
    header: () => h("div", { class: "text-center font-semibold" }, "Email Verified"),
    cell: ({ row }) => {
      const user = row.original
      const isVerified = isEmailVerified(user.email_verified_at)
      return h("div", { class: "flex items-center justify-center space-x-2" }, [
        h(isVerified ? CheckCircle : XCircle, { 
          class: `h-4 w-4 ${isVerified ? 'text-green-600' : 'text-muted-foreground'}`
        }),
        h(Badge, { 
          variant: getVerificationBadgeVariant(isVerified),
          class: "text-xs"
        }, isVerified ? "Verified" : "Unverified")
      ])
    },
  },
  {
    id: "actions",
    header: () => h("div", { class: "text-center font-semibold" }, "Actions"),
    cell: ({ row }) => {
      const user = row.original
      const isCurrentUser = user.id === (page.props.auth.user as any).id

      const handleView = (e: Event) => {
        e.stopPropagation()
        router.get(`/user-management/${user.id}`)
      }

      const handleEdit = (e: Event) => {
        e.stopPropagation()
        router.get(`/user-management/${user.id}/edit`)
      }

      const handleStatus = (e: Event) => {
        e.stopPropagation()
        handleStatusToggle(user)
      }

      const handleDelete = (e: Event) => {
        e.stopPropagation()
        openDeleteDialog(user)
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
            h(TooltipContent, {}, "View User")
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
            h(TooltipContent, {}, "Edit User")
          ]),
          h(Tooltip, {}, [
            h(TooltipTrigger, { asChild: true }, [
              h(Button, {
                variant: "ghost",
                size: "sm",
                onClick: handleStatus,
                class: `h-8 w-8 p-0 hover:bg-accent ${getStatusButtonClass(user.is_active)}`,
                disabled: isCurrentUser
              }, [
                h(user.is_active ? UserX : UserCheck, { class: "h-4 w-4" }),
              ])
            ]),
            h(TooltipContent, {}, 
              isCurrentUser 
                ? "Cannot modify your own status" 
                : user.is_active ? "Deactivate User" : "Activate User"
            )
          ]),
          h(Tooltip, {}, [
            h(TooltipTrigger, { asChild: true }, [
              h(Button, {
                variant: "ghost",
                size: "sm",
                onClick: handleDelete,
                class: "h-8 w-8 p-0 text-destructive hover:text-destructive/90 hover:bg-accent",
                disabled: isCurrentUser
              }, [
                h(Trash2, { class: "h-4 w-4" }),
              ])
            ]),
            h(TooltipContent, {}, 
              isCurrentUser 
                ? "Cannot delete your own account" 
                : "Delete User"
            )
          ]),
        ])
      ])
    },
    enableHiding: false,
  },
]

// Watchers to update reactive data when props change
watch(() => props.users, (newUsers) => {
  data.value = newUsers.data || []
  total.value = newUsers.total || 0
  currentPage.value = newUsers.current_page || 1
  pageSize.value = newUsers.per_page || 10
}, { deep: true })

watch(() => props.filters, (newFilters) => {
  searchQuery.value = newFilters?.search || ''
  roleFilter.value = newFilters?.role || ''
  officeFilter.value = newFilters?.office || ''
  statusFilter.value = newFilters?.status || ''
}, { deep: true })

// Clear shown messages when component unmounts
onMounted(() => {
  shownFlashMessages.value.clear()
})
</script>

<template>
  <Head title="User Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="text-center sm:text-left">
          <h1 class="text-3xl font-bold text-foreground">User Management</h1>
          <p class="text-muted-foreground mt-2">Manage system users, roles, and permissions</p>
        </div>
        
        <Link href="/user-management/create" as="button" class="w-full sm:w-auto">
          <Button class="w-full sm:w-auto">
            <Plus class="h-4 w-4 mr-2" />
            Add New User
          </Button>
        </Link>
      </div>

      <!-- Filters Section -->
      <div class="bg-card rounded-lg border p-4 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-foreground mb-2">Search</label>
            <div class="relative">
              <Input
                v-model="searchQuery"
                type="text"
                placeholder="Search users, emails..."
                class="w-full pr-10"
                @input="handleSearchInput"
                :disabled="loading"
              />
              <div v-if="loading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary"></div>
              </div>
            </div>
          </div>

          <!-- Role Filter Dropdown -->
          <div>
            <label class="block text-sm font-medium text-foreground mb-2">Role</label>
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="outline" class="w-full justify-between" :disabled="loading">
                  <span class="truncate">
                    {{ getRoleDisplayText() }}
                  </span>
                  <ChevronDown class="h-4 w-4 opacity-50 ml-2 flex-shrink-0" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-56">
                <DropdownMenuLabel>Filter by Role</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                  :model-value="!roleFilter"
                  @update:model-value="() => handleRoleFilter('')"
                  :disabled="loading"
                >
                  All Roles
                </DropdownMenuCheckboxItem>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                  v-for="(label, value) in props.roleOptions"
                  :key="value"
                  :model-value="isRoleSelected(value)"
                  @update:model-value="() => handleRoleFilter(value)"
                  :disabled="loading"
                >
                  {{ label }}
                </DropdownMenuCheckboxItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>

          <!-- Office Filter Dropdown -->
          <div>
            <label class="block text-sm font-medium text-foreground mb-2">Office</label>
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="outline" class="w-full justify-between" :disabled="loading">
                  <span class="truncate">
                    {{ getOfficeDisplayText() }}
                  </span>
                  <ChevronDown class="h-4 w-4 opacity-50 ml-2 flex-shrink-0" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-56">
                <DropdownMenuLabel>Filter by Office</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                  :model-value="!officeFilter"
                  @update:model-value="() => handleOfficeFilter('')"
                  :disabled="loading"
                >
                  All Offices
                </DropdownMenuCheckboxItem>
                <DropdownMenuSeparator />
                <DropdownMenuCheckboxItem
                  v-for="(label, value) in props.officeOptions"
                  :key="value"
                  :model-value="isOfficeSelected(value)"
                  @update:model-value="() => handleOfficeFilter(value)"
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
                  v-for="(label, value) in props.statusOptions"
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
      </div>

      <!-- DataTable with Card View Support -->
      <DataTable
        :data="data"
        :columns="columns"
        :loading="loading"
        :search-query="searchQuery"
        :enable-row-selection="false"
        :enable-column-visibility="false"
        :enable-pagination="true"
        :current-page="currentPage"
        :total="total"
        :page-size="pageSize"
        :card-view="cardViewConfig"
        @page-change="handlePageChange"
        @page-size-change="handlePageSizeChange"
        @row-click="handleRowClick"
        @view="handleView"
        @edit="handleEdit"
        @delete="handleDelete"
        @status-toggle="handleStatusToggleCard"
      >
        <!-- Custom card header slot -->
        <template #card-header="{ row }">
          <div class="flex items-center space-x-3 flex-1 min-w-0">
            <!-- Avatar -->
            <div class="flex-shrink-0">
              <div v-if="getAvatarUrl(row.avatar)" class="w-10 h-10 rounded-full bg-cover bg-center border-2 border-border shadow-sm" 
                   :style="{ backgroundImage: `url(${getAvatarUrl(row.avatar)})` }">
              </div>
              <div v-else class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center text-white font-semibold text-sm border-2 border-border shadow-sm">
                <span class="text-sm font-medium">{{ getInitials(row.name) }}</span>
              </div>
            </div>
            <!-- User info -->
            <div class="flex-1 min-w-0">
              <h4 class="font-semibold text-foreground truncate">{{ row.name || 'No name' }}</h4>
              <p class="text-sm text-muted-foreground truncate">{{ row.email }}</p>
            </div>
          </div>
        </template>

        <!-- Custom card badge slot -->
        <template #card-badge="{ row }">
          <Badge :variant="getStatusBadgeVariant(row.is_active)" class="text-xs whitespace-nowrap">
            {{ row.is_active ? "Active" : "Inactive" }}
          </Badge>
        </template>
      </DataTable>
    </div>

    <!-- Delete Confirmation Dialog -->
    <AlertDialog v-model:open="deleteDialogOpen">
      <AlertDialogContent class="max-w-[95vw] sm:max-w-md">
        <AlertDialogHeader>
          <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
          <AlertDialogDescription>
            This action cannot be undone. This will permanently delete the user
            "{{ userToDelete?.name }}" and remove their account from our system.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="flex flex-col sm:flex-row gap-2">
          <AlertDialogCancel :disabled="deleting" @click="deleteDialogOpen = false" class="w-full sm:w-auto">Cancel</AlertDialogCancel>
          <AlertDialogAction 
            @click="deleteUser"
            class="bg-destructive text-destructive-foreground hover:bg-destructive/90 w-full sm:w-auto"
            :disabled="deleting"
          >
            <div v-if="deleting" class="flex items-center justify-center space-x-2">
              <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-white"></div>
              <span>Deleting...</span>
            </div>
            <span v-else>Delete User</span>
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>