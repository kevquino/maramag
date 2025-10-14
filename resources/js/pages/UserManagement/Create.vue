<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Save, User, Mail, Shield, Building, CheckSquare, Square } from 'lucide-vue-next';
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
  roleOptions: Record<string, string>;
  officeOptions: Record<string, string>;
  permissionOptions: Record<string, any>;
  permissionGroups: Record<string, any>;
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
const breadcrumbs: BreadcrumbItem[] = [
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
];

// Form handling
const form = useForm({
  name: '',
  email: '',
  role: '',
  office: '',
  is_active: true,
  password: '',
  password_confirmation: '',
  permissions: [] as string[],
});

// Dialog states
const saveDialogOpen = ref(false);
const cancelDialogOpen = ref(false);

// Permission selection
const selectAllPermissions = ref(false);

// Get ungrouped permissions
const getUngroupedPermissions = () => {
  const allPermissions = Object.keys(props.permissionOptions);
  const groupedPermissions = Object.values(props.permissionGroups).flatMap((group: any) => group.permissions);
  return allPermissions.filter(permission => !groupedPermissions.includes(permission));
};

// Toggle all permissions - FIXED: Properly handles the switch state
const toggleAllPermissions = (checked: boolean) => {
  selectAllPermissions.value = checked;
  
  if (checked) {
    // Add all permissions
    form.permissions = Object.keys(props.permissionOptions);
  } else {
    // Remove all permissions
    form.permissions = [];
  }
};

