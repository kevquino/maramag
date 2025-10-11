<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { router, Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { ArrowLeft } from 'lucide-vue-next';

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

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'user',
  is_active: true,
});

const submit = () => {
  form.post('/user-management', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
    },
  });
};
</script>

<template>
  <Head title="Create User" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="text-center sm:text-left">
          <div class="flex items-center gap-2">
            <Button variant="ghost" size="sm" @click="router.visit('/user-management')" class="p-0 h-8 w-8">
              <ArrowLeft class="h-4 w-4" />
            </Button>
            <h1 class="text-3xl font-bold text-foreground">Create New User</h1>
          </div>
          <p class="text-muted-foreground mt-2">Add a new user to the system</p>
        </div>
      </div>

      <!-- Form Section -->
      <div class="max-w-2xl mx-auto w-full">
        <div class="bg-card rounded-lg border p-6 shadow-sm">
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Personal Information -->
            <div class="space-y-4">
              <h3 class="text-lg font-semibold">Personal Information</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label for="name">Full Name</Label>
                  <Input
                    id="name"
                    v-model="form.name"
                    type="text"
                    placeholder="Enter full name"
                    :class="{ 'border-destructive': form.errors.name }"
                  />
                  <p v-if="form.errors.name" class="text-destructive text-sm">{{ form.errors.name }}</p>
                </div>
                
                <div class="space-y-2">
                  <Label for="email">Email Address</Label>
                  <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    placeholder="Enter email address"
                    :class="{ 'border-destructive': form.errors.email }"
                  />
                  <p v-if="form.errors.email" class="text-destructive text-sm">{{ form.errors.email }}</p>
                </div>
              </div>
            </div>

            <!-- Password -->
            <div class="space-y-4">
              <h3 class="text-lg font-semibold">Password</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label for="password">Password</Label>
                  <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    placeholder="Enter password"
                    :class="{ 'border-destructive': form.errors.password }"
                  />
                  <p v-if="form.errors.password" class="text-destructive text-sm">{{ form.errors.password }}</p>
                </div>
                
                <div class="space-y-2">
                  <Label for="password_confirmation">Confirm Password</Label>
                  <Input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    placeholder="Confirm password"
                  />
                </div>
              </div>
            </div>

            <!-- Role & Status -->
            <div class="space-y-4">
              <h3 class="text-lg font-semibold">Role & Status</h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label for="role">User Role</Label>
                  <Select v-model="form.role">
                    <SelectTrigger :class="{ 'border-destructive': form.errors.role }">
                      <SelectValue placeholder="Select a role" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="user">Regular User</SelectItem>
                      <SelectItem value="PIO Staff">PIO Staff</SelectItem>
                      <SelectItem value="PIO Officer">PIO Officer</SelectItem>
                      <SelectItem value="admin">Administrator</SelectItem>
                    </SelectContent>
                  </Select>
                  <p v-if="form.errors.role" class="text-destructive text-sm">{{ form.errors.role }}</p>
                </div>
                
                <div class="flex items-center space-x-2 pt-8">
                  <Checkbox
                    id="is_active"
                    v-model:checked="form.is_active"
                  />
                  <Label for="is_active" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                    Active User
                  </Label>
                </div>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="flex gap-4 pt-6">
              <Button
                type="submit"
                :disabled="form.processing"
                class="flex-1 sm:flex-none"
              >
                <span v-if="form.processing">Creating...</span>
                <span v-else>Create User</span>
              </Button>
              
              <Button
                type="button"
                variant="outline"
                @click="router.visit('/user-management')"
                class="flex-1 sm:flex-none"
              >
                Cancel
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>