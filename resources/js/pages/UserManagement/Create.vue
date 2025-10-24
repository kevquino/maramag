<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Save, User, Mail, Shield, Building, CheckSquare, Phone, Key, Info, X, RotateCcw } from 'lucide-vue-next';
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

// Composables
import { useFlashMessages } from '@/composables/useFlashMessages';

// Props
const props = defineProps<{
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
    title: 'Create User',
    href: '/user-management/create',
  },
]);

// Form state
const formState = ref({
  name: '',
  email: '',
  phone: '',
  role: '',
  office: '',
  position: '',
  is_active: true,
  password: '',
  password_confirmation: '',
  permissions: ['dashboard'], // Default permissions - always include dashboard
  processing: false,
  errors: {} as Record<string, string>,
});

// Debug: Log permissions data
onMounted(() => {
  console.log('Permission options:', props.permissionOptions);
  console.log('Permission groups:', props.permissionGroups);
  console.log('Can edit permissions:', props.canEditPermissions);
});

// Dialog states
const saveDialogOpen = ref(false);
const cancelDialogOpen = ref(false);

// Permission management
const togglePermission = (permission: string) => {
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
  
  return form.name !== '' ||
         form.email !== '' ||
         form.phone !== '' ||
         form.role !== '' ||
         form.office !== '' ||
         form.position !== '' ||
         form.password !== '' ||
         form.password_confirmation !== '' ||
         form.permissions.length > 1; // More than just dashboard
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
    role: props.roleOptions[form.role] || form.role || 'Not selected',
    office: props.officeOptions[form.office] || form.office || 'Not selected',
    status: form.is_active ? 'Active' : 'Inactive',
    hasPassword: !!form.password,
    permissions: isSuperAdmin ? 'All (Superadmin)' : `${form.permissions.length} enabled`,
  };
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