// Toggle single permission using switch
const togglePermission = (permission: string, checked: boolean) => {
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

// Update select all state based on current permissions - FIXED: Proper state calculation
const updateSelectAllState = () => {
  const allPermissions = Object.keys(props.permissionOptions);
  selectAllPermissions.value = form.permissions.length === allPermissions.length && allPermissions.length > 0;
};

// Watch permissions to update select all - FIXED: Use the proper update function
watch(() => form.permissions, () => {
  updateSelectAllState();
}, { deep: true });

// Check if form has unsaved changes
const hasUnsavedChanges = computed(() => {
  return !!form.name || !!form.email || !!form.role || !!form.office || !!form.password || form.permissions.length > 0;
});

// Get user summary for confirmation dialogs
const userSummary = computed(() => {
  return {
    name: form.name || 'Unnamed User',
    email: form.email || 'No email',
    role: props.roleOptions[form.role] || form.role || 'Not selected',
    office: props.officeOptions[form.office] || form.office || 'Not selected',
    status: form.is_active ? 'Active' : 'Inactive',
    permissions: form.permissions.length,
  };
});

// Handle form submission
const submit = () => {
  form.post('/user-management', {
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
  if (!form.role) {
    showToast('Please select a role.', 'error');
    return false;
  }
  if (!form.office) {
    showToast('Please select an office.', 'error');
    return false;
  }
  if (!form.password) {
    showToast('Please enter a password.', 'error');
    return false;
  }
  if (form.password.length < 8) {
    showToast('Password must be at least 8 characters long.', 'error');
    return false;
  }
  if (form.password !== form.password_confirmation) {
    showToast('Password confirmation does not match.', 'error');
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
  router.visit('/user-management');
};

// Clear shown messages when component unmounts
onMounted(() => {
  shownFlashMessages.value.clear();
  // Initialize select all state
  updateSelectAllState();
});
</script>

<template>
  <Head title="Create New User" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full p-4 sm:p-6">
      <div class="w-full max-w-none mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
          <div class="flex-1 min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-foreground truncate">Create New User</h1>
            <p class="text-muted-foreground mt-1">Add a new user to the system</p>
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
                </div>

                <!-- Password -->
                <div class="space-y-4">
                  <Label class="text-sm font-medium">Password *</Label>
                  
                  <div class="space-y-2">
                    <Label for="password" class="text-sm font-medium">Password</Label>
                    <Input
                      id="password"
                      v-model="form.password"
                      type="password"
                      placeholder="Enter password"
                      :class="form.errors.password ? 'border-destructive' : ''"
                      class="w-full"
                    />
                    <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
                    <p class="text-xs text-muted-foreground">
                      Password must be at least 8 characters long
                    </p>
                  </div>

                  <div class="space-y-2">
                    <Label for="password_confirmation" class="text-sm font-medium">Confirm Password</Label>
                    <Input
                      id="password_confirmation"
                      v-model="form.password_confirmation"
                      type="password"
                      placeholder="Confirm password"
                      :class="form.errors.password_confirmation ? 'border-destructive' : ''"
                      class="w-full"
                    />
                    <p v-if="form.errors.password_confirmation" class="text-sm text-destructive">{{ form.errors.password_confirmation }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Permissions Card -->
            <Card>
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
                  />
                </div>

                <!-- Permissions List - Single Box -->
                <div class="border rounded-lg">
                  <div class="p-4 bg-muted/30 border-b">
                    <h3 class="font-semibold text-sm">All Permissions</h3>
                    <p class="text-xs text-muted-foreground mt-1">
                      {{ Object.keys(permissionOptions).length }} total permissions • {{ form.permissions.length }} enabled
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
                          v-for="permissionKey in group.permissions"
                          :key="permissionKey"
                          class="flex flex-row items-center justify-between rounded-lg border p-4 transition-colors hover:bg-muted/30 cursor-pointer"
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
                          class="flex flex-row items-center justify-between rounded-lg border p-4 transition-colors hover:bg-muted/30 cursor-pointer"
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
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-if="form.errors.permissions" class="text-sm text-destructive">
                  {{ form.errors.permissions }}
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
                      <Select v-model="form.role" :class="form.errors.role ? 'border-destructive' : ''">
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
                </div>

                <!-- Office -->
                <div class="space-y-2">
                  <Label for="office" class="text-sm font-medium">Office *</Label>
                  <div class="flex items-center space-x-2">
                    <div class="flex-shrink-0">
                      <Building class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <div class="flex-1">
                      <Select v-model="form.office" :class="form.errors.office ? 'border-destructive' : ''">
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
                  <p v-if="form.errors.office" class="text-sm text-destructive">{{ form.errors.office }}</p>
                </div>

                <!-- Status Toggle -->
                <div class="space-y-2">
                  <Label class="text-sm font-medium">Account Status</Label>
                  <div class="flex items-center justify-between p-3 border rounded-lg bg-muted/50">
                    <div class="space-y-0.5">
                      <Label class="text-sm font-medium">Active Account</Label>
                      <p class="text-xs text-muted-foreground">
                        User can login and access system immediately
                      </p>
                    </div>
                    <Switch
                      v-model="form.is_active"
                      aria-label="Toggle account status"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Summary Card -->
            <Card>
              <CardHeader>
                <CardTitle class="text-sm">Summary</CardTitle>
              </CardHeader>
              <CardContent class="space-y-3 text-sm">
                <div class="flex justify-between">
                  <span class="text-muted-foreground">Selected Permissions:</span>
                  <span class="font-medium">{{ form.permissions.length }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-muted-foreground">Role:</span>
                  <Badge 
                    v-if="form.role"
                    :variant="form.role === 'admin' ? 'destructive' : form.role === 'PIO Officer' ? 'default' : form.role === 'PIO Staff' ? 'secondary' : 'outline'"
                    class="text-xs"
                  >
                    {{ roleOptions[form.role] || form.role }}
                  </Badge>
                  <span v-else class="text-muted-foreground text-xs">Not selected</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-muted-foreground">Office:</span>
                  <span class="font-medium text-right text-xs">{{ officeOptions[form.office] || form.office || 'Not selected' }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-muted-foreground">Status:</span>
                  <Badge :variant="form.is_active ? 'default' : 'secondary'" class="text-xs">
                    {{ form.is_active ? 'Active' : 'Inactive' }}
                  </Badge>
                </div>
              </CardContent>
            </Card>

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
                    <span v-if="form.processing">Creating...</span>
                    <span v-else>Create User</span>
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
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create User Confirmation Dialog -->
    <AlertDialog v-model:open="saveDialogOpen">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Create User?</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to create this new user account?
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
            Continue Editing
          </AlertDialogCancel>
          <AlertDialogAction @click="confirmCancel">
            Cancel Creation
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>