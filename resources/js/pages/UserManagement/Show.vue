<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Edit, User, Mail, Shield, Building, Calendar, CheckCircle, XCircle, Send, Trash2, CheckSquare, Phone, Globe, Clock, MapPin, Monitor, Key } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
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
  // Allow if user is admin, superadmin, or viewing their own profile
  return authUser.role === 'admin' || 
         authUser.role === 'superadmin' || 
         authUser.id.toString() === props.user.id.toString();
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

// Get role badge variant
const getRoleBadgeVariant = (role: string) => {
  switch (role) {
    case 'superadmin': return 'destructive';
    case 'admin': return 'destructive';
    case 'PIO Officer': return 'default';
    case 'PIO Staff': return 'secondary';
    default: return 'outline';
  }
}

// Get status badge variant
const getStatusBadgeVariant = (isActive: boolean) => {
  return isActive ? 'default' : 'secondary';
}

// Avatar utility functions
const getInitials = (name: string): string => {
  if (!name) return '?';
  return name
    .split(' ')
    .map(part => part.charAt(0).toUpperCase())
    .slice(0, 2)
    .join('');
}

const getAvatarUrl = (avatar: string | null): string | undefined => {
  if (!avatar) return undefined;
  // If avatar is already a full URL, return it
  if (avatar.startsWith('http')) return avatar;
  // If avatar starts with /images/, return as is (for predefined avatars)
  if (avatar.startsWith('/images/')) return avatar;
  // Otherwise, assume it's a relative path from storage
  return `/storage/${avatar}`;
}