// Handle form submission
const submit = () => {
  formState.value.processing = true;

  // Prepare form data
  const formData = {
    name: formState.value.name,
    email: formState.value.email,
    phone: formState.value.phone,
    role: formState.value.role,
    office: formState.value.office,
    position: formState.value.position,
    is_active: formState.value.is_active,
    password: formState.value.password,
    password_confirmation: formState.value.password_confirmation,
    permissions: formState.value.permissions,
  };

  console.log('Submitting form data:', formData); // Debug log

  router.post('/user-management', formData, {
    preserveScroll: true,
    onSuccess: () => {
      formState.value.processing = false;
      // Success handled by flash messages - controller will redirect to index
    },
    onError: (errors: any) => {
      formState.value.processing = false;
      formState.value.errors = errors;
      console.error('Form submission errors:', errors); // Debug log
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
  if (!formState.value.role?.trim()) {
    toast.error('Role is required');
    return;
  }
  if (!formState.value.office?.trim()) {
    toast.error('Office is required');
    return;
  }
  if (!formState.value.password?.trim()) {
    toast.error('Password is required');
    return;
  }
  if (formState.value.password !== formState.value.password_confirmation) {
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
</script>

<template>
  <Head title="Create User" />

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
              <h1 class="text-2xl sm:text-3xl font-bold text-foreground truncate">Create New User</h1>
            </div>
            <p class="text-muted-foreground mt-1">Create a new user account with specific permissions</p>
            <div v-if="isCurrentUserSuperAdmin" class="mt-2">
              <Badge variant="outline" class="bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950 dark:text-purple-300 dark:border-purple-800">
                Super Admin Creating User
              </Badge>
            </div>
            <div v-else-if="isAdmin" class="mt-2">
              <Badge variant="outline" class="bg-green-50 text-green-700 border-green-200 dark:bg-green-950 dark:text-green-300 dark:border-green-800">
                Admin Creating User
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
                  Enter user's personal and contact information
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-6">
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
                    Set Password
                  </Label>
                  <p class="text-sm text-muted-foreground">
                    Create a secure password for the user
                  </p>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                      <Label for="password" class="text-sm font-medium">Password *</Label>
                      <Input
                        id="password"
                        v-model="formState.password"
                        type="password"
                        placeholder="Enter password"
                        :class="formState.errors?.password ? 'border-destructive' : ''"
                        class="w-full"
                      />
                      <p v-if="formState.errors?.password" class="text-sm text-destructive">{{ formState.errors.password }}</p>
                    </div>

                    <div class="space-y-2">
                      <Label for="password_confirmation" class="text-sm font-medium">Confirm Password *</Label>
                      <Input
                        id="password_confirmation"
                        v-model="formState.password_confirmation"
                        type="password"
                        placeholder="Confirm password"
                        :class="formState.errors?.password_confirmation ? 'border-destructive' : ''"
                        class="w-full"
                      />
                      <p v-if="formState.errors?.password_confirmation" class="text-sm text-destructive">{{ formState.errors.password_confirmation }}</p>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Permissions Card - EXACTLY SAME AS EDIT.VUE -->
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
                  Click on permission cards to enable or disable access
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
                      key !== 'dashboard' ? 'hover:border-green-300 dark:hover:border-green-700' : '',
                      key === 'dashboard' ? 'cursor-not-allowed opacity-80' : ''
                    ]"
                    @click="key !== 'dashboard' && togglePermission(key)"
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

                <!-- Quick Actions -->
                <div class="flex flex-wrap gap-2 pt-4 border-t">
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
                    @click="formState.permissions = ['dashboard']"
                    class="text-xs"
                  >
                    <RotateCcw class="h-3 w-3 mr-1" />
                    Reset to Default
                  </Button>
                </div>

                <!-- Permission editing instructions -->
                <div class="p-3 bg-blue-50 border border-blue-200 dark:bg-blue-950/30 dark:border-blue-800 rounded-lg">
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
                  This user will have full access to all system features and modules
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div class="flex items-center space-x-3 p-3 bg-white/50 dark:bg-gray-900/50 rounded-lg border border-purple-200 dark:border-purple-800">
                  <CheckSquare class="h-5 w-5 text-purple-600 dark:text-purple-400" />
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
                      >
                        <SelectTrigger class="w-full">
                          <SelectValue placeholder="Select a role" />
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
                      >
                        <SelectTrigger class="w-full">
                          <SelectValue placeholder="Select an office" />
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
                </div>

                <!-- Status Toggle -->
                <div class="space-y-2">
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
                      aria-label="Toggle account status"
                    />
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Actions Card - MOVED BELOW ACCOUNT SETTINGS -->
            <Card>
              <CardHeader>
                <CardTitle>Actions</CardTitle>
                <CardDescription>
                  Create user account or cancel
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
                    <span v-if="formState.processing">Creating...</span>
                    <span v-else>Create User</span>
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
                </div>
              </CardContent>
            </Card>

            <!-- User Summary Card -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <User class="h-5 w-5 text-blue-600" />
                  User Summary
                </CardTitle>
                <CardDescription>
                  Preview of the user account being created
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-1 gap-3 text-sm">
                  <div class="flex justify-between items-center p-2 bg-muted/30 rounded">
                    <span class="font-medium text-muted-foreground">Name:</span>
                    <span>{{ formState.name || 'Not set' }}</span>
                  </div>
                  <div class="flex justify-between items-center p-2 bg-muted/30 rounded">
                    <span class="font-medium text-muted-foreground">Email:</span>
                    <span>{{ formState.email || 'Not set' }}</span>
                  </div>
                  <div class="flex justify-between items-center p-2 bg-muted/30 rounded">
                    <span class="font-medium text-muted-foreground">Role:</span>
                    <span>{{ roleOptions[formState.role] || 'Not selected' }}</span>
                  </div>
                  <div class="flex justify-between items-center p-2 bg-muted/30 rounded">
                    <span class="font-medium text-muted-foreground">Office:</span>
                    <span>{{ officeOptions[formState.office] || 'Not selected' }}</span>
                  </div>
                  <div class="flex justify-between items-center p-2 bg-muted/30 rounded">
                    <span class="font-medium text-muted-foreground">Status:</span>
                    <span :class="formState.is_active ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400'">
                      {{ formState.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                  <div class="flex justify-between items-center p-2 bg-muted/30 rounded">
                    <span class="font-medium text-muted-foreground">Password:</span>
                    <span :class="formState.password ? 'text-green-600 dark:text-green-400' : 'text-destructive'">
                      {{ formState.password ? 'Set' : 'Not set' }}
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
          </div>
        </div>
      </div>
    </div>

    <!-- Save Changes Confirmation Dialog -->
    <AlertDialog v-model:open="saveDialogOpen">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Create User?</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to create this user account?
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
                <span>{{ userSummary.hasPassword ? 'Set' : 'Not set' }}</span>
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
            Create User
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- Cancel Confirmation Dialog -->
    <AlertDialog v-model:open="cancelDialogOpen">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Cancel Creation?</AlertDialogTitle>
          <AlertDialogDescription>
            <span v-if="hasUnsavedChanges">
              You have unsaved changes. If you cancel now, all your changes will be lost.
            </span>
            <span v-else>
              This will cancel the user creation and return you to the user management list.
            </span>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="cancelDialogOpen = false">
            Continue Creating
          </AlertDialogCancel>
          <AlertDialogAction @click="confirmCancel">
            Cancel Creation
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>