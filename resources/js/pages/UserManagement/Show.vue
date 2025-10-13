<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Edit, User, Mail, Shield, Building, Calendar, CheckCircle, XCircle, Send, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
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
    last_login_at: string | null;
    created_at: string;
    updated_at: string;
  };
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

// Check if current user can edit this profile
const canEdit = computed(() => {
  const authUser = page.props.auth.user as any;
  return authUser.role === 'admin' || authUser.id.toString() === props.user.id.toString();
});

// Check if current user is admin
const isAdmin = computed(() => {
  const authUser = page.props.auth.user as any;
  return authUser.role === 'admin';
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
    case 'admin': return 'destructive'
    case 'PIO Officer': return 'default'
    case 'PIO Staff': return 'secondary'
    default: return 'outline'
  }
}

// Get status badge variant
const getStatusBadgeVariant = (isActive: boolean) => {
  return isActive ? 'default' : 'secondary'
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
                  <div class="flex-shrink-0 w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center">
                    <User class="h-10 w-10 text-primary" />
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
                <div class="p-4 sm:p-6">
                  <div class="flex items-center space-x-3 p-3 bg-muted/30 rounded-lg">
                    <Building class="h-5 w-5 text-muted-foreground flex-shrink-0" />
                    <div class="min-w-0 flex-1">
                      <p class="font-medium text-foreground">{{ user.office }}</p>
                      <p class="text-sm text-muted-foreground">Assigned Office</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Account Activity & System Information -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Account Activity -->
              <div class="bg-card rounded-lg border shadow-sm">
                <div class="p-4 sm:p-6 border-b">
                  <h2 class="text-lg font-semibold">Account Activity</h2>
                </div>
                <div class="p-4 sm:p-6">
                  <div class="space-y-4">
                    <div class="flex items-center space-x-2 text-sm">
                      <Calendar class="h-4 w-4 text-muted-foreground" />
                      <span class="font-medium text-muted-foreground">Last Login:</span>
                      <span class="text-foreground">{{ formatDate(user.last_login_at) }}</span>
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
                      <span class="font-medium text-muted-foreground">User ID:</span>
                      <span class="font-mono text-xs bg-muted px-2 py-1 rounded">{{ user.id }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b">
                      <span class="font-medium text-muted-foreground">Database ID:</span>
                      <span class="font-mono text-xs text-muted-foreground">#{{ user.id }}</span>
                    </div>
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
                    <div class="flex justify-between items-center py-2">
                      <span class="font-medium text-muted-foreground">Account Status:</span>
                      <Badge :variant="getStatusBadgeVariant(user.is_active)" class="text-xs">
                        {{ user.is_active ? 'Active' : 'Inactive' }}
                      </Badge>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column - Actions & Quick Stats -->
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

            <!-- Quick Stats -->
            <div class="bg-card rounded-lg border shadow-sm">
              <div class="p-4 sm:p-6 border-b">
                <h2 class="text-lg font-semibold">Quick Stats</h2>
              </div>
              <div class="p-4 sm:p-6">
                <div class="space-y-3">
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-muted-foreground">Account Age</span>
                    <span class="text-sm font-medium">
                      {{ new Date().getFullYear() - new Date(user.created_at).getFullYear() }} years
                    </span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-muted-foreground">Last Activity</span>
                    <span class="text-sm font-medium">
                      {{ user.last_login_at ? 'Recently' : 'Never' }}
                    </span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-muted-foreground">Verification</span>
                    <span class="text-sm font-medium" :class="user.email_verified_at ? 'text-green-600' : 'text-amber-600'">
                      {{ user.email_verified_at ? 'Complete' : 'Pending' }}
                    </span>
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

<style scoped>
/* Custom styling for better visual hierarchy */
.bg-muted\/30 {
  background-color: hsl(var(--muted) / 0.3);
}

.bg-green-50 {
  background-color: hsl(142, 76%, 96%);
}

.border-green-200 {
  border-color: hsl(142, 76%, 86%);
}

.bg-amber-50 {
  background-color: hsl(38, 96%, 96%);
}

.border-amber-200 {
  border-color: hsl(38, 96%, 86%);
}
</style>