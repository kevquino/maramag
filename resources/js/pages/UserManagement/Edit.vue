<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Save, User, Mail, Shield, Building, Eye, Trash2 } from 'lucide-vue-next';
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
  };
  roleOptions: Record<string, string>;
  officeOptions: Record<string, string>;
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
    title: 'Edit User',
    href: `/user-management/${props.user.id}/edit`,
  },
];

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
});

// Dialog states
const saveDialogOpen = ref(false);
const cancelDialogOpen = ref(false);
const deleteDialogOpen = ref(false);
const deleting = ref(false);

// Check if form has unsaved changes
const hasUnsavedChanges = computed(() => {
  return form.name !== props.user.name ||
         form.email !== props.user.email ||
         form.role !== props.user.role ||
         form.office !== props.user.office ||
         form.is_active !== props.user.is_active ||
         form.password !== '' ||
         form.password_confirmation !== '';
});

// Get user summary for confirmation dialogs
const userSummary = computed(() => {
  return {
    name: form.name || 'Unnamed User',
    email: form.email || 'No email',
    role: props.roleOptions[form.role] || form.role,
    office: props.officeOptions[form.office] || form.office,
    status: form.is_active ? 'Active' : 'Inactive',
    hasPassword: !!form.password
  };
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
  if (!form.role) {
    showToast('Please select a role.', 'error');
    return false;
  }
  if (!form.office) {
    showToast('Please select an office.', 'error');
    return false;
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

// Open delete confirmation dialog
const openDeleteDialog = () => {
  deleteDialogOpen.value = true;
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

// Check if current user is editing their own profile
const isEditingSelf = computed(() => {
  return props.user.id === (page.props.auth.user as any).id;
});

// Clear shown messages when component unmounts
onMounted(() => {
  shownFlashMessages.value.clear();
});
</script>

<template>
  <Head title="Edit User" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full p-4 sm:p-6">
      <div class="w-full max-w-none mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
          <div class="flex-1 min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-foreground truncate">Edit User</h1>
            <p class="text-muted-foreground mt-1">Update user details and permissions</p>
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
                </div>

                <!-- Status Toggle -->
                <div class="space-y-2">
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
                      :disabled="isEditingSelf"
                      aria-label="Toggle account status"
                    />
                  </div>
                  <p v-if="isEditingSelf" class="text-xs text-amber-600">
                    You cannot deactivate your own account
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
                    <span v-else>Save Changes</span>
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

                  <Button
                    type="button"
                    variant="destructive"
                    @click="openDeleteDialog"
                    :disabled="form.processing || isEditingSelf"
                    class="w-full"
                  >
                    <Trash2 class="h-4 w-4 mr-2" />
                    Delete User
                  </Button>

                  <div v-if="isEditingSelf" class="text-xs text-muted-foreground text-center">
                    You cannot delete your own account
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
          <AlertDialogTitle>Save Changes?</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to save the changes to this user account?
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
            </div>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="saveDialogOpen = false">
            Continue Editing
          </AlertDialogCancel>
          <AlertDialogAction @click="confirmSave">
            Save Changes
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
  </AppLayout>
</template>

<style scoped>
.resize-vertical {
  resize: vertical;
}

/* Custom scrollbar for textareas */
textarea::-webkit-scrollbar {
  width: 6px;
}

textarea::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

textarea::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

textarea::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  textarea::-webkit-scrollbar-track {
    background: #374151;
  }

  textarea::-webkit-scrollbar-thumb {
    background: #6b7280;
  }

  textarea::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
  }
}
</style>