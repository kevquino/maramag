<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem, type BadgeCounts, type User } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// Import role-specific dashboard components
import SuperAdminDashboard from './dashboard-components/SuperAdminDashboard.vue';
import AdminDashboard from './dashboard-components/AdminDashboard.vue';
import StaffDashboard from './dashboard-components/StaffDashboard.vue';
import UserDashboard from './dashboard-components/UserDashboard.vue';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
];

const page = usePage();
const currentUser = computed(() => page.props.auth?.user as User);
const userRole = computed(() => currentUser.value?.role);
const badgeCounts = computed(() => page.props.badgeCounts as BadgeCounts);

// Component mapping based on role
const roleComponents = {
  superadmin: SuperAdminDashboard,
  admin: AdminDashboard,
  staff: StaffDashboard,
  user: UserDashboard,
};

const CurrentRoleComponent = computed(() => 
  roleComponents[userRole.value as keyof typeof roleComponents] || UserDashboard
);
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <component 
      :is="CurrentRoleComponent" 
      :user="currentUser"
      :badge-counts="badgeCounts"
    />
  </AppLayout>
</template>