// Resend email verification
const resendEmailVerification = () => {
  resendingVerification.value = true;
  
  router.post(`/user-management/${props.user.id}/resend-verification`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Verification email sent successfully!');
    },
    onError: (errors) => {
      console.error('Resend verification error:', errors);
      let errorMsg = 'Failed to send verification email';
      if (typeof errors === 'string') {
        errorMsg = errors;
      } else if (errors && typeof errors === 'object' && 'message' in errors) {
        errorMsg = (errors as any).message;
      } else if (errors && typeof errors === 'object' && 'error' in errors) {
        errorMsg = (errors as any).error;
      }
      toast.error(errorMsg);
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
        toast.success('User deleted successfully!');
        router.get('/user-management');
      },
      onError: (errors) => {
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

// Open delete confirmation dialog
const openDeleteDialog = () => {
  deleteDialogOpen.value = true;
};

// Check if current user is viewing their own profile
const isViewingSelf = computed(() => {
  return props.user.id === (page.props.auth.user as any).id;
});

// Check if permission should be highlighted (for superadmin)
const shouldHighlightPermission = (permissionKey: string) => {
  if (isSuperAdmin.value) return true;
  return props.user.permissions?.includes(permissionKey) || false;
}

// Get timezone display name
const getTimezoneDisplay = (timezone: string) => {
  const timezones: Record<string, string> = {
    'UTC': 'Coordinated Universal Time (UTC)',
    'America/New_York': 'Eastern Time (ET)',
    'America/Chicago': 'Central Time (CT)',
    'America/Denver': 'Mountain Time (MT)',
    'America/Los_Angeles': 'Pacific Time (PT)',
    'Europe/London': 'Greenwich Mean Time (GMT)',
    'Europe/Paris': 'Central European Time (CET)',
    'Asia/Tokyo': 'Japan Standard Time (JST)',
    'Asia/Shanghai': 'China Standard Time (CST)',
  };
  return timezones[timezone] || timezone;
}

// Get locale display name
const getLocaleDisplay = (locale: string) => {
  const locales: Record<string, string> = {
    'en': 'English',
    'es': 'Spanish',
    'fr': 'French',
    'de': 'German',
    'ja': 'Japanese',
    'zh': 'Chinese',
    'tl': 'Filipino',
  };
  return locales[locale] || locale;
}
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
          <Button
            v-if="canEdit"
            @click="handleEdit"
            class="w-full sm:w-auto"
          >
            <Edit class="h-4 w-4 mr-2" />
            Edit User
          </Button>
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
                  <!-- Avatar -->
                  <div class="flex-shrink-0 relative">
                    <div v-if="getAvatarUrl(user.avatar)" class="w-20 h-20 rounded-full border-2 border-border shadow-sm overflow-hidden">
                      <img 
                        :src="getAvatarUrl(user.avatar)" 
                        :alt="user.name"
                        class="w-full h-full object-cover"
                      />
                    </div>
                    <div v-else class="w-20 h-20 rounded-full bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center text-white font-semibold text-2xl border-2 border-border shadow-sm">
                      {{ getInitials(user.name) }}
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <h3 class="text-2xl font-bold text-foreground truncate">{{ user.name }}</h3>
                    <p class="text-muted-foreground mt-1 text-lg">{{ user.email }}</p>
                    <div class="flex flex-wrap gap-2 mt-4">
                      <Badge :variant="getRoleBadgeVariant(user.role)" class="text-sm py-1">
                        {{ user.role }}
                      </Badge>
                      <Badge :variant="getStatusBadgeVariant(user.is_active)" class="text-sm py-1">
                        {{ user.is_active ? 'Active' : 'Inactive' }}
                      </Badge>
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

            <!-- Permissions Card -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <CheckSquare class="h-5 w-5 text-blue-600" />
                  Module Permissions
                  <Badge v-if="isSuperAdmin" variant="destructive" class="ml-2">
                    Super Administrator - Full Access
                  </Badge>
                </CardTitle>
                <CardDescription>
                  {{ isSuperAdmin ? 'Super administrators have access to all modules and system features.' : 'Modules and pages this user can access' }}
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                  <div
                    v-for="(permission, key) in permissionOptions"
                    :key="key"
                    class="flex items-center space-x-2 p-3 border rounded-lg transition-all duration-200"
                    :class="shouldHighlightPermission(key) 
                      ? 'bg-green-50 border-green-200 shadow-sm' 
                      : 'bg-muted/30 border-muted'"
                  >
                    <div 
                      class="h-3 w-3 rounded-full flex items-center justify-center transition-colors"
                      :class="shouldHighlightPermission(key) ? 'bg-green-500' : 'bg-gray-300'"
                    >
                      <CheckCircle 
                        v-if="shouldHighlightPermission(key)"
                        class="h-2 w-2 text-white" 
                      />
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium" :class="shouldHighlightPermission(key) ? 'text-green-800' : 'text-foreground'">
                        {{ permission.label }}
                      </p>
                      <p class="text-xs mt-1" :class="shouldHighlightPermission(key) ? 'text-green-600' : 'text-muted-foreground'">
                        {{ permission.description }}
                      </p>
                    </div>
                  </div>
                </div>
                <div v-if="!isSuperAdmin && (!user.permissions || user.permissions.length === 0)" class="text-center py-8 text-muted-foreground">
                  <CheckSquare class="h-12 w-12 mx-auto mb-2 opacity-50" />
                  <p>No permissions assigned</p>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Right Column - Actions, Account Activity & System Information -->
          <div class="space-y-6">
            <!-- Actions Card -->
            <div class="bg-card rounded-lg border shadow-sm">
              <div class="p-4 sm:p-6">
                <div class="space-y-3">
                  <Button
                    v-if="canEdit"
                    @click="handleEdit"
                    class="w-full"
                    size="lg"
                  >
                    <Edit class="h-4 w-4 mr-2" />
                    Edit User
                  </Button>
                  
                  <Button
                    @click="handleBack"
                    variant="outline"
                    class="w-full"
                  >
                    <ArrowLeft class="h-4 w-4 mr-2" />
                    Back to List
                  </Button>

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
                    <Badge :variant="getStatusBadgeVariant(user.is_active)" class="text-xs">
                      {{ user.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
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
                    <Badge :variant="getRoleBadgeVariant(user.role)" class="text-xs">
                      {{ user.role }}
                    </Badge>
                  </div>
                  <div class="flex justify-between items-center py-2 border-b">
                    <span class="font-medium text-muted-foreground">Email Status:</span>
                    <Badge :variant="user.email_verified_at ? 'default' : 'outline'" class="text-xs">
                      {{ user.email_verified_at ? 'Verified' : 'Pending' }}
                    </Badge>
                  </div>
                  <div class="flex justify-between items-center py-2 border-b">
                    <span class="font-medium text-muted-foreground">Account Status:</span>
                    <Badge :variant="getStatusBadgeVariant(user.is_active)" class="text-xs">
                      {{ user.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
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