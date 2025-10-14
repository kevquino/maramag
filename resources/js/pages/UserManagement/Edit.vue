<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Save, User, Mail, Shield, Building, Eye, Trash2, CheckSquare, Square, LogOut } from 'lucide-vue-next';
import { ref, computed, watch, nextTick, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Switch } from '@/components/ui/switch';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { toast } from 'vue-sonner';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';

// Props
const props = defineProps<{
  user: {
    id: string;
    name: string;
    email: string;
    role: string;
    office: string;
    is_active: boolean;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    permissions: string[];
  };
  roleOptions: Record<string, string>;
  officeOptions: Record<string, string>;
  permissionOptions: Record<string, any>;
  permissionGroups: Record<string, any>;
  canEditPermissions?: boolean;
}>();

const page = usePage();

// Define flash message interface
interface FlashMessages {
  success?: string;
  error?: string;
  warning?: string;
  info?: string;
}

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

// Breadcrumbs
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
  {
    title: 'User Management',
    href: '/user-management',
  },
  {
    title: `Edit ${props.user.name}`,
    href: `/user-management/${props.user.id}/edit`,
  },
]);

// Form handling - Use POST method with _method field
const form = useForm({
  _method: 'PUT',
  name: props.user.name,
  email: props.user.email,
  role: props.user.role,
  office: props.user.office,
  is_active: props.user.is_active,
  password: '',
  password_confirmation: '',
  permissions: Array.isArray(props.user.permissions) ? [...props.user.permissions] : [],
});

// Dialog states
const saveDialogOpen = ref(false);
const cancelDialogOpen = ref(false);
const deleteDialogOpen = ref(false);
const impersonateDialogOpen = ref(false);
const deleting = ref(false);
const impersonating = ref(false);

// Permission selection
const selectAllPermissions = ref(false);

// Get all available permissions
const allPermissions = computed(() => {
  return Object.keys(props.permissionOptions);
});

// Get ungrouped permissions
const getUngroupedPermissions = () => {
  const groupedPermissions = Object.values(props.permissionGroups).flatMap((group: any) => group.permissions || []);
  return allPermissions.value.filter(permission => !groupedPermissions.includes(permission));
};

// Toggle all permissions - UPDATED: Same as Create.vue
const toggleAllPermissions = (checked: boolean) => {
  if (!canEditPermissions.value) return;
  
  selectAllPermissions.value = checked;
  
  if (checked) {
    // Add all permissions
    form.permissions = [...allPermissions.value];
  } else {
    // Remove all permissions
    form.permissions = [];
  }
};

// Toggle single permission using switch - UPDATED: Same as Create.vue
const togglePermission = (permission: string, checked: boolean) => {
  if (!canEditPermissions.value) return;
  
  if (checked) {
    // Add permission if not already present
    if (!form.permissions.includes(permission)) {
      form.permissions.push(permission);
    }
  } else {
    // Remove permission
    const index = form.permissions.indexOf(permission);
    if (index > -1) {
      form.permissions.splice(index, 1);
    }
  }
  
  // Update select all state based on current permissions
  updateSelectAllState();
};

// Check if permission is enabled
const isPermissionEnabled = (permission: string) => {
  return form.permissions.includes(permission);
};

// Update select all state based on current permissions - UPDATED: Same as Create.vue
const updateSelectAllState = () => {
  selectAllPermissions.value = form.permissions.length === allPermissions.value.length && allPermissions.value.length > 0;
};

// Watch permissions to update select all - UPDATED: Same as Create.vue
watch(() => form.permissions, () => {
  updateSelectAllState();
}, { deep: true });

// Check if form has unsaved changes
const hasUnsavedChanges = computed(() => {
  return form.name !== props.user.name ||
         form.email !== props.user.email ||
         form.role !== props.user.role ||
         form.office !== props.user.office ||
         form.is_active !== props.user.is_active ||
         form.password !== '' ||
         form.password_confirmation !== '' ||
         JSON.stringify([...form.permissions].sort()) !== JSON.stringify([...(props.user.permissions || [])].sort());
});

