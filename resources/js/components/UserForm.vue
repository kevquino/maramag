<!-- components/UserForm.vue -->
<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { User, Mail, Shield, Building, Phone, Key, CheckSquare } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface Props {
  user?: {
    id: string;
    name: string;
    email: string;
    phone: string;
    role: string;
    office: string;
    position: string;
    avatar: string;
    is_active: boolean;
    permissions: string[];
  };
  roleOptions: Record<string, string>;
  officeOptions: Record<string, string>;
  permissionOptions: Record<string, any>;
  permissionGroups: Record<string, any>;
  mode: 'create' | 'edit';
}

const props = defineProps<Props>();
const page = usePage();

// Form handling
const form = useForm({
  _method: props.mode === 'edit' ? 'PUT' : 'POST',
  name: props.user?.name || '',
  email: props.user?.email || '',
  phone: props.user?.phone || '',
  role: props.user?.role || '',
  office: props.user?.office || '',
  position: props.user?.position || '',
  is_active: props.user?.is_active ?? true,
  password: '',
  password_confirmation: '',
  permissions: props.user?.permissions || [] as string[],
  avatar: props.user?.avatar || '',
});

// Permission selection
const selectAllPermissions = ref(false);

// Computed properties
const allPermissions = computed(() => Object.keys(props.permissionOptions));
const isSuperAdmin = computed(() => form.role === 'superadmin');
const isEditingSelf = computed(() => props.user?.id === (page.props.auth.user as any).id);
const isAdmin = computed(() => {
  const authUser = page.props.auth.user as any;
  return authUser.role === 'admin' || authUser.role === 'superadmin';
});
const canEditPermissions = computed(() => isAdmin.value && !isEditingSelf.value);
const canEditRoleOffice = computed(() => isAdmin.value && !isEditingSelf.value);

// Methods
const toggleAllPermissions = (checked: boolean) => {
  if (!canEditPermissions.value || isSuperAdmin.value) return;
  
  selectAllPermissions.value = checked;
  form.permissions = checked ? [...allPermissions.value] : ['dashboard'];
};

const togglePermission = (permission: string, checked: boolean) => {
  if (!canEditPermissions.value || isSuperAdmin.value || permission === 'dashboard') return;
  
  if (checked && !form.permissions.includes(permission)) {
    form.permissions.push(permission);
  } else {
    const index = form.permissions.indexOf(permission);
    if (index > -1) form.permissions.splice(index, 1);
  }
};

const isPermissionEnabled = (permission: string) => {
  if (isSuperAdmin.value) return true;
  if (permission === 'dashboard') return true;
  return form.permissions.includes(permission);
};

const updateSelectAllState = () => {
  if (isSuperAdmin.value) {
    selectAllPermissions.value = true;
  } else {
    const editablePermissions = allPermissions.value.filter(p => p !== 'dashboard');
    selectAllPermissions.value = editablePermissions.every(p => form.permissions.includes(p));
  }
};

// Watchers
watch(() => form.role, (newRole) => {
  if (newRole === 'superadmin') {
    form.permissions = [...allPermissions.value];
    selectAllPermissions.value = true;
  }
});

watch(() => form.permissions, updateSelectAllState, { deep: true, immediate: true });

// Expose form and methods to parent
defineExpose({
  form,
  validateForm: () => {
    if (!form.name.trim()) return 'Please enter a name for the user.';
    if (!form.email.trim()) return 'Please enter an email address.';
    if (!form.role && canEditRoleOffice.value) return 'Please select a role.';
    if (!form.office && canEditRoleOffice.value) return 'Please select an office.';
    if (form.password && form.password.length < 8) return 'Password must be at least 8 characters long.';
    if (form.password !== form.password_confirmation) return 'Password confirmation does not match.';
    return null;
  }
});
</script>

<template>
  <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <!-- Left Column -->
    <div class="xl:col-span-2 space-y-6">
      <!-- Basic Information -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <User class="h-5 w-5 text-blue-600" />
            Basic Information
          </CardTitle>
          <CardDescription>
            {{ mode === 'create' ? 'Enter user personal and contact information' : 'Update user personal and contact information' }}
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
          <!-- Name and Email -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
          </div>

          <!-- Phone and Position -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
              {{ mode === 'create' ? 'Set Password' : 'Change Password' }}
            </Label>
            <p class="text-sm text-muted-foreground" v-if="mode === 'edit'">
              Leave blank to keep current password
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label for="password" class="text-sm font-medium">
                  {{ mode === 'create' ? 'Password *' : 'New Password' }}
                </Label>
                <Input
                  id="password"
                  v-model="form.password"
                  type="password"
                  :placeholder="mode === 'create' ? 'Enter password' : 'Enter new password'"
                  :class="form.errors.password ? 'border-destructive' : ''"
                  class="w-full"
                />
                <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
              </div>

              <div class="space-y-2">
                <Label for="password_confirmation" class="text-sm font-medium">
                  {{ mode === 'create' ? 'Confirm Password *' : 'Confirm New Password' }}
                </Label>
                <Input
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  type="password"
                  :placeholder="mode === 'create' ? 'Confirm password' : 'Confirm new password'"
                  :class="form.errors.password_confirmation ? 'border-destructive' : ''"
                  class="w-full"
                />
                <p v-if="form.errors.password_confirmation" class="text-sm text-destructive">{{ form.errors.password_confirmation }}</p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Permissions Section -->
      <Card v-if="isAdmin">
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

          <!-- Permissions Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div
              v-for="permission in allPermissions"
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

          <div v-if="form.errors.permissions" class="text-sm text-destructive">
            {{ form.errors.permissions }}
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Right Column -->
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
              <Shield class="h-4 w-4 text-muted-foreground flex-shrink-0" />
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
            <p v-if="form.errors.role" class="text-sm text-destructive">{{ form.errors.role }}</p>
          </div>

          <!-- Office -->
          <div class="space-y-2">
            <Label for="office" class="text-sm font-medium">Office *</Label>
            <div class="flex items-center space-x-2">
              <Building class="h-4 w-4 text-muted-foreground flex-shrink-0" />
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
                :disabled="isEditingSelf"
                aria-label="Toggle account status"
              />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>