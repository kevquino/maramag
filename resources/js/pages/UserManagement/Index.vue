<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Eye, Edit, Trash2, User, Mail, Calendar, CheckCircle, XCircle, Search, ChevronDown, MoreVertical, Plus, Clock } from "lucide-vue-next"
import { ref, watch, nextTick } from "vue"
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
  DropdownMenuItem,
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
  is_active: boolean
  email_verified_at: string | null
  last_login_at: string | null
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
    status?: string
  }
  roleOptions: Record<string, string>
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
const statusFilter = ref(props.filters?.status || '')

// Delete dialog state
const deleteDialogOpen = ref(false)
const userToDelete = ref<User | null>(null)
const deleting = ref(false)

// Status toggle dialog state
const statusDialogOpen = ref(false)
const userToToggleStatus = ref<User | null>(null)
const togglingStatus = ref(false)

// Search timeout reference
let searchTimeout: number | null = null

// Function to reload the entire page with current filters
const reloadPage = () => {
  const params: any = {
    page: currentPage.value
  }

  if (searchQuery.value) params.search = searchQuery.value
  if (roleFilter.value) params.role = roleFilter.value
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

const handleStatusFilter = (value: string) => {
  statusFilter.value = value
  currentPage.value = 1
  reloadPage()
}

const clearFilters = () => {
  searchQuery.value = ''
  roleFilter.value = ''
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
const deleteUser = () => {
  if (!userToDelete.value) return

  deleting.value = true
  
  router.delete(`/user-management/${userToDelete.value.id}`, {
    preserveScroll: true,
    preserveState: false,
    onSuccess: () => {
      deleteDialogOpen.value = false
      userToDelete.value = null
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
      
      toast.error(errorMsg)
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
const toggleUserStatus = () => {
  if (!userToToggleStatus.value) return

  togglingStatus.value = true
  
  router.post(`/user-management/${userToToggleStatus.value.id}/toggle-status`, {}, {
    preserveScroll: true,
    preserveState: false,
    onSuccess: () => {
      statusDialogOpen.value = false
      userToToggleStatus.value = null
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
      toast.error(errorMsg)
    },
    onFinish: () => {
      togglingStatus.value = false
    }
  })
}

const openStatusDialog = (user: User) => {
  userToToggleStatus.value = user
  statusDialogOpen.value = true
}

// Utility functions for last login display
const formatLastLogin = (dateString: string | null) => {
  if (!dateString) return 'Never logged in'
  
  const date = new Date(dateString);
  const now = new Date();
  const diffTime = Math.abs(now.getTime() - date.getTime());
  const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
  const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
  const diffMinutes = Math.floor(diffTime / (1000 * 60));

  if (diffDays > 0) {
    return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
  } else if (diffHours > 0) {
    return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
  } else if (diffMinutes > 0) {
    return `${diffMinutes} minute${diffMinutes > 1 ? 's' : ''} ago`;
  } else {
    return 'Just now';
  }
}

const getDetailedLastLogin = (dateString: string | null) => {
  if (!dateString) return 'User has never logged in'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatDate = (dateString: string | null) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getRoleBadgeVariant = (role: string) => {
  switch (role) {
    case 'admin': return 'default'
    case 'PIO Officer': return 'secondary'
    case 'PIO Staff': return 'outline'
    case 'user': return 'secondary'
    default: return 'outline'
  }
}

const getStatusBadgeVariant = (isActive: boolean) => {
  return isActive ? 'default' : 'destructive'
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

const getStatusDisplayText = () => {
  if (!statusFilter.value) return 'All Status'
  return props.statusOptions[statusFilter.value] || 'Select Status'
}

const isRoleSelected = (value: string) => {
  return roleFilter.value === value
}

const isStatusSelected = (value: string) => {
  return statusFilter.value === value
}

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
  statusFilter.value = newFilters?.status || ''
}, { deep: true })
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-foreground mb-2">Search</label>
            <div class="relative">
              <Input
                v-model="searchQuery"
                type="text"
                placeholder="Search users by name or email..."
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
                  v-for="(label, value) in roleOptions"
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
        <div v-if="roleFilter || statusFilter" class="mt-4 flex flex-wrap gap-2">
          <div 
            v-if="roleFilter" 
            class="inline-flex items-center gap-1 bg-primary/10 text-primary px-2 py-1 rounded-md text-sm"
          >
            Role: {{ roleOptions[roleFilter] }}
            <button 
              @click="handleRoleFilter('')"
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
            Showing {{ total }} user(s)
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

      <!-- Table -->
      <div class="bg-card rounded-lg border shadow-sm">
        <div class="relative w-full overflow-auto">
          <table class="w-full caption-bottom text-sm">
            <thead class="[&_tr]:border-b">
              <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground w-12">
                  <Checkbox />
                </th>
                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">User Details</th>
                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Role</th>
                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Status</th>
                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Last Login</th>
                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Created</th>
                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Actions</th>
              </tr>
            </thead>
            <tbody class="[&_tr:last-child]:border-0">
              <tr v-if="loading" class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                <td colspan="7" class="p-4 align-middle text-center">
                  <div class="flex items-center justify-center space-x-2">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary"></div>
                    <span>Loading users...</span>
                  </div>
                </td>
              </tr>
              <tr 
                v-else-if="data.length === 0" 
                class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted"
              >
                <td colspan="7" class="p-4 align-middle text-center text-muted-foreground">
                  No users found.
                </td>
              </tr>
              <tr
                v-else
                v-for="user in data"
                :key="user.id"
                class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted"
              >
                <td class="p-4 align-middle">
                  <Checkbox />
                </td>
                <td class="p-4 align-middle">
                  <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-muted flex items-center justify-center">
                      <User class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <div class="min-w-0 flex-1">
                      <div class="flex items-center space-x-2">
                        <span class="font-medium text-sm text-foreground truncate">{{ user.name }}</span>
                        <CheckCircle v-if="user.email_verified_at" class="h-3 w-3 text-green-500 flex-shrink-0" />
                      </div>
                      <div class="flex items-center space-x-1 text-xs text-muted-foreground mt-1 truncate">
                        <Mail class="h-3 w-3 flex-shrink-0" />
                        <span class="truncate">{{ user.email }}</span>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="p-4 align-middle">
                  <Badge :variant="getRoleBadgeVariant(user.role)" class="text-xs">
                    {{ roleOptions[user.role] || user.role }}
                  </Badge>
                </td>
                <td class="p-4 align-middle">
                  <div class="flex flex-col space-y-1">
                    <Badge :variant="getStatusBadgeVariant(user.is_active)" class="text-xs">
                      {{ user.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                    <Badge v-if="!user.email_verified_at" variant="outline" class="text-xs">
                      Unverified
                    </Badge>
                  </div>
                </td>
                <td class="p-4 align-middle">
                  <TooltipProvider>
                    <Tooltip>
                      <TooltipTrigger as-child>
                        <div class="flex items-center space-x-1 cursor-help">
                          <Clock class="h-3 w-3 text-muted-foreground" />
                          <span class="text-sm" :class="{ 'text-muted-foreground italic': !user.last_login_at }">
                            {{ formatLastLogin(user.last_login_at) }}
                          </span>
                        </div>
                      </TooltipTrigger>
                      <TooltipContent>
                        <p>{{ getDetailedLastLogin(user.last_login_at) }}</p>
                      </TooltipContent>
                    </Tooltip>
                  </TooltipProvider>
                </td>
                <td class="p-4 align-middle">
                  <div class="flex items-center space-x-1">
                    <Calendar class="h-3 w-3 text-muted-foreground" />
                    <span class="text-sm">{{ formatDate(user.created_at) }}</span>
                  </div>
                </td>
                <td class="p-4 align-middle">
                  <div class="flex space-x-1 justify-center">
                    <TooltipProvider>
                      <Tooltip>
                        <TooltipTrigger as-child>
                          <Button variant="ghost" size="sm" @click="router.get(`/user-management/${user.id}`)" class="h-8 w-8 p-0 hover:bg-accent">
                            <Eye class="h-4 w-4" />
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>View User</TooltipContent>
                      </Tooltip>
                      
                      <Tooltip>
                        <TooltipTrigger as-child>
                          <Button variant="ghost" size="sm" @click="router.get(`/user-management/${user.id}/edit`)" class="h-8 w-8 p-0 hover:bg-accent">
                            <Edit class="h-4 w-4" />
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent>Edit User</TooltipContent>
                      </Tooltip>

                      <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                          <Button variant="ghost" size="sm" class="h-8 w-8 p-0 hover:bg-accent">
                            <MoreVertical class="h-4 w-4" />
                          </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                          <DropdownMenuItem @click="openStatusDialog(user)" :class="user.is_active ? 'text-destructive' : 'text-green-600'">
                            <component :is="user.is_active ? XCircle : CheckCircle" class="h-4 w-4 mr-2" />
                            {{ user.is_active ? 'Deactivate User' : 'Activate User' }}
                          </DropdownMenuItem>
                          <DropdownMenuSeparator />
                          <DropdownMenuItem @click="openDeleteDialog(user)" class="text-destructive">
                            <Trash2 class="h-4 w-4 mr-2" />
                            Delete User
                          </DropdownMenuItem>
                        </DropdownMenuContent>
                      </DropdownMenu>
                    </TooltipProvider>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div class="flex items-center justify-between px-2">
        <div class="text-sm text-muted-foreground">
          Page {{ currentPage }} of {{ Math.ceil(total / pageSize) }}
        </div>
        <div class="flex items-center space-x-2">
          <Button
            variant="outline"
            size="sm"
            @click="handlePreviousPage"
            :disabled="currentPage <= 1 || loading"
          >
            Previous
          </Button>
          <Button
            variant="outline"
            size="sm"
            @click="handleNextPage"
            :disabled="currentPage >= Math.ceil(total / pageSize) || loading"
          >
            Next
          </Button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <AlertDialog v-model:open="deleteDialogOpen">
      <AlertDialogContent class="max-w-[95vw] sm:max-w-md">
        <AlertDialogHeader>
          <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
          <AlertDialogDescription>
            This action cannot be undone. This will permanently delete the user
            "{{ userToDelete?.name }}" and remove all their data from our servers.
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

    <!-- Status Toggle Confirmation Dialog -->
    <AlertDialog v-model:open="statusDialogOpen">
      <AlertDialogContent class="max-w-[95vw] sm:max-w-md">
        <AlertDialogHeader>
          <AlertDialogTitle>
            {{ userToToggleStatus?.is_active ? 'Deactivate User' : 'Activate User' }}
          </AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to {{ userToToggleStatus?.is_active ? 'deactivate' : 'activate' }} 
            the user "{{ userToToggleStatus?.name }}"?
            {{ userToToggleStatus?.is_active ? 'Deactivated users cannot log in to the system.' : 'Activated users will be able to log in again.' }}
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="flex flex-col sm:flex-row gap-2">
          <AlertDialogCancel :disabled="togglingStatus" @click="statusDialogOpen = false" class="w-full sm:w-auto">Cancel</AlertDialogCancel>
          <AlertDialogAction 
            @click="toggleUserStatus"
            :class="userToToggleStatus?.is_active ? 'bg-destructive text-destructive-foreground hover:bg-destructive/90' : 'bg-green-600 text-white hover:bg-green-700'"
            class="w-full sm:w-auto"
            :disabled="togglingStatus"
          >
            <div v-if="togglingStatus" class="flex items-center justify-center space-x-2">
              <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-white"></div>
              <span>Processing...</span>
            </div>
            <span v-else>{{ userToToggleStatus?.is_active ? 'Deactivate User' : 'Activate User' }}</span>
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>