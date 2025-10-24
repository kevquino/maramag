<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Edit, Mail, Building, Calendar, CheckCircle, XCircle, Send, Trash2, CheckSquare, Phone, Clock, MapPin, Key, User, Shield } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
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
import { useFlashMessages } from '@/composables/useFlashMessages'

// Components
import UserAvatar from '@/components/UserAvatar.vue'
import UserStatusBadge from '@/components/UserStatusBadge.vue'
import UserRoleBadge from '@/components/UserRoleBadge.vue'

// Props
const props = defineProps<{
  user: {
    id: string;
    name: string;
    email: string;
    phone: string | null;
    role: string;
    office: string;
    position: string | null;
    avatar: string | null;
    is_active: boolean;
    email_verified_at: string | null;
    last_login_at: string | null;
    last_login_ip: string | null;
    login_count: number;
    permissions: string[];
    timezone: string;
    locale: string;
    two_factor_confirmed_at: string | null;
    created_at: string;
    updated_at: string;
  };
  permissionOptions: Record<string, any>;
}>();

const page = usePage();
useFlashMessages();

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
    title: 'User Details',
    href: `/user-management/${props.user.id}`,
  },
];

// Delete dialog state
const deleteDialogOpen = ref(false);
const deleting = ref(false);

// Resend verification state
const resendingVerification = ref(false);

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

