<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Save, User, Mail, Shield, Building, Eye, Trash2, CheckSquare, Square, LogOut, Phone, Calendar, Key, BadgeCheck } from 'lucide-vue-next';
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
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';

// Props
const props = defineProps<{
  user: {
    id: string;
    name: string;
    email: string;
    phone: string;
    role: string;
    office: string;
    position: string;
    avatar: string;
    is_active: boolean;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    permissions: string[];
    last_login_at: string | null;
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
const form = useForm<{
  _method: string;
  name: string;
  email: string;
  phone: string;
  role: string;
  office: string;
  position: string;
  is_active: boolean;
  password: string;
  password_confirmation: string;
  permissions: string[];
  avatar: string;
}>({
  _method: 'PUT',
  name: props.user.name,
  email: props.user.email,
  phone: props.user.phone || '',
  role: props.user.role,
  office: props.user.office,
  position: props.user.position || '',
  is_active: props.user.is_active,
  password: '',
  password_confirmation: '',
  permissions: [] as string[],
  avatar: props.user.avatar || '',
});

// Dialog states
const saveDialogOpen = ref(false);
const cancelDialogOpen = ref(false);
const deleteDialogOpen = ref(false);
const impersonateDialogOpen = ref(false);
const avatarDialogOpen = ref(false);
const deleting = ref(false);
const impersonating = ref(false);

// Avatar functionality
const tempSelectedAvatar = ref(props.user.avatar || '');

// Avatar options - empty string represents initials
const avatarOptions = [
  // Initials option (empty string)
  { type: 'initials', display: generateAvatarFromName(props.user.name), value: '' },
  // Predefined avatars
  { type: 'image', display: '/images/avatars/avatar-adventurer-1.svg', value: '/images/avatars/avatar-adventurer-1.svg' },
  { type: 'image', display: '/images/avatars/avatar-adventurer-2.svg', value: '/images/avatars/avatar-adventurer-2.svg' },
  { type: 'image', display: '/images/avatars/avatar-adventurer-3.svg', value: '/images/avatars/avatar-adventurer-3.svg' },
  { type: 'image', display: '/images/avatars/avatar-adventurer-4.svg', value: '/images/avatars/avatar-adventurer-4.svg' },
  { type: 'image', display: '/images/avatars/avatar-adventurer-5.svg', value: '/images/avatars/avatar-adventurer-5.svg' },
  { type: 'image', display: '/images/avatars/avatar-adventurer-6.svg', value: '/images/avatars/avatar-adventurer-6.svg' },
  { type: 'image', display: '/images/avatars/avatar-bottts-1.svg', value: '/images/avatars/avatar-bottts-1.svg' },
  { type: 'image', display: '/images/avatars/avatar-bottts-2.svg', value: '/images/avatars/avatar-bottts-2.svg' },
  { type: 'image', display: '/images/avatars/avatar-micah-1.svg', value: '/images/avatars/avatar-micah-1.svg' },
  { type: 'image', display: '/images/avatars/avatar-micah-2.svg', value: '/images/avatars/avatar-micah-2.svg' },
  { type: 'image', display: '/images/avatars/avatar-pixel-art-1.svg', value: '/images/avatars/avatar-pixel-art-1.svg' },
  { type: 'image', display: '/images/avatars/avatar-pixel-art-2.svg', value: '/images/avatars/avatar-pixel-art-2.svg' },
];

// Generate avatar from name
function generateAvatarFromName(name: string) {
  if (!name) return '';
  const names = name.split(' ');
  let initials = names[0].charAt(0).toUpperCase();
  if (names.length > 1) {
    initials += names[names.length - 1].charAt(0).toUpperCase();
  }
  return initials;
}

const avatarInitials = generateAvatarFromName(props.user.name);

// Computed properties for avatar display
const isInitialsSelected = computed(() => form.avatar === '');
const isImageSelected = computed(() => form.avatar !== '' && form.avatar.startsWith('/images/'));
const currentAvatarDisplay = computed(() => {
  if (isImageSelected.value) {
    return form.avatar;
  }
  return ''; // Empty for initials
});

// Avatar selection in dialog
const selectAvatarInDialog = (avatarValue: string) => {
  tempSelectedAvatar.value = avatarValue;
};

// Confirm avatar selection
const confirmAvatarSelection = () => {
  form.avatar = tempSelectedAvatar.value;
  avatarDialogOpen.value = false;
};

// Cancel avatar selection
const cancelAvatarSelection = () => {
  tempSelectedAvatar.value = form.avatar; // Reset to current selection
  avatarDialogOpen.value = false;
};

// Initialize temp selection when dialog opens
const openAvatarDialog = () => {
  tempSelectedAvatar.value = form.avatar;
  avatarDialogOpen.value = true;
};

// Permission selection
const selectAllPermissions = ref(false);

// Get all available permissions
const allPermissions = computed(() => {
  return Object.keys(props.permissionOptions);
});

// Get permissions that can be edited (exclude dashboard)
const editablePermissions = computed(() => {
  return allPermissions.value.filter(permission => permission !== 'dashboard');
});

// Check if user is superadmin
const isSuperAdmin = computed(() => {
  return form.role === 'superadmin';
});

// Check if permission is enabled in database (original user permissions)
const hasPermissionInDatabase = (permission: string) => {
  return Array.isArray(props.user.permissions) && props.user.permissions.includes(permission);
};

// Toggle all permissions
const toggleAllPermissions = (checked: boolean) => {
  if (!canEditPermissions.value || isSuperAdmin.value) return;
  
  selectAllPermissions.value = checked;
  
  if (checked) {
    // Add all editable permissions (excluding dashboard)
    form.permissions = [...editablePermissions.value];
  } else {
    // Remove all permissions except dashboard
    form.permissions = ['dashboard'];
  }
};

// Toggle single permission using switch
const togglePermission = (permission: string, checked: boolean) => {
  if (!canEditPermissions.value || isSuperAdmin.value || permission === 'dashboard') return;
  
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

// Check if permission is enabled in current form
const isPermissionEnabled = (permission: string) => {
  if (isSuperAdmin.value) return true; // Superadmin always has all permissions
  if (permission === 'dashboard') return true; // Dashboard is always enabled for all roles
  return form.permissions.includes(permission);
};

// Update select all state based on current permissions
const updateSelectAllState = () => {
  if (isSuperAdmin.value) {
    selectAllPermissions.value = true;
  } else {
    // Check if all editable permissions are enabled
    const allEditableEnabled = editablePermissions.value.every(permission => 
      form.permissions.includes(permission)
    );
    selectAllPermissions.value = allEditableEnabled && editablePermissions.value.length > 0;
  }
};

// Watch role changes to handle superadmin permissions
watch(() => form.role, (newRole) => {
  if (newRole === 'superadmin') {
    // Automatically enable all permissions for superadmin
    form.permissions = [...allPermissions.value];
    selectAllPermissions.value = true;
  } else if (props.user.role === 'superadmin' && newRole !== 'superadmin') {
    // If changing from superadmin to another role, reset to original permissions but ensure dashboard is included
    const originalPermissions = Array.isArray(props.user.permissions) ? [...props.user.permissions] : [];
    form.permissions = [...new Set([...originalPermissions, 'dashboard'])];
    updateSelectAllState();
  }
});

// Watch permissions to update select all
watch(() => form.permissions, () => {
  updateSelectAllState();
}, { deep: true });

// Ensure dashboard permission is always included
watch(() => form.permissions, (newPermissions) => {
  if (!newPermissions.includes('dashboard')) {
    form.permissions = ['dashboard', ...newPermissions];
  }
}, { deep: true, immediate: true });

// Check if form has unsaved changes
const hasUnsavedChanges = computed(() => {
  const originalPermissions = Array.isArray(props.user.permissions) ? [...props.user.permissions].sort() : [];
  const currentPermissions = [...form.permissions].sort();
  
  return form.name !== props.user.name ||
         form.email !== props.user.email ||
         form.phone !== props.user.phone ||
         form.role !== props.user.role ||
         form.office !== props.user.office ||
         form.position !== props.user.position ||
         form.avatar !== props.user.avatar ||
         form.is_active !== props.user.is_active ||
         form.password !== '' ||
         form.password_confirmation !== '' ||
         JSON.stringify(currentPermissions) !== JSON.stringify(originalPermissions);
});

// Check if save button should be disabled
const isSaveDisabled = computed(() => {
  return !hasUnsavedChanges.value || form.processing;
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
    permissions: isSuperAdmin.value ? 'All (Superadmin)' : `${form.permissions.length} enabled`,
  };
});

// Check if current user is editing their own profile
const isEditingSelf = computed(() => {
  return props.user.id === (page.props.auth.user as any).id;
});

// Check if current user is admin
const isAdmin = computed(() => {
  const authUser = page.props.auth.user as any;
  return authUser.role === 'admin' || authUser.role === 'superadmin';
});

// Check if current user is superadmin
const isCurrentUserSuperAdmin = computed(() => {
  const authUser = page.props.auth.user as any;
  return authUser.role === 'superadmin';
});

// Check if can edit permissions (admin or superadmin, but not for self)
const canEditPermissions = computed(() => {
  return (isAdmin.value || isCurrentUserSuperAdmin.value) && !isEditingSelf.value;
});

// Check if can edit role and office (admin or superadmin, but not for self)
const canEditRoleOffice = computed(() => {
  return (isAdmin.value || isCurrentUserSuperAdmin.value) && !isEditingSelf.value;
});

// Check if can edit status (admin or superadmin, but not for self)
const canEditStatus = computed(() => {
  return (isAdmin.value || isCurrentUserSuperAdmin.value) && !isEditingSelf.value;
});

// Check if can delete user (admin or superadmin, but not for self)
const canDeleteUser = computed(() => {
  return (isAdmin.value || isCurrentUserSuperAdmin.value) && !isEditingSelf.value;
});

// Check if can impersonate user (superadmin only, not for self)
const canImpersonateUser = computed(() => {
  return isCurrentUserSuperAdmin.value && !isEditingSelf.value;
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
  // Only validate role and office if admin/superadmin is editing another user
  if ((isAdmin.value || isCurrentUserSuperAdmin.value) && !isEditingSelf.value) {
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

// Cancel and go back to user management list
const cancel = () => {
  router.visit('/user-management');
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

// Format last update date
const formatLastUpdate = (dateString: string) => {
  if (!dateString) return 'Never updated';
  
  const date = new Date(dateString);
  const now = new Date();
  const diffTime = Math.abs(now.getTime() - date.getTime());
  const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays === 0) {
    return 'Today at ' + date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
  } else if (diffDays === 1) {
    return 'Yesterday at ' + date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
  } else if (diffDays < 7) {
    return `${diffDays} days ago`;
  } else {
    return date.toLocaleDateString('en-US', { 
      year: 'numeric', 
      month: 'short', 
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }
};

const lastUpdate = formatLastUpdate(props.user.updated_at);

// Get role badge variant
const getRoleBadgeVariant = (role: string) => {
  switch (role) {
    case 'superadmin': return 'destructive';
    case 'admin': return 'default';
    case 'staff': return 'secondary';
    default: return 'outline';
  }
}

// Initialize permissions
const initializePermissions = () => {
  shownFlashMessages.value.clear();
  
  // Handle different formats of permissions from props
  let initialPermissions: string[] = [];
  
  if (Array.isArray(props.user.permissions)) {
    // Already an array - use as is
    initialPermissions = [...props.user.permissions];
  } else if (typeof props.user.permissions === 'string') {
    // Try to parse as JSON
    try {
      const parsed = JSON.parse(props.user.permissions);
      if (Array.isArray(parsed)) {
        initialPermissions = parsed;
      } else {
        initialPermissions = ['dashboard'];
      }
    } catch (e) {
      initialPermissions = ['dashboard'];
    }
  } else {
    // Fallback to just dashboard
    initialPermissions = ['dashboard'];
  }
  
  // Ensure permissions is always an array in the form
  form.permissions = initialPermissions;
  
  // Ensure dashboard permission is always included
  if (!form.permissions.includes('dashboard')) {
    form.permissions = ['dashboard', ...form.permissions];
  }
  
  // If user is superadmin, automatically enable all permissions
  if (form.role === 'superadmin') {
    form.permissions = [...allPermissions.value];
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
            <div class="flex items-center space-x-2">
              <Button
                variant="ghost"
                size="sm"
                @click="cancel"
                class="h-8 w-8 p-0"
              >
                <ArrowLeft class="h-4 w-4" />
              </Button>
              <h1 class="text-2xl sm:text-3xl font-bold text-foreground truncate">Edit User</h1>
            </div>
            <p class="text-muted-foreground mt-1">Update user details and permissions</p>
            <div v-if="isEditingSelf" class="mt-2">
              <Badge variant="outline" class="bg-blue-50 text-blue-700 border-blue-200">
                Editing your own profile
              </Badge>
            </div>
            <div v-else-if="isCurrentUserSuperAdmin" class="mt-2">
              <Badge variant="outline" class="bg-purple-50 text-purple-700 border-purple-200">
                Super Admin Editing User
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
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <User class="h-5 w-5 text-blue-600" />
                  Basic Information
                </CardTitle>
                <CardDescription>
                  Update user's personal and contact information
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-6">
                <!-- Avatar Section -->
                <div class="w-full grid gap-4">
                  <Label for="avatar" class="text-base font-medium">Profile Picture</Label>
                  
                  <!-- Current Avatar Display -->
                  <div class="w-full flex items-center gap-6 p-4 bg-muted/30 rounded-lg border">
                    <div class="flex items-center gap-4">
                      <div 
                        v-if="isImageSelected"
                        class="w-16 h-16 rounded-full bg-cover bg-center border-2 border-border shadow-sm"
                        :style="{ backgroundImage: `url('${currentAvatarDisplay}')` }"
                      ></div>
                      <div 
                        v-else
                        class="w-16 h-16 rounded-full bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center text-white font-semibold text-xl border-2 border-border shadow-sm"
                      >
                        {{ avatarInitials }}
                      </div>
                    </div>
                    
                    <div class="flex-1">
                      <p class="text-sm font-medium text-foreground">Current Avatar</p>
                      <p class="text-xs text-muted-foreground">
                        {{ isImageSelected ? 'Selected avatar' : 'Initials based on name' }}
                      </p>
                    </div>

                    <!-- Avatar Selection Dialog Trigger -->
                    <Dialog v-model:open="avatarDialogOpen">
                      <DialogTrigger as-child>
                        <Button variant="outline" size="sm" @click="openAvatarDialog">
                          Change Avatar
                        </Button>
                      </DialogTrigger>
                      <DialogContent class="sm:max-w-md">
                        <DialogHeader>
                          <DialogTitle>Choose Avatar</DialogTitle>
                          <DialogDescription>
                            Select an avatar or use initials as profile picture.
                          </DialogDescription>
                        </DialogHeader>
                        <div class="grid grid-cols-6 gap-3 py-4">
                          <!-- Avatar Options -->
                          <div
                            v-for="(avatar, index) in avatarOptions"
                            :key="index"
                            class="aspect-square rounded-full border-2 cursor-pointer hover:border-primary transition-all hover:scale-105"
                            :class="[
                              tempSelectedAvatar === avatar.value ? 'border-primary ring-2 ring-primary/30' : 'border-border'
                            ]"
                            @click="selectAvatarInDialog(avatar.value)"
                          >
                            <!-- Initials Avatar -->
                            <div
                              v-if="avatar.type === 'initials'"
                              class="w-full h-full rounded-full bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center text-white font-medium text-sm"
                            >
                              {{ avatar.display }}
                            </div>
                            <!-- Image Avatar -->
                            <img 
                              v-else
                              :src="avatar.display" 
                              :alt="`Avatar ${index}`"
                              class="w-full h-full rounded-full object-cover bg-gray-100"
                              loading="lazy"
                            />
                          </div>
                        </div>
                        <div class="flex justify-end gap-2">
                          <Button 
                            variant="outline" 
                            @click="cancelAvatarSelection"
                          >
                            Cancel
                          </Button>
                          <Button 
                            @click="confirmAvatarSelection"
                          >
                            Confirm Selection
                          </Button>
                        </div>
                      </DialogContent>
                    </Dialog>
                  </div>
                  
                  <!-- Hidden input to store the selected avatar -->
                  <input type="hidden" name="avatar" v-model="form.avatar" />
                  <p v-if="form.errors.avatar" class="text-sm text-destructive">{{ form.errors.avatar }}</p>
                </div>

                <!-- Name and Email -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                      <BadgeCheck class="h-3 w-3" />
                      <span>Email verified</span>
                      <span class="text-muted-foreground">• {{ formatDate(user.email_verified_at) }}</span>
                    </div>
                    <div v-else class="flex items-center space-x-1 text-sm text-amber-600">
                      <span>⚠ Email not verified</span>
                    </div>
                  </div>
                </div>

                <!-- Phone and Position -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- Phone -->
                  <div class="space-y-2">
                    <Label for="phone" class="text-sm font-medium">Phone Number</Label>
                    <div class="relative">
                      <Phone class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                      <Input
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        placeholder="Enter phone number"
                        :class="form.errors.phone ? 'border-destructive pl-10' : 'pl-10'"
                        class="w-full"
                      />
                    </div>
                    <p v-if="form.errors.phone" class="text-sm text-destructive">{{ form.errors.phone }}</p>
                  </div>

                  <!-- Position -->
                  <div class="space-y-2">
                    <Label for="position" class="text-sm font-medium">Position</Label>
                    <Input
                      id="position"
                      v-model="form.position"
                      type="text"
                      placeholder="Enter position"
                      :class="form.errors.position ? 'border-destructive' : ''"
                      class="w-full"
                    />
                    <p v-if="form.errors.position" class="text-sm text-destructive">{{ form.errors.position }}</p>
                  </div>
                </div>

                <!-- Password Section -->
                <div class="space-y-4 pt-4 border-t">
                  <Label class="text-sm font-medium flex items-center gap-2">
                    <Key class="h-4 w-4" />
                    Change Password
                  </Label>
                  <p class="text-sm text-muted-foreground">
                    Leave blank to keep current password
                  </p>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
              </CardContent>
            </Card>

            <!-- Permissions Card -->
            <Card v-if="isAdmin || isCurrentUserSuperAdmin">
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <CheckSquare class="h-5 w-5 text-blue-600" />
                  Module Permissions
                  <Badge v-if="isSuperAdmin" variant="destructive" class="ml-2">
                    Super Administrator - Full Access
                  </Badge>
                </CardTitle>
                <CardDescription>
                  {{ isSuperAdmin ? 'Super administrators have access to all modules and system features.' : 'Select which modules and pages this user can access' }}
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-6">
                <!-- Select All Toggle -->
                <div v-if="!isSuperAdmin" class="flex items-center justify-between p-4 border rounded-lg bg-muted/30">
                  <div class="space-y-0.5">
                    <Label class="text-base font-medium">Select All Permissions</Label>
                    <p class="text-sm text-muted-foreground">
                      Enable or disable all permissions at once (except Dashboard)
                    </p>
                  </div>
                  <Switch
                    :model-value="selectAllPermissions"
                    @update:model-value="toggleAllPermissions"
                    :disabled="!canEditPermissions || isSuperAdmin"
                  />
                </div>

                <!-- Superadmin Notice -->
                <div v-if="isSuperAdmin" class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                  <div class="flex items-center space-x-2">
                    <Shield class="h-5 w-5 text-blue-600" />
                    <div>
                      <p class="text-sm font-medium text-blue-800">Super Administrator Access</p>
                      <p class="text-sm text-blue-600 mt-1">
                        Super administrators automatically have full access to all system modules and features. 
                        All permissions are enabled and cannot be modified.
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Permissions List - Simple grid without grouping -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <div
                    v-for="permission in editablePermissions"
                    :key="permission"
                    class="flex flex-row items-center justify-between rounded-lg border p-4 transition-colors"
                    :class="[
                      canEditPermissions && !isSuperAdmin ? 'hover:bg-muted/30 cursor-pointer' : '',
                      isPermissionEnabled(permission) ? 'bg-green-50 border-green-200' : 'bg-muted/30 border-muted'
                    ]"
                    @click="canEditPermissions && !isSuperAdmin && togglePermission(permission, !isPermissionEnabled(permission))"
                  >
                    <div class="space-y-0.5 flex-1">
                      <Label class="text-base font-medium cursor-pointer" :class="isPermissionEnabled(permission) ? 'text-green-800' : 'text-foreground'">
                        {{ permissionOptions[permission]?.label || permission }}
                      </Label>
                      <p class="text-sm" :class="isPermissionEnabled(permission) ? 'text-green-600' : 'text-muted-foreground'">
                        {{ permissionOptions[permission]?.description || 'No description available' }}
                      </p>
                      <!-- Database permission indicator -->
                      <div v-if="hasPermissionInDatabase(permission) && !isSuperAdmin" class="flex items-center space-x-1 mt-1">
                        
                      </div>
                    </div>
                    <Switch
                      :model-value="isPermissionEnabled(permission)"
                      @update:model-value="(checked: boolean) => togglePermission(permission, checked)"
                      :disabled="!canEditPermissions || isSuperAdmin"
                      @click.stop
                      :class="isPermissionEnabled(permission) ? 'bg-green-600' : ''"
                    />
                  </div>
                </div>

                <!-- Dashboard Permission (Always Enabled) -->
                <div class="border-t pt-4 mt-4">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div
                      class="flex flex-row items-center justify-between rounded-lg border p-4 bg-green-50 border-green-200"
                    >
                      <div class="space-y-0.5 flex-1">
                        <Label class="text-base font-medium text-green-800">
                          {{ permissionOptions['dashboard']?.label || 'Dashboard' }}
                        </Label>
                        <p class="text-sm text-green-600">
                          {{ permissionOptions['dashboard']?.description || 'Access to the main dashboard' }}
                        </p>
                      </div>
                      <Switch
                        :model-value="true"
                        disabled
                        class="bg-green-600"
                      />
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
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Shield class="h-5 w-5 text-blue-600" />
                  Account Settings
                </CardTitle>
                <CardDescription>
                  Configure user role and account status
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-6">
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
                                :variant="getRoleBadgeVariant(value)"
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
                <div class="space-y-2" v-if="isAdmin || isCurrentUserSuperAdmin">
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
              </CardContent>
            </Card>

            <!-- User Information Card -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <User class="h-5 w-5 text-blue-600" />
                  User Information
                </CardTitle>
                <CardDescription>
                  Account details and activity information
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-1 gap-3 text-sm">
                  <div class="flex justify-between items-center p-2 bg-muted/30 rounded">
                    <span class="font-medium text-muted-foreground">User ID:</span>
                    <span class="font-mono text-xs">{{ user.id }}</span>
                  </div>
                  <div class="flex justify-between items-center p-2 bg-muted/30 rounded">
                    <span class="font-medium text-muted-foreground">Created:</span>
                    <span>{{ formatDate(user.created_at) }}</span>
                  </div>
                  <div class="flex justify-between items-center p-2 bg-muted/30 rounded">
                    <span class="font-medium text-muted-foreground">Last Updated:</span>
                    <span>{{ lastUpdate }}</span>
                  </div>
                  <div class="flex justify-between items-center p-2 bg-muted/30 rounded">
                    <span class="font-medium text-muted-foreground">Last Login:</span>
                    <span>{{ formatDate(user.last_login_at) }}</span>
                  </div>
                  <div class="flex justify-between items-center p-2 bg-muted/30 rounded">
                    <span class="font-medium text-muted-foreground">Email Verified:</span>
                    <span :class="user.email_verified_at ? 'text-green-600' : 'text-amber-600'">
                      {{ user.email_verified_at ? 'Yes' : 'No' }}
                    </span>
                  </div>
                  
                  <!-- Permissions Display -->
                  <div class="col-span-2 space-y-3 p-3 bg-muted/30 rounded">
                    <div class="flex justify-between items-center">
                      <span class="font-medium text-muted-foreground">Current Permissions:</span>
                      <span class="text-xs font-medium bg-primary text-primary-foreground px-2 py-1 rounded-full">
                        {{ isSuperAdmin ? 'All (Superadmin)' : `${form.permissions.length} enabled` }}
                      </span>
                    </div>
                    <div class="space-y-2">
                      <div 
                        v-for="permission in allPermissions" 
                        :key="permission"
                        class="flex items-center justify-between p-2 bg-background rounded border transition-all"
                        :class="isPermissionEnabled(permission) ? 'bg-green-50 border-green-200' : 'opacity-60'"
                      >
                        <div class="flex items-center space-x-2">
                          <div 
                            class="w-2 h-2 rounded-full transition-colors"
                            :class="isPermissionEnabled(permission) ? 'bg-green-500' : 'bg-gray-400'"
                          ></div>
                          <span class="text-sm font-medium" :class="isPermissionEnabled(permission) ? 'text-green-800' : 'text-muted-foreground'">
                            {{ permissionOptions[permission]?.label || permission }}
                          </span>
                        </div>
                        <Badge 
                          variant="outline" 
                          class="text-xs"
                          :class="isPermissionEnabled(permission) ? 'bg-green-50 text-green-700 border-green-200' : 'bg-gray-100 text-gray-600 border-gray-300'"
                        >
                          {{ isPermissionEnabled(permission) ? 'Enabled' : 'Disabled' }}
                        </Badge>
                      </div>
                      <div v-if="!isSuperAdmin && (!form.permissions || form.permissions.length === 0)" class="text-center p-4 text-muted-foreground">
                        <CheckSquare class="h-8 w-8 mx-auto mb-2 opacity-50" />
                        <span class="text-sm">No permissions assigned</span>
                      </div>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Actions Card -->
            <Card>
              <CardHeader>
                <CardTitle>Actions</CardTitle>
                <CardDescription>
                  Manage user account actions
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div class="space-y-3">
                  <Button
                    type="button"
                    @click="openSaveDialog"
                    :disabled="isSaveDisabled"
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

                  <!-- Impersonate Button (Superadmin only, not for self) -->
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

                  <!-- Delete Button (Admin/Superadmin only, not for self) -->
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
              </CardContent>
            </Card>
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
                <span>{{ userSummary.permissions }}</span>
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
              This will cancel the editing and return you to the user management list.
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