// Get user summary for confirmation dialogs
const userSummary = computed(() => {
  return {
    name: form.name || 'Unnamed User',
    email: form.email || 'No email',
    role: props.roleOptions[form.role] || form.role,
    office: props.officeOptions[form.office] || form.office,
    status: form.is_active ? 'Active' : 'Inactive',
    hasPassword: !!form.password,
    permissions: form.permissions.length,
  };
});

// Check if current user is editing their own profile
const isEditingSelf = computed(() => {
  return props.user.id === (page.props.auth.user as any).id;
});

// Check if current user is admin
const isAdmin = computed(() => {
  return (page.props.auth.user as any).role === 'admin';
});

// Check if can edit permissions
const canEditPermissions = computed(() => {
  return isAdmin.value && !isEditingSelf.value;
});

// Check if can edit role and office
const canEditRoleOffice = computed(() => {
  return isAdmin.value && !isEditingSelf.value;
});

// Check if can edit status
const canEditStatus = computed(() => {
  return isAdmin.value && !isEditingSelf.value;
});

// Check if can delete user
const canDeleteUser = computed(() => {
  return isAdmin.value && !isEditingSelf.value;
});

// Check if can impersonate user
const canImpersonateUser = computed(() => {
  return isAdmin.value && !isEditingSelf.value;
});