// Format relative time
const formatRelativeTime = (dateString: string | null) => {
  if (!dateString) return 'Never';
  
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now.getTime() - date.getTime();
  const diffMins = Math.floor(diffMs / (1000 * 60));
  const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
  const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
  
  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins} minutes ago`;
  if (diffHours < 24) return `${diffHours} hours ago`;
  if (diffDays === 1) return 'Yesterday';
  if (diffDays < 7) return `${diffDays} days ago`;
  if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
  return formatDate(dateString);
};

// Check if current user can edit this profile
const canEdit = computed(() => {
  const authUser = page.props.auth.user as any;
  return authUser.role === 'admin' || 
    authUser.role === 'superadmin' || 
    authUser.id.toString() === props.user.id.toString();
});

// Check if current user is admin
const isAdmin = computed(() => {
  const authUser = page.props.auth.user as any;
  return authUser.role === 'admin' || authUser.role === 'superadmin';
});

// Check if user is superadmin
const isSuperAdmin = computed(() => {
  return props.user.role === 'superadmin';
});

// Handle edit
const handleEdit = () => {
  router.get(`/user-management/${props.user.id}/edit`);
};

// Handle back
const handleBack = () => {
  router.visit('/user-management');
};

// Resend email verification
const resendEmailVerification = () => {
  resendingVerification.value = true;
  
  router.post(`/user-management/${props.user.id}/resend-verification`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Success handled by flash messages
    },
    onError: (errors: any) => {
      console.error('Resend verification error:', errors);
    },
    onFinish: () => {
      resendingVerification.value = false;
    }
  });
};

// Delete user using Inertia
const deleteUser = async () => {
  deleting.value = true;
  try {
    router.delete(`/user-management/${props.user.id}`, {
      preserveScroll: false,
      onSuccess: () => {
        router.get('/user-management');
      },
      onError: (errors: any) => {
        console.error('Delete error:', errors);
        deleting.value = false;
        deleteDialogOpen.value = false;
      },
    });
  } catch (err) {
    console.error('Failed to delete user:', err);
    deleting.value = false;
    deleteDialogOpen.value = false;
  }
};

// Open delete confirmation dialog
const openDeleteDialog = () => {
  deleteDialogOpen.value = true;
};

// Check if current user is viewing their own profile
const isViewingSelf = computed(() => {
  return props.user.id === (page.props.auth.user as any).id;
});

// Check if permission is enabled (for superadmin or specific permissions)
const hasPermission = (permissionKey: string) => {
  // Dashboard permission is always enabled
  if (permissionKey === 'dashboard') return true;
  
  // Super admins have all permissions
  if (isSuperAdmin.value) return true;
  
  // Check if user has the specific permission
  return props.user.permissions?.includes(permissionKey) || false;
}

// Get enabled permissions count
const enabledPermissionsCount = computed(() => {
  if (isSuperAdmin.value) {
    return Object.keys(props.permissionOptions).length;
  }
  return props.user.permissions?.length || 0;
});
</script>

<template>
  <Head title="User Details" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full p-4 sm:p-6">
      <div class="w-full max-auto mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
          <div class="flex-1 min-w-0">
            <div class="flex items-center space-x-2">
              <Button
                variant="ghost"
                size="sm"
                @click="handleBack"
                class="h-8 w-8 p-0"
              >
                <ArrowLeft class="h-4 w-4" />
              </Button>
              <h1 class="text-2xl sm:text-3xl font-bold text-foreground truncate">User Details</h1>
            </div>
            <p class="text-muted-foreground mt-1">View complete user information and account details</p>
          </div>
          
          <!-- Edit Button in Header for Easy Access -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
            <Button
              @click="handleBack"
              variant="outline"
              class="w-full sm:w-auto"
            >
              <ArrowLeft class="h-4 w-4 mr-2" />
              <span class="hidden sm:inline">Back to List</span>
              <span class="sm:hidden">Back</span>
            </Button>
            <Button
              v-if="canEdit"
              @click="handleEdit"
              class="w-full sm:w-auto"
            >
              <Edit class="h-4 w-4 mr-2" />
              <span class="hidden sm:inline">Edit User</span>
              <span class="sm:hidden">Edit</span>
            </Button>
          </div>
        </div>

        <!-- Main Content Grid - Full Width -->
        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
          <!-- Left Column - User Profile & Information -->
          <div class="xl:col-span-3 space-y-6">
            <!-- Profile Card -->
            <div class="bg-card rounded-lg border shadow-sm">
              <div class="p-4 sm:p-6 border-b">
                <h2 class="text-lg font-semibold">Profile Information</h2>
              </div>
              <div class="p-4 sm:p-6">
                <div class="flex items-start space-x-4">
                  <!-- Avatar Component -->
                  <UserAvatar
                    :user="user"
                    size="xl"
                  />
                  <div class="flex-1 min-w-0">
                    <h3 class="text-2xl font-bold text-foreground truncate">{{ user.name }}</h3>
                    <p class="text-muted-foreground mt-1 text-lg">{{ user.email }}</p>
                    <div class="flex flex-wrap gap-2 mt-4">
                      <UserRoleBadge
                        :role="user.role"
                        size="sm"
                      />
                      <UserStatusBadge
                        :is-active="user.is_active"
                        size="sm"
                      />
                      <Badge :variant="user.email_verified_at ? 'default' : 'outline'" class="text-sm py-1">
                        <div class="flex items-center space-x-1">
                          <component 
                            :is="user.email_verified_at ? CheckCircle : XCircle" 
                            class="h-3 w-3" 
                          />
                          <span>{{ user.email_verified_at ? 'Verified' : 'Unverified' }}</span>
                        </div>
                      </Badge>
                      <Badge v-if="user.two_factor_confirmed_at" variant="default" class="text-sm py-1 bg-purple-100 text-purple-800 hover:bg-purple-200">
                        <div class="flex items-center space-x-1">
                          <Key class="h-3 w-3" />
                          <span>2FA Enabled</span>
                        </div>
                      </Badge>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Detailed Information Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Contact Information -->
              <div class="bg-card rounded-lg border shadow-sm">
                <div class="p-4 sm:p-6 border-b">
                  <h2 class="text-lg font-semibold">Contact Information</h2>
                </div>
                <div class="p-4 sm:p-6 space-y-4">
                  <div class="flex items-center space-x-3 p-3 bg-muted/30 rounded-lg">
                    <Mail class="h-5 w-5 text-muted-foreground flex-shrink-0" />
                    <div class="min-w-0 flex-1">
                      <p class="font-medium text-foreground truncate">{{ user.email }}</p>
                      <p class="text-sm text-muted-foreground">Email Address</p>
                    </div>
                  </div>
                  
                  <div v-if="user.phone" class="flex items-center space-x-3 p-3 bg-muted/30 rounded-lg">
                    <Phone class="h-5 w-5 text-muted-foreground flex-shrink-0" />
                    <div class="min-w-0 flex-1">
                      <p class="font-medium text-foreground">{{ user.phone }}</p>
                      <p class="text-sm text-muted-foreground">Phone Number</p>
                    </div>
                  </div>
                  
                  <!-- Email Verification Status -->
                  <div class="p-3 border rounded-lg" :class="user.email_verified_at ? 'border-green-200 bg-green-50' : 'border-amber-200 bg-amber-50'">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center space-x-2">
                        <component 
                          :is="user.email_verified_at ? CheckCircle : XCircle" 
                          class="h-4 w-4" 
                          :class="user.email_verified_at ? 'text-green-600' : 'text-amber-600'" 
                        />
                        <span class="text-sm font-medium" :class="user.email_verified_at ? 'text-green-800' : 'text-amber-800'">
                          {{ user.email_verified_at ? 'Email Verified' : 'Email Not Verified' }}
                        </span>
                      </div>
                      <Button
                        v-if="!user.email_verified_at && isAdmin"
                        @click="resendEmailVerification"
                        variant="outline"
                        size="sm"
                        :disabled="resendingVerification"
                        class="h-8 text-xs"
                      >
                        <Send class="h-3 w-3 mr-1" />
                        <span v-if="resendingVerification">Sending...</span>
                        <span v-else>Resend</span>
                      </Button>
                    </div>
                    <p class="text-xs mt-1" :class="user.email_verified_at ? 'text-green-600' : 'text-amber-600'">
                      {{ user.email_verified_at 
                        ? `Verified on ${formatDate(user.email_verified_at)}` 
                        : 'User has not verified their email address' 
                      }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Office Information -->
              <div class="bg-card rounded-lg border shadow-sm">
                <div class="p-4 sm:p-6 border-b">
                  <h2 class="text-lg font-semibold">Office Information</h2>
                </div>
                <div class="p-4 sm:p-6 space-y-4">
                  <div class="flex items-center space-x-3 p-3 bg-muted/30 rounded-lg">
                    <Building class="h-5 w-5 text-muted-foreground flex-shrink-0" />
                    <div class="min-w-0 flex-1">
                      <p class="font-medium text-foreground">{{ user.office }}</p>
                      <p class="text-sm text-muted-foreground">Assigned Office</p>
                    </div>
                  </div>
                  
                  <div v-if="user.position" class="flex items-center space-x-3 p-3 bg-muted/30 rounded-lg">
                    <User class="h-5 w-5 text-muted-foreground flex-shrink-0" />
                    <div class="min-w-0 flex-1">
                      <p class="font-medium text-foreground">{{ user.position }}</p>
                      <p class="text-sm text-muted-foreground">Position</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Permissions Card - UPDATED: Same card design as Create.vue but non-interactive -->
            <Card v-if="props.permissionOptions && Object.keys(props.permissionOptions).length > 0">
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <CheckSquare class="h-5 w-5 text-blue-600" />
                  Module Permissions
                  <Badge variant="outline" class="ml-2">
                    {{ enabledPermissionsCount }} enabled
                  </Badge>
                  <Badge v-if="isSuperAdmin" variant="destructive" class="ml-2">
                    Super Administrator
                  </Badge>
                </CardTitle>
                <CardDescription>
                  {{ isSuperAdmin ? 'Super administrators have full access to all system features and modules.' : 'Modules and pages this user can access' }}
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  <div
                    v-for="(permission, key) in props.permissionOptions"
                    :key="key"
                    class="flex items-start p-4 border-2 rounded-lg transition-all duration-200 select-none cursor-default"
                    :class="[
                      hasPermission(key) 
                        ? 'bg-green-50 border-green-500 dark:bg-green-950/50 dark:border-green-600 shadow-md' 
                        : 'bg-muted/30 border-muted',
                      key === 'dashboard' ? 'opacity-80' : ''
                    ]"
                  >
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center justify-between mb-2">
                        <Label 
                          class="text-sm font-semibold text-foreground cursor-default"
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

                <!-- Permission summary information -->
                <div class="p-3 bg-blue-50 border border-blue-200 dark:bg-blue-950/30 dark:border-blue-800 rounded-lg">
                  <div class="flex items-start space-x-2">
                    <CheckSquare class="h-4 w-4 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" />
                    <div class="flex-1">
                      <p class="text-sm text-blue-800 dark:text-blue-300 font-medium">Permission Summary</p>
                      <p class="text-xs text-blue-700 dark:text-blue-400 mt-1">
                        Green cards with checkmarks indicate enabled permissions. 
                        <span v-if="isSuperAdmin" class="font-semibold">Super administrators have all permissions automatically enabled.</span>
                        <span v-else class="font-semibold">Dashboard permission is always enabled for all users.</span>
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Debug information -->
                <div v-if="enabledPermissionsCount > 0" class="p-3 bg-muted border border-muted-foreground/20 rounded-lg">
                  <p class="text-sm font-medium">Current Permissions ({{ enabledPermissionsCount }}):</p>
                  <p class="text-xs text-muted-foreground mt-1">
                    <span v-if="isSuperAdmin">All permissions (Super Admin)</span>
                    <span v-else>{{ user.permissions?.join(', ') || 'No permissions' }}</span>
                  </p>
                </div>
              </CardContent>
            </Card>

            <!-- No Permissions Available Card -->
            <Card v-if="!props.permissionOptions || Object.keys(props.permissionOptions).length === 0" class="bg-amber-50 border-amber-200 dark:bg-amber-950/30 dark:border-amber-800">
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

          <!-- Right Column - Actions, Account Activity & System Information -->
          <div class="space-y-6">
            <!-- Account Activity -->
            <div class="bg-card rounded-lg border shadow-sm">
              <div class="p-4 sm:p-6 border-b">
                <h2 class="text-lg font-semibold">Account Activity</h2>
              </div>
              <div class="p-4 sm:p-6">
                <div class="space-y-4">
                  <div class="flex items-center space-x-2 text-sm">
                    <Clock class="h-4 w-4 text-muted-foreground" />
                    <span class="font-medium text-muted-foreground">Last Active:</span>
                    <span class="text-foreground">{{ formatRelativeTime(user.last_login_at) }}</span>
                  </div>
                  <div class="flex items-center space-x-2 text-sm">
                    <Calendar class="h-4 w-4 text-muted-foreground" />
                    <span class="font-medium text-muted-foreground">Last Login:</span>
                    <span class="text-foreground">{{ formatDate(user.last_login_at) }}</span>
                  </div>
                  <div v-if="user.last_login_ip" class="flex items-center space-x-2 text-sm">
                    <MapPin class="h-4 w-4 text-muted-foreground" />
                    <span class="font-medium text-muted-foreground">Last Login IP:</span>
                    <span class="font-mono text-xs bg-muted px-2 py-1 rounded">{{ user.last_login_ip }}</span>
                  </div>
                  <div class="flex items-center space-x-2 text-sm">
                    <User class="h-4 w-4 text-muted-foreground" />
                    <span class="font-medium text-muted-foreground">Login Count:</span>
                    <Badge variant="outline" class="text-xs">
                      {{ user.login_count || 0 }} times
                    </Badge>
                  </div>
                  <div class="flex items-center space-x-2 text-sm">
                    <Shield class="h-4 w-4 text-muted-foreground" />
                    <span class="font-medium text-muted-foreground">Account Status:</span>
                    <UserStatusBadge
                      :is-active="user.is_active"
                      size="sm"
                    />
                  </div>
                  <div class="flex items-center space-x-2 text-sm">
                    <User class="h-4 w-4 text-muted-foreground" />
                    <span class="font-medium text-muted-foreground">Member Since:</span>
                    <span class="text-foreground">{{ formatDate(user.created_at) }}</span>
                  </div>
                  <div class="flex items-center space-x-2 text-sm">
                    <Calendar class="h-4 w-4 text-muted-foreground" />
                    <span class="font-medium text-muted-foreground">Last Updated:</span>
                    <span class="text-foreground">{{ formatDate(user.updated_at) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- System Information -->
            <div class="bg-card rounded-lg border shadow-sm">
              <div class="p-4 sm:p-6 border-b">
                <h2 class="text-lg font-semibold">System Information</h2>
              </div>
              <div class="p-4 sm:p-6 space-y-4">
                <div class="grid gap-3 text-sm">
                  <div class="flex justify-between items-center py-2 border-b">
                    <span class="font-medium text-muted-foreground">Role Level:</span>
                    <UserRoleBadge
                      :role="user.role"
                      size="sm"
                    />
                  </div>
                  <div class="flex justify-between items-center py-2 border-b">
                    <span class="font-medium text-muted-foreground">Email Status:</span>
                    <Badge :variant="user.email_verified_at ? 'default' : 'outline'" class="text-xs">
                      {{ user.email_verified_at ? 'Verified' : 'Pending' }}
                    </Badge>
                  </div>
                  <div class="flex justify-between items-center py-2 border-b">
                    <span class="font-medium text-muted-foreground">Account Status:</span>
                    <UserStatusBadge
                      :is-active="user.is_active"
                      size="sm"
                    />
                  </div>
                  <div class="flex justify-between items-center py-2">
                    <span class="font-medium text-muted-foreground">2FA Status:</span>
                    <Badge :variant="user.two_factor_confirmed_at ? 'default' : 'outline'" class="text-xs">
                      <div class="flex items-center space-x-1">
                        <Key class="h-3 w-3" />
                        <span>{{ user.two_factor_confirmed_at ? 'Enabled' : 'Disabled' }}</span>
                      </div>
                    </Badge>
                  </div>
                </div>
              </div>
            </div>

            <!-- Delete User Card -->
            <div class="bg-card rounded-lg border shadow-sm border-destructive/50">
              <div class="p-4 sm:p-6">
                <div class="space-y-3">
                  <Button
                    v-if="isAdmin && !isViewingSelf"
                    type="button"
                    variant="destructive"
                    @click="openDeleteDialog"
                    class="w-full"
                  >
                    <Trash2 class="h-4 w-4 mr-2" />
                    Delete User
                  </Button>

                  <div v-if="isViewingSelf" class="text-xs text-muted-foreground text-center p-2 bg-muted/30 rounded">
                    You cannot delete your own account
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

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