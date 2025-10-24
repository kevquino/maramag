<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Save, Trash2, LogOut, User, Mail, Shield, Building, CheckSquare, Phone, Key, BadgeCheck, Info, X, RotateCcw } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
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

// Composables
import { useFlashMessages } from '@/composables/useFlashMessages';

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
useFlashMessages();

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

// Form state
const formState = ref({
  name: props.user.name,
  email: props.user.email,
  phone: props.user.phone,
  role: props.user.role,
  office: props.user.office,
  position: props.user.position,
  avatar: props.user.avatar,
  is_active: props.user.is_active,
  password: '',
  password_confirmation: '',
  permissions: [...props.user.permissions],
  processing: false,
  errors: {} as Record<string, string>,
});

// Debug: Log permissions data
onMounted(() => {
  console.log('User permissions:', props.user.permissions);
  console.log('Permission options:', props.permissionOptions);
  console.log('Permission groups:', props.permissionGroups);
  console.log('Can edit permissions:', props.canEditPermissions);
  
  // Ensure dashboard permission is always included
  if (!formState.value.permissions.includes('dashboard')) {
    formState.value.permissions.push('dashboard');
  }
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
const isInitialsSelected = computed(() => formState.value.avatar === '');
const isImageSelected = computed(() => formState.value.avatar !== '' && formState.value.avatar?.startsWith('/images/'));
const currentAvatarDisplay = computed(() => {
  if (isImageSelected.value) {
    return formState.value.avatar;
  }
  return ''; // Empty for initials
});

// Avatar selection in dialog
const selectAvatarInDialog = (avatarValue: string) => {
  tempSelectedAvatar.value = avatarValue;
};

// Confirm avatar selection
const confirmAvatarSelection = () => {
  formState.value.avatar = tempSelectedAvatar.value;
  avatarDialogOpen.value = false;
};

// Cancel avatar selection
const cancelAvatarSelection = () => {
  tempSelectedAvatar.value = formState.value.avatar || ''; // Reset to current selection
  avatarDialogOpen.value = false;
};

// Initialize temp selection when dialog opens
const openAvatarDialog = () => {
  tempSelectedAvatar.value = formState.value.avatar || '';
  avatarDialogOpen.value = true;
};

// Permission management
const togglePermission = (permission: string) => {
  if (!props.canEditPermissions) return;
  
  // Dashboard permission cannot be changed - always enabled
  if (permission === 'dashboard') return;
  
  const currentPermissions = [...formState.value.permissions];
  const index = currentPermissions.indexOf(permission);
  
  if (index > -1) {
    // Remove permission
    currentPermissions.splice(index, 1);
  } else {
    // Add permission
    currentPermissions.push(permission);
  }
  
  // Update the form state
  formState.value.permissions = currentPermissions;
};

const hasPermission = (permission: string) => {
  // Dashboard permission is always enabled
  if (permission === 'dashboard') return true;
  
  return formState.value.permissions.includes(permission);
};

// Check if form has unsaved changes
const hasUnsavedChanges = computed(() => {
  const form = formState.value;

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
  return !hasUnsavedChanges.value || formState.value.processing;
});

// Get user summary for confirmation dialogs
const userSummary = computed(() => {
  const form = formState.value;
  
  const isSuperAdmin = form.role === 'superadmin';
  
  return {
    name: form.name || 'Unnamed User',
    email: form.email || 'No email',
    role: props.roleOptions[form.role] || form.role,
    office: props.officeOptions[form.office] || form.office,
    status: form.is_active ? 'Active' : 'Inactive',
    hasPassword: !!form.password,
    permissions: isSuperAdmin ? 'All (Superadmin)' : `${form.permissions.length} enabled`,
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
  formState.value.processing = true;
  
  // Convert boolean to string for FormData compatibility
  const formData = new FormData();
  Object.entries(formState.value).forEach(([key, value]) => {
    if (key === 'permissions' && Array.isArray(value)) {
      value.forEach(permission => formData.append('permissions[]', permission));
    } else if (key === 'is_active') {
      // Ensure is_active is sent as '1' or '0' to avoid boolean conversion issues
      formData.append(key, value ? '1' : '0');
    } else if (value !== null && value !== undefined) {
      formData.append(key, value as string);
    }
  });
  
  formData.append('_method', 'PUT');

  router.post(`/user-management/${props.user.id}`, formData, {
    preserveScroll: true,
    onSuccess: () => {
      formState.value.processing = false;
      // Success handled by flash messages - controller will redirect to show page
    },
    onError: (errors: any) => {
      formState.value.processing = false;
      formState.value.errors = errors;
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
  // Basic validation
  if (!formState.value.name?.trim()) {
    toast.error('Name is required');
    return;
  }
  if (!formState.value.email?.trim()) {
    toast.error('Email is required');
    return;
  }
  if (formState.value.password && formState.value.password !== formState.value.password_confirmation) {
    toast.error('Passwords do not match');
    return;
  }
  saveDialogOpen.value = true;
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
        // Success handled by flash messages
      },
      onError: (errors: any) => {
        const errorMsg = errors.message || 'Failed to delete user';
        toast.error(errorMsg);
        deleting.value = false;
        deleteDialogOpen.value = false;
      },
    });
  } catch (err) {
    console.error('Failed to delete user:', err);
    const errorMsg = err instanceof Error ? err.message : 'Failed to delete user';
    toast.error(errorMsg);
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
      onError: (errors: any) => {
        const errorMsg = errors.message || 'Failed to impersonate user';
        toast.error(errorMsg);
        impersonating.value = false;
        impersonateDialogOpen.value = false;
      },
    });
  } catch (err) {
    console.error('Failed to impersonate user:', err);
    const errorMsg = err instanceof Error ? err.message : 'Failed to impersonate user';
    toast.error(errorMsg);
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
              <Badge variant="outline" class="bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950 dark:text-blue-300 dark:border-blue-800">
                Editing your own profile
              </Badge>
            </div>
            <div v-else-if="isCurrentUserSuperAdmin" class="mt-2">
              <Badge variant="outline" class="bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950 dark:text-purple-300 dark:border-purple-800">
                Super Admin Editing User
              </Badge>
            </div>
            <div v-else-if="isAdmin" class="mt-2">
              <Badge variant="outline" class="bg-green-50 text-green-700 border-green-200 dark:bg-green-950 dark:text-green-300 dark:border-green-800">
                Admin Editing User
              </Badge>
            </div>
          </div>
        </div>

        <!-- Error summary -->
        <div v-if="formState.errors && Object.keys(formState.errors).length" class="mb-6 p-4 bg-destructive/15 border border-destructive/50 text-destructive rounded-lg">
          <h3 class="font-semibold mb-2">Please fix the following errors:</h3>
          <ul class="list-disc list-inside space-y-1">
            <li v-for="(error, field) in formState.errors" :key="field">
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
                  <input type="hidden" name="avatar" v-model="formState.avatar" />
                  <p v-if="formState.errors?.avatar" class="text-sm text-destructive">{{ formState.errors.avatar }}</p>
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
                        v-model="formState.name"
                        type="text"
                        placeholder="Enter full name"
                        :class="formState.errors?.name ? 'border-destructive pl-10' : 'pl-10'"
                        class="w-full"
                      />
                    </div>
                    <p v-if="formState.errors?.name" class="text-sm text-destructive">{{ formState.errors.name }}</p>
                  </div>

                  <!-- Email -->
                  <div class="space-y-2">
                    <Label for="email" class="text-sm font-medium">Email Address *</Label>
                    <div class="relative">
                      <Mail class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                      <Input
                        id="email"
                        v-model="formState.email"
                        type="email"
                        placeholder="Enter email address"
                        :class="formState.errors?.email ? 'border-destructive pl-10' : 'pl-10'"
                        class="w-full"
                      />
                    </div>
                    <p v-if="formState.errors?.email" class="text-sm text-destructive">{{ formState.errors.email }}</p>
                    <div v-if="user.email_verified_at" class="flex items-center space-x-1 text-sm text-green-600 dark:text-green-400">
                      <BadgeCheck class="h-3 w-3" />
                      <span>Email verified</span>
                      <span class="text-muted-foreground">• {{ formatDate(user.email_verified_at) }}</span>
                    </div>
                    <div v-else class="flex items-center space-x-1 text-sm text-amber-600 dark:text-amber-400">
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
                        v-model="formState.phone"
                        type="tel"
                        placeholder="Enter phone number"
                        :class="formState.errors?.phone ? 'border-destructive pl-10' : 'pl-10'"
                        class="w-full"
                      />
                    </div>
                    <p v-if="formState.errors?.phone" class="text-sm text-destructive">{{ formState.errors.phone }}</p>
                  </div>

                  <!-- Position -->
                  <div class="space-y-2">
                    <Label for="position" class="text-sm font-medium">Position</Label>
                    <Input
                      id="position"
                      v-model="formState.position"
                      type="text"
                      placeholder="Enter position"
                      :class="formState.errors?.position ? 'border-destructive' : ''"
                      class="w-full"
                    />
                    <p v-if="formState.errors?.position" class="text-sm text-destructive">{{ formState.errors.position }}</p>
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
                        v-model="formState.password"
                        type="password"
                        placeholder="Enter new password"
                        :class="formState.errors?.password ? 'border-destructive' : ''"
                        class="w-full"
                      />
                      <p v-if="formState.errors?.password" class="text-sm text-destructive">{{ formState.errors.password }}</p>
                    </div>

                    <div class="space-y-2">
                      <Label for="password_confirmation" class="text-sm font-medium">Confirm New Password</Label>
                      <Input
                        id="password_confirmation"
                        v-model="formState.password_confirmation"
                        type="password"
                        placeholder="Confirm new password"
                        :class="formState.errors?.password_confirmation ? 'border-destructive' : ''"
                        class="w-full"
                      />
                      <p v-if="formState.errors?.password_confirmation" class="text-sm text-destructive">{{ formState.errors.password_confirmation }}</p>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Permissions Card - UPDATED: No Checkboxes, Card-based Selection -->
            <Card v-if="(isAdmin || isCurrentUserSuperAdmin) && formState.role !== 'superadmin' && permissionOptions && Object.keys(permissionOptions).length > 0">
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <CheckSquare class="h-5 w-5 text-blue-600" />
                  Module Permissions
                  <Badge variant="outline" class="ml-2">
                    {{ formState.permissions.length }} enabled
                  </Badge>
                </CardTitle>
                <CardDescription>
                  {{ canEditPermissions ? 'Click on permission cards to enable or disable access' : 'You do not have permission to edit permissions for this user' }}
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  <div
                    v-for="(permission, key) in permissionOptions"
                    :key="key"
                    class="flex items-start p-4 border-2 rounded-lg transition-all duration-200 group cursor-pointer select-none"
                    :class="[
                      hasPermission(key) 
                        ? 'bg-green-50 border-green-500 dark:bg-green-950/50 dark:border-green-600 shadow-md scale-[1.02]' 
                        : 'bg-muted/30 border-muted hover:bg-muted/50',
                      canEditPermissions && key !== 'dashboard' ? 'hover:border-green-300 dark:hover:border-green-700' : '',
                      key === 'dashboard' ? 'cursor-not-allowed opacity-80' : ''
                    ]"
                    @click="canEditPermissions && togglePermission(key)"
                  >
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center justify-between mb-2">
                        <Label 
                          class="text-sm font-semibold text-foreground"
                          :class="key === 'dashboard' ? 'cursor-not-allowed' : 'cursor-pointer'"
                        >
                          {{ permission.label }}
                          
                        </Label>
                        <div 
                          v-if="hasPermission(key)"
                          class="flex-shrink-0 w-5 h-5 bg-green-500 rounded-full flex items-center justify-center"
                        >
                          <CheckSquare class="h-3 w-3 text-white" />
                        </div>
                        <div 
                          v-else
                          class="flex-shrink-0 w-5 h-5 border-2 border-muted-foreground/30 rounded"
                        ></div>
                      </div>
                      <p class="text-xs text-muted-foreground">
                        {{ permission.description }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Quick Actions - Only show if user can edit permissions -->
                <div v-if="canEditPermissions" class="flex flex-wrap gap-2 pt-4 border-t">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="formState.permissions = Object.keys(permissionOptions)"
                    class="text-xs"
                  >
                    <CheckSquare class="h-3 w-3 mr-1" />
                    Grant All Permissions
                  </Button>
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="formState.permissions = ['dashboard']"
                    class="text-xs"
                  >
                    <X class="h-3 w-3 mr-1" />
                    Revoke All Permissions
                  </Button>
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="formState.permissions = [...props.user.permissions]"
                    class="text-xs"
                  >
                    <RotateCcw class="h-3 w-3 mr-1" />
                    Reset to Original
                  </Button>
                </div>

                <!-- Permission editing instructions -->
                <div v-if="canEditPermissions" class="p-3 bg-blue-50 border border-blue-200 dark:bg-blue-950/30 dark:border-blue-800 rounded-lg">
                  <div class="flex items-start space-x-2">
                    <Info class="h-4 w-4 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" />
                    <div class="flex-1">
                      <p class="text-sm text-blue-800 dark:text-blue-300 font-medium">Permission Management</p>
                      <p class="text-xs text-blue-700 dark:text-blue-400 mt-1">
                        Click on permission cards to enable or disable access. Green cards with checkmarks indicate enabled permissions. 
                        <span class="font-semibold">Dashboard permission is always enabled and cannot be changed.</span>
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Debug information -->
                <div v-if="formState.permissions.length > 0" class="p-3 bg-muted border border-muted-foreground/20 rounded-lg">
                  <p class="text-sm font-medium">Current Permissions ({{ formState.permissions.length }}):</p>
                  <p class="text-xs text-muted-foreground mt-1">{{ formState.permissions.join(', ') }}</p>
                </div>
              </CardContent>
            </Card>

            <!-- Super Admin Notice Card -->
            <Card v-if="formState.role === 'superadmin'" class="bg-gradient-to-r from-purple-50 to-pink-50 border-purple-200 dark:from-purple-950/30 dark:to-pink-950/30 dark:border-purple-800">
              <CardHeader>
                <CardTitle class="flex items-center gap-2 text-purple-900 dark:text-purple-200">
                  <Shield class="h-5 w-5" />
                  Super Administrator Access
                </CardTitle>
                <CardDescription class="text-purple-700 dark:text-purple-300">
                  This user has full access to all system features and modules
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div class="flex items-center space-x-3 p-3 bg-white/50 dark:bg-gray-900/50 rounded-lg border border-purple-200 dark:border-purple-800">
                  <BadgeCheck class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                  <div>
                    <p class="font-medium text-purple-900 dark:text-purple-200">Full System Access</p>
                    <p class="text-sm text-purple-700 dark:text-purple-300">
                      Super administrators automatically have all permissions enabled and can access every part of the system.
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- No Permissions Available Card -->
            <Card v-if="(isAdmin || isCurrentUserSuperAdmin) && formState.role !== 'superadmin' && (!permissionOptions || Object.keys(permissionOptions).length === 0)" class="bg-amber-50 border-amber-200 dark:bg-amber-950/30 dark:border-amber-800">
              <CardHeader>
                <CardTitle class="flex items-center gap-2 text-amber-900 dark:text-amber-200">
                  <CheckSquare class="h-5 w-5" />
                  No Permissions Available
                </CardTitle>
                <CardDescription class="text-amber-700 dark:text-amber-300">
                  Permission options are not configured in the system
                </CardDescription>
              </CardHeader>
              <CardContent>
                <p class="text-sm text-amber-800 dark:text-amber-300">
                  Please check your User model configuration to ensure permission options are properly defined.
                </p>
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
                        v-model="formState.role" 
                        :class="formState.errors?.role ? 'border-destructive' : ''"
                        :disabled="!((isAdmin || isCurrentUserSuperAdmin) && !isEditingSelf)"
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
                                :variant="value === 'admin' || value === 'superadmin' ? 'destructive' : value === 'PIO Officer' ? 'default' : value === 'PIO Staff' ? 'secondary' : 'outline'"
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
                  <p v-if="formState.errors?.role" class="text-sm text-destructive">{{ formState.errors.role }}</p>
                  <p v-if="isEditingSelf" class="text-xs text-muted-foreground">
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
                        v-model="formState.office" 
                        :class="formState.errors?.office ? 'border-destructive' : ''"
                        :disabled="!((isAdmin || isCurrentUserSuperAdmin) && !isEditingSelf)"
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
                  <p v-if="formState.errors?.office" class="text-sm text-destructive">{{ formState.errors.office }}</p>
                  <p v-if="isEditingSelf" class="text-xs text-muted-foreground">
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
                        {{ formState.is_active ? 'User can login and access system' : 'User cannot login to system' }}
                      </p>
                    </div>
                    <Switch
                      v-model="formState.is_active"
                      :disabled="isEditingSelf"
                      aria-label="Toggle account status"
                    />
                  </div>
                  <p v-if="isEditingSelf" class="text-xs text-amber-600 dark:text-amber-400">
                    You cannot deactivate your own account
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
                    <span :class="user.email_verified_at ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400'">
                      {{ user.email_verified_at ? 'Yes' : 'No' }}
                    </span>
                  </div>
                  <div class="flex justify-between items-center p-2 bg-muted/30 rounded">
                    <span class="font-medium text-muted-foreground">Permissions:</span>
                    <span>
                      {{ formState.role === 'superadmin' ? 'All' : `${formState.permissions.length} enabled` }}
                    </span>
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
                    <span v-if="formState.processing">Saving...</span>
                    <span v-else>{{ isEditingSelf ? 'Update Profile' : 'Save Changes' }}</span>
                  </Button>
                  
                  <Button
                    type="button"
                    variant="outline"
                    @click="openCancelDialog"
                    :disabled="formState.processing"
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
                    :disabled="formState.processing"
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
                    :disabled="formState.processing"
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
            <div v-if="userSummary" class="mt-4 p-3 bg-muted rounded-lg space-y-2">
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
            <div class="mt-3 p-3 bg-amber-50 border border-amber-200 dark:bg-amber-950/30 dark:border-amber-800 rounded-lg">
              <p class="text-sm text-amber-800 dark:text-amber-200 font-medium">Security Notice:</p>
              <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
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