// Handle form submission - Use POST with _method=PUT
const submit = () => {
  form.post(`/user-management/${props.user.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      // Don't show toast here - let the server flash message handle it
    },
    onError: (errors) => {
      // Don't show generic error toast here - let form.errors handle specific errors
    },
  });
};

// Confirm save
const confirmSave = () => {
  saveDialogOpen.value = false;
  submit();
};

// Open save confirmation
const openSaveDialog = () => {
  if (validateForm()) {
    saveDialogOpen.value = true;
  }
};

// Validate form before submission
const validateForm = (): boolean => {
  if (!form.name.trim()) {
    showToast('Please enter a name for the user.', 'error');
    return false;
  }
  if (!form.email.trim()) {
    showToast('Please enter an email address.', 'error');
    return false;
  }
  // Only validate role and office if admin is editing another user
  if (isAdmin.value && !isEditingSelf.value) {
    if (!form.role) {
      showToast('Please select a role.', 'error');
      return false;
    }
    if (!form.office) {
      showToast('Please select an office.', 'error');
      return false;
    }
  }
  if (form.password && form.password !== form.password_confirmation) {
    showToast('Password confirmation does not match.', 'error');
    return false;
  }
  if (form.password && form.password.length < 8) {
    showToast('Password must be at least 8 characters long.', 'error');
    return false;
  }
  return true;
};

// Open cancel confirmation
const openCancelDialog = () => {
  if (hasUnsavedChanges.value) {
    cancelDialogOpen.value = true;
  } else {
    cancel();
  }
};

// Confirm cancel
const confirmCancel = () => {
  cancelDialogOpen.value = false;
  cancel();
};

// Cancel and go back
const cancel = () => {
  if (isEditingSelf.value) {
    router.visit('/dashboard');
  } else {
    router.visit('/user-management');
  }
};

// Delete user using Inertia
const deleteUser = async () => {
  deleting.value = true;
  try {
    router.delete(`/user-management/${props.user.id}`, {
      preserveScroll: false,
      onSuccess: () => {
        // Don't show toast here - let the server flash message handle it
      },
      onError: (errors) => {
        const errorMsg = errors.message || 'Failed to delete user';
        showToast(errorMsg, 'error');
        deleting.value = false;
        deleteDialogOpen.value = false;
      },
    });
  } catch (err) {
    console.error('Failed to delete user:', err);
    const errorMsg = err instanceof Error ? err.message : 'Failed to delete user';
    showToast(errorMsg, 'error');
    deleting.value = false;
    deleteDialogOpen.value = false;
  }
};

// Impersonate user
const impersonateUser = async () => {
  impersonating.value = true;
  try {
    router.post(`/user-management/${props.user.id}/impersonate`, {}, {
      preserveScroll: false,
      onSuccess: () => {
        // Success handled by server redirect
      },
      onError: (errors) => {
        const errorMsg = errors.message || 'Failed to impersonate user';
        showToast(errorMsg, 'error');
        impersonating.value = false;
        impersonateDialogOpen.value = false;
      },
    });
  } catch (err) {
    console.error('Failed to impersonate user:', err);
    const errorMsg = err instanceof Error ? err.message : 'Failed to impersonate user';
    showToast(errorMsg, 'error');
    impersonating.value = false;
    impersonateDialogOpen.value = false;
  }
};

// Open delete confirmation dialog
const openDeleteDialog = () => {
  deleteDialogOpen.value = true;
};

// Open impersonate confirmation dialog
const openImpersonateDialog = () => {
  impersonateDialogOpen.value = true;
};

// Format date for display
const formatDate = (dateString: string | null) => {
  if (!dateString) return 'Never';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Initialize permissions
const initializePermissions = () => {
  shownFlashMessages.value.clear();
  
  // Ensure permissions is always an array
  if (!Array.isArray(form.permissions)) {
    form.permissions = [];
  }
  
  // Initialize select all state
  updateSelectAllState();
};

// Clear shown messages when component unmounts
onMounted(() => {
  initializePermissions();
});
</script>

<template>
  <Head :title="`Edit ${user.name}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full p-4 sm:p-6">
      <div class="w-full max-w-none mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
          <div class="flex-1 min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-foreground truncate">Edit User</h1>
            <p class="text-muted-foreground mt-1">Update user details and permissions</p>
            <div v-if="isEditingSelf" class="mt-2">
              <Badge variant="outline" class="bg-blue-50 text-blue-700 border-blue-200">
                Editing your own profile
              </Badge>
            </div>
            <div v-else-if="isAdmin" class="mt-2">
              <Badge variant="outline" class="bg-green-50 text-green-700 border-green-200">
                Admin Editing User
              </Badge>
            </div>
          </div>
        </div>

        <!-- Error summary -->
        <div v-if="Object.keys(form.errors).length" class="mb-6 p-4 bg-destructive/15 border border-destructive/50 text-destructive rounded-lg">
          <h3 class="font-semibold mb-2">Please fix the following errors:</h3>
          <ul class="list-disc list-inside space-y-1">
            <li v-for="(error, field) in form.errors" :key="field">
              {{ error }}
            </li>
          </ul>
        </div>

        <!-- Main Form Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
          <!-- Left Column - Main Content -->
          <div class="xl:col-span-2 space-y-6">
            <!-- Basic Information Card -->
            <div class="bg-card rounded-lg border shadow-sm">
              <div class="p-4 sm:p-6 border-b">
                <h2 class="text-lg font-semibold">Basic Information</h2>
              </div>
              <div class="p-4 sm:p-6 space-y-6">
                <!-- Name -->
                <div class="space-y-2">
                  <Label for="name" class="text-sm font-medium">Full Name *</Label>
                  <div class="relative">
                    <User class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input
                      id="name"
                      v-model="form.name"
                      type="text"
                      placeholder="Enter full name"
                      :class="form.errors.name ? 'border-destructive pl-10' : 'pl-10'"
                      class="w-full"
                    />
                  </div>
                  <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                </div>

                <!-- Email -->
                <div class="space-y-2">
                  <Label for="email" class="text-sm font-medium">Email Address *</Label>
                  <div class="relative">
                    <Mail class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input
                      id="email"
                      v-model="form.email"
                      type="email"
                      placeholder="Enter email address"
                      :class="form.errors.email ? 'border-destructive pl-10' : 'pl-10'"
                      class="w-full"
                    />
                  </div>
                  <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                  <div v-if="user.email_verified_at" class="flex items-center space-x-1 text-sm text-green-600">
                    <span>✓ Email verified</span>
                    <span class="text-muted-foreground">• {{ formatDate(user.email_verified_at) }}</span>
                  </div>
                  <div v-else class="flex items-center space-x-1 text-sm text-amber-600">
                    <span>⚠ Email not verified</span>
                  </div>
                </div>

                <!-- Password -->
                <div class="space-y-4">
                  <Label class="text-sm font-medium">Change Password</Label>
                  <p class="text-sm text-muted-foreground">
                    Leave blank to keep current password
                  </p>
                  
                  <div class="space-y-2">
                    <Label for="password" class="text-sm font-medium">New Password</Label>
                    <Input
                      id="password"
                      v-model="form.password"
                      type="password"
                      placeholder="Enter new password"
                      :class="form.errors.password ? 'border-destructive' : ''"
                      class="w-full"
                    />
                    <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
                  </div>

                  <div class="space-y-2">
                    <Label for="password_confirmation" class="text-sm font-medium">Confirm New Password</Label>
                    <Input
                      id="password_confirmation"
                      v-model="form.password_confirmation"
                      type="password"
                      placeholder="Confirm new password"
                      :class="form.errors.password_confirmation ? 'border-destructive' : ''"
                      class="w-full"
                    />
                    <p v-if="form.errors.password_confirmation" class="text-sm text-destructive">{{ form.errors.password_confirmation }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Permissions Card -->
            <Card v-if="isAdmin">
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <CheckSquare class="h-5 w-5 text-blue-600" />
                  Module Permissions
                </CardTitle>
                <CardDescription>
                  Select which modules and pages this user can access
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-6">
                <!-- Select All Toggle -->
                <div class="flex items-center justify-between p-4 border rounded-lg bg-muted/30">
                  <div class="space-y-0.5">
                    <Label class="text-base font-medium">Select All Permissions</Label>
                    <p class="text-sm text-muted-foreground">
                      Enable or disable all permissions at once
                    </p>
                  </div>
                  <Switch
                    :model-value="selectAllPermissions"
                    @update:model-value="toggleAllPermissions"
                    :disabled="!canEditPermissions"
                  />
                </div>

                <!-- Permissions List - Single Box -->
                <div class="border rounded-lg">
                  <div class="p-4 bg-muted/30 border-b">
                    <h3 class="font-semibold text-sm">All Permissions</h3>
                    <p class="text-xs text-muted-foreground mt-1">
                      {{ allPermissions.length }} total permissions • {{ form.permissions.length }} enabled
                    </p>
                  </div>
                  
                  <div class="p-4 space-y-4">
                    <!-- Group permissions by category for better organization -->
                    <div v-for="(group, groupKey) in permissionGroups" :key="groupKey" class="space-y-3">
                      <div class="flex items-center space-x-2">
                        <div class="h-px flex-1 bg-border"></div>
                        <span class="text-xs font-medium text-muted-foreground px-2 bg-background">{{ group.label }}</span>
                        <div class="h-px flex-1 bg-border"></div>
                      </div>
                      
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div
                          v-for="permissionKey in group.permissions || []"
                          :key="permissionKey"
                          class="flex flex-row items-center justify-between rounded-lg border p-4 transition-colors"
                          :class="canEditPermissions ? 'hover:bg-muted/30 cursor-pointer' : ''"
                          @click="canEditPermissions && togglePermission(permissionKey, !isPermissionEnabled(permissionKey))"
                        >
                          <div class="space-y-0.5 flex-1">
                            <Label class="text-base font-medium cursor-pointer">
                              {{ permissionOptions[permissionKey]?.label || permissionKey }}
                            </Label>
                            <p class="text-sm text-muted-foreground">
                              {{ permissionOptions[permissionKey]?.description || 'No description available' }}
                            </p>
                          </div>
                          <Switch
                            :model-value="isPermissionEnabled(permissionKey)"
                            @update:model-value="(checked: boolean) => togglePermission(permissionKey, checked)"
                            :disabled="!canEditPermissions"
                            @click.stop
                          />
                        </div>
                      </div>
                    </div>

                    <!-- Handle any permissions not in groups -->
                    <div v-if="getUngroupedPermissions().length > 0" class="space-y-3">
                      <div class="flex items-center space-x-2">
                        <div class="h-px flex-1 bg-border"></div>
                        <span class="text-xs font-medium text-muted-foreground px-2 bg-background">Other Permissions</span>
                        <div class="h-px flex-1 bg-border"></div>
                      </div>
                      
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div
                          v-for="permissionKey in getUngroupedPermissions()"
                          :key="permissionKey"
                          class="flex flex-row items-center justify-between rounded-lg border p-4 transition-colors"
                          :class="canEditPermissions ? 'hover:bg-muted/30 cursor-pointer' : ''"
                          @click="canEditPermissions && togglePermission(permissionKey, !isPermissionEnabled(permissionKey))"
                        >
                          <div class="space-y-0.5 flex-1">
                            <Label class="text-base font-medium cursor-pointer">
                              {{ permissionOptions[permissionKey]?.label || permissionKey }}
                            </Label>
                            <p class="text-sm text-muted-foreground">
                              {{ permissionOptions[permissionKey]?.description || 'No description available' }}
                            </p>
                          </div>
                          <Switch
                            :model-value="isPermissionEnabled(permissionKey)"
                            @update:model-value="(checked: boolean) => togglePermission(permissionKey, checked)"
                            :disabled="!canEditPermissions"
                            @click.stop
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-if="form.errors.permissions" class="text-sm text-destructive">
                  {{ form.errors.permissions }}
                </div>

                <div v-if="!canEditPermissions" class="p-3 bg-amber-50 border border-amber-200 rounded-lg">
                  <p class="text-sm text-amber-800">
                    <span v-if="isEditingSelf">You cannot edit your own permissions.</span>
                    <span v-else>You don't have permission to edit permissions. Only administrators can modify user permissions.</span>
                  </p>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Right Column - Sidebar -->
          <div class="space-y-6">
            <!-- Settings Card -->
            <div class="bg-card rounded-lg border shadow-sm">
              <div class="p-4 sm:p-6 border-b">
                <h2 class="text-lg font-semibold">Settings</h2>
              </div>
              <div class="p-4 sm:p-6 space-y-6">
                <!-- Role -->
                <div class="space-y-2">
                  <Label for="role" class="text-sm font-medium">Role *</Label>
                  <div class="flex items-center space-x-2">
                    <div class="flex-shrink-0">
                      <Shield class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <div class="flex-1">
                      <Select 
                        v-model="form.role" 
                        :class="form.errors.role ? 'border-destructive' : ''"
                        :disabled="!canEditRoleOffice"
                      >
                        <SelectTrigger class="w-full">
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem
                            v-for="(label, value) in roleOptions"
                            :key="value"
                            :value="value"
                          >
                            <div class="flex items-center space-x-2">
                              <Badge 
                                :variant="value === 'admin' ? 'destructive' : value === 'PIO Officer' ? 'default' : value === 'PIO Staff' ? 'secondary' : 'outline'"
                                class="text-xs"
                              >
                                {{ label }}
                              </Badge>
                            </div>
                          </SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                  </div>
                  <p v-if="form.errors.role" class="text-sm text-destructive">{{ form.errors.role }}</p>
                  <p v-if="!canEditRoleOffice" class="text-xs text-muted-foreground">
                    You cannot change your own role
                  </p>
                </div>

                <!-- Office -->
                <div class="space-y-2">
                  <Label for="office" class="text-sm font-medium">Office *</Label>
                  <div class="flex items-center space-x-2">
                    <div class="flex-shrink-0">
                      <Building class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <div class="flex-1">
                      <Select 
                        v-model="form.office" 
                        :class="form.errors.office ? 'border-destructive' : ''"
                        :disabled="!canEditRoleOffice"
                      >
                        <SelectTrigger class="w-full">
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem
                            v-for="(label, value) in officeOptions"
                            :key="value"
                            :value="value"
                          >
                            {{ label }}
                          </SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                  </div>
                  <p v-if="form.errors.office" class="text-sm text-destructive">{{ form.errors.office }}</p>
                  <p v-if="!canEditRoleOffice" class="text-xs text-muted-foreground">
                    You cannot change your own office
                  </p>
                </div>

                <!-- Status Toggle -->
                <div class="space-y-2" v-if="isAdmin">
                  <Label class="text-sm font-medium">Account Status</Label>
                  <div class="flex items-center justify-between p-3 border rounded-lg bg-muted/50">
                    <div class="space-y-0.5">
                      <Label class="text-sm font-medium">Active Account</Label>
                      <p class="text-xs text-muted-foreground">
                        {{ form.is_active ? 'User can login and access system' : 'User cannot login to system' }}
                      </p>
                    </div>
                    <Switch
                      v-model="form.is_active"
                      :disabled="!canEditStatus"
                      aria-label="Toggle account status"
                    />
                  </div>
                  <p v-if="isEditingSelf" class="text-xs text-amber-600">
                    You cannot deactivate your own account
                  </p>
                  <p v-if="!canEditStatus && !isEditingSelf" class="text-xs text-muted-foreground">
                    Only administrators can change account status
                  </p>
                </div>
              </div>
            </div>

            <!-- User Information Card -->
            <div class="bg-card rounded-lg border shadow-sm">
              <div class="p-4 sm:p-6 border-b">
                <h2 class="text-lg font-semibold">User Information</h2>
              </div>
              <div class="p-4 sm:p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div class="space-y-1">
                    <span class="font-medium text-muted-foreground">User ID:</span>
                    <p class="font-mono text-xs">{{ user.id }}</p>
                  </div>
                  <div class="space-y-1">
                    <span class="font-medium text-muted-foreground">Created:</span>
                    <p>{{ formatDate(user.created_at) }}</p>
                  </div>
                  <div class="space-y-1">
                    <span class="font-medium text-muted-foreground">Last Updated:</span>
                    <p>{{ formatDate(user.updated_at) }}</p>
                  </div>
                  <div class="space-y-1">
                    <span class="font-medium text-muted-foreground">Email Verified:</span>
                    <p :class="user.email_verified_at ? 'text-green-600' : 'text-amber-600'">
                      {{ user.email_verified_at ? 'Yes' : 'No' }}
                    </p>
                  </div>
                  <div class="col-span-2 space-y-1">
                    <span class="font-medium text-muted-foreground">Current Permissions:</span>
                    <div class="flex flex-wrap gap-1 mt-1">
                      <Badge 
                        v-for="permission in form.permissions || []" 
                        :key="permission"
                        variant="outline"
                        class="text-xs"
                      >
                        {{ permissionOptions[permission]?.label || permission }}
                      </Badge>
                      <span v-if="!form.permissions || form.permissions.length === 0" class="text-xs text-muted-foreground">
                        No permissions assigned
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions Card -->
            <div class="bg-card rounded-lg border shadow-sm">
              <div class="p-4 sm:p-6">
                <div class="space-y-3">
                  <Button
                    type="button"
                    @click="openSaveDialog"
                    :disabled="form.processing"
                    class="w-full"
                    size="lg"
                  >
                    <Save class="h-4 w-4 mr-2" />
                    <span v-if="form.processing">Saving...</span>
                    <span v-else>{{ isEditingSelf ? 'Update Profile' : 'Save Changes' }}</span>
                  </Button>
                  
                  <Button
                    type="button"
                    variant="outline"
                    @click="openCancelDialog"
                    :disabled="form.processing"
                    class="w-full"
                  >
                    <ArrowLeft class="h-4 w-4 mr-2" />
                    Cancel
                  </Button>

                  <!-- Impersonate Button (Admin only, not for self) -->
                  <Button
                    v-if="canImpersonateUser"
                    type="button"
                    variant="secondary"
                    @click="openImpersonateDialog"
                    :disabled="form.processing"
                    class="w-full"
                  >
                    <LogOut class="h-4 w-4 mr-2" />
                    Impersonate User
                  </Button>

                  <!-- Delete Button (Admin only, not for self) -->
                  <Button
                    v-if="canDeleteUser"
                    type="button"
                    variant="destructive"
                    @click="openDeleteDialog"
                    :disabled="form.processing"
                    class="w-full"
                  >
                    <Trash2 class="h-4 w-4 mr-2" />
                    Delete User
                  </Button>

                  <div v-if="isEditingSelf" class="text-xs text-muted-foreground text-center p-2 bg-muted/30 rounded">
                    You cannot delete or impersonate your own account
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Save Changes Confirmation Dialog -->
    <AlertDialog v-model:open="saveDialogOpen">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>{{ isEditingSelf ? 'Update Profile?' : 'Save Changes?' }}</AlertDialogTitle>
          <AlertDialogDescription>
            {{ isEditingSelf ? 'Are you sure you want to update your profile?' : 'Are you sure you want to save the changes to this user account?' }}
            <div class="mt-4 p-3 bg-muted rounded-lg space-y-2">
              <div class="flex justify-between">
                <span class="font-medium">Name:</span>
                <span>{{ userSummary.name }}</span>
              </div>
              <div class="flex justify-between">  
                <span class="font-medium">Email:</span>
                <span>{{ userSummary.email }}</span>
              </div>
              <div class="flex justify-between">
                <span class="font-medium">Role:</span>
                <span>{{ userSummary.role }}</span>
              </div>
              <div class="flex justify-between">
                <span class="font-medium">Office:</span>
                <span>{{ userSummary.office }}</span>
              </div>
              <div class="flex justify-between">
                <span class="font-medium">Status:</span>
                <span>{{ userSummary.status }}</span>
              </div>
              <div class="flex justify-between">
                <span class="font-medium">Password:</span>
                <span>{{ userSummary.hasPassword ? 'Changed' : 'Unchanged' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="font-medium">Permissions:</span>
                <span>{{ userSummary.permissions }} modules</span>
              </div>
            </div>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="saveDialogOpen = false">
            Continue Editing
          </AlertDialogCancel>
          <AlertDialogAction @click="confirmSave">
            {{ isEditingSelf ? 'Update Profile' : 'Save Changes' }}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- Cancel Confirmation Dialog -->
    <AlertDialog v-model:open="cancelDialogOpen">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Cancel Changes?</AlertDialogTitle>
          <AlertDialogDescription>
            <span v-if="hasUnsavedChanges">
              You have unsaved changes. If you cancel now, all your changes will be lost.
            </span>
            <span v-else>
              {{ isEditingSelf ? 'This will cancel the profile editing and return you to dashboard.' : 'This will cancel the editing and return you to the user management list.' }}
            </span>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="cancelDialogOpen = false">
            Continue Editing
          </AlertDialogCancel>
          <AlertDialogAction @click="confirmCancel">
            Cancel Changes
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- Delete Confirmation Dialog -->
    <AlertDialog v-model:open="deleteDialogOpen">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
          <AlertDialogDescription>
            This action cannot be undone. This will permanently delete the user
            "{{ user.name }}" and remove their account from our system.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="deleting" @click="deleteDialogOpen = false">
            Cancel
          </AlertDialogCancel>
          <AlertDialogAction 
            @click="deleteUser"
            class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
            :disabled="deleting"
          >
            <div v-if="deleting" class="flex items-center space-x-2">
              <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-white"></div>
              <span>Deleting...</span>
            </div>
            <span v-else>Delete User</span>
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- Impersonate Confirmation Dialog -->
    <AlertDialog v-model:open="impersonateDialogOpen">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Impersonate User?</AlertDialogTitle>
          <AlertDialogDescription>
            You are about to log in as "{{ user.name }}". You will be able to see and do everything as this user until you stop impersonating.
            <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
              <p class="text-sm text-amber-800 font-medium">Security Notice:</p>
              <p class="text-sm text-amber-700 mt-1">
                All actions performed while impersonating will be attributed to this user. 
                Make sure to stop impersonating when you're done.
              </p>
            </div>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="impersonating" @click="impersonateDialogOpen = false">
            Cancel
          </AlertDialogCancel>
          <AlertDialogAction 
            @click="impersonateUser"
            class="bg-amber-600 text-amber-50 hover:bg-amber-700"
            :disabled="impersonating"
          >
            <div v-if="impersonating" class="flex items-center space-x-2">
              <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-white"></div>
              <span>Impersonating...</span>
            </div>
            <span v-else>Impersonate User</span>
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>