<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type User } from '@/types';
import { ref, computed } from 'vue';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    userData?: User;
    formOptions?: {
        roles: Record<string, string>;
        offices: Record<string, string>;
        positions: Record<string, string>;
    };
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: edit().url,
    },
];

const page = usePage();
const user = props.userData || page.props.auth.user;

// Use provided form options or fallback to defaults
const roles = props.formOptions?.roles || {
    superadmin: 'Super Administrator',
    admin: 'Office Administrator', 
    staff: 'Office Staff',
};

const offices = props.formOptions?.offices || {
    "Mayor's Office": "Mayor's Office",
    "Vice Mayor's Office": "Vice Mayor's Office",
    "Sangguniang Bayan": "Sangguniang Bayan",
    "Municipal Planning and Development Office": "Municipal Planning and Development Office",
    "Municipal Accounting Office": "Municipal Accounting Office",
    "Municipal Treasurer's Office": "Municipal Treasurer's Office",
    "Municipal Budget Office": "Municipal Budget Office",
    "Municipal Assessor's Office": "Municipal Assessor's Office",
    "Municipal Engineer's Office": "Municipal Engineer's Office",
    "Municipal Health Office": "Municipal Health Office",
    "Municipal Agriculture Office": "Municipal Agriculture Office",
    "Municipal Social Welfare and Development Office": "Municipal Social Welfare and Development Office",
    "Public Information Office": "Public Information Office",
    "Municipal Disaster Risk Reduction and Management Office": "Municipal Disaster Risk Reduction and Management Office",
    "Bids and Awards Committee": "Bids and Awards Committee",
    "Tourism Office": "Tourism Office",
    "Business Permit and Licensing Office": "Business Permit and Licensing Office",
    "Other": "Other",
};

const positions = props.formOptions?.positions || {
    'Department Head': 'Department Head',
    'Assistant Department Head': 'Assistant Department Head',
    'Division Chief': 'Division Chief',
    'Administrative Officer': 'Administrative Officer',
    'Information Officer': 'Information Officer',
    'Planning Officer': 'Planning Officer',
    'Budget Officer': 'Budget Officer',
    'Accountant': 'Accountant',
    'Treasurer': 'Treasurer',
    'Assessor': 'Assessor',
    'Engineer': 'Engineer',
    'Health Officer': 'Health Officer',
    'Agriculturalist': 'Agriculturalist',
    'Social Welfare Officer': 'Social Welfare Officer',
    'Disaster Risk Officer': 'Disaster Risk Officer',
    'Tourism Officer': 'Tourism Officer',
    'Permit Officer': 'Permit Officer',
    'Staff': 'Staff',
    'Other': 'Other',
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

const lastUpdate = formatLastUpdate(user.updated_at);

// Get display value for select fields
const getOfficeDisplayValue = () => {
    return user.office || 'Select office';
};

const getPositionDisplayValue = () => {
    return user.position || 'Select position';
};

// Generate avatar from name
const generateAvatarFromName = (name: string) => {
    if (!name) return '';
    const names = name.split(' ');
    let initials = names[0].charAt(0).toUpperCase();
    if (names.length > 1) {
        initials += names[names.length - 1].charAt(0).toUpperCase();
    }
    return initials;
};

const avatarInitials = generateAvatarFromName(user.name);

// Avatar functionality - use empty string for initials, image paths for avatars
const selectedAvatar = ref(user.avatar || '');

// Avatar options - empty string represents initials
const avatarOptions = [
    // Initials option (empty string)
    { type: 'initials', display: avatarInitials, value: '' },
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

// Select avatar
const selectAvatar = (avatarValue: string) => {
    selectedAvatar.value = avatarValue;
};

// Computed properties for avatar display
const isInitialsSelected = computed(() => selectedAvatar.value === '');
const isImageSelected = computed(() => selectedAvatar.value !== '' && selectedAvatar.value.startsWith('/images/'));
const currentAvatarDisplay = computed(() => {
    if (isImageSelected.value) {
        return selectedAvatar.value;
    }
    return ''; // Empty for initials
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col w-full space-y-6">
                <HeadingSmall
                    title="Profile information"
                    description="Update your name and email address"
                />

                <Form
                    v-bind="ProfileController.update.form()"
                    class="w-full space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <!-- Avatar Section -->
                    <div class="w-full grid gap-4">
                        <Label for="avatar" class="text-base font-medium">Profile Picture</Label>
                        
                        <!-- Current Avatar Display - SMALLER -->
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
                                    {{ isImageSelected ? 'Selected avatar' : 'Initials based on your name' }}
                                </p>
                            </div>
                        </div>

                        <!-- Avatar Selection Grid - SMALLER -->
                        <div class="w-full space-y-2">
                            <Label class="text-sm font-medium">Choose Your Avatar</Label>
                            <div class="w-full grid grid-cols-8 gap-2">
                                <!-- Avatar Options -->
                                <div
                                    v-for="(avatar, index) in avatarOptions"
                                    :key="index"
                                    class="aspect-square rounded-full border-2 cursor-pointer hover:border-primary transition-all hover:scale-105"
                                    :class="[
                                        selectedAvatar === avatar.value ? 'border-primary ring-1 ring-primary/20' : 'border-border'
                                    ]"
                                    @click="selectAvatar(avatar.value)"
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
                            <p class="text-xs text-muted-foreground">
                                Click to select an avatar. First option shows your initials.
                            </p>
                        </div>
                        
                        <!-- Hidden input to store the selected avatar -->
                        <input type="hidden" name="avatar" :value="selectedAvatar" />
                        <InputError class="mt-2" :message="errors.avatar" />
                    </div>

                    <!-- Personal Information Section -->
                    <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="w-full grid gap-2">
                            <Label for="name">Name</Label>
                            <Input
                                id="name"
                                class="w-full"
                                name="name"
                                :default-value="user.name"
                                required
                                autocomplete="name"
                                placeholder="Full name"
                            />
                            <InputError class="mt-2" :message="errors.name" />
                        </div>

                        <div class="w-full grid gap-2">
                            <Label for="phone">Phone</Label>
                            <Input
                                id="phone"
                                type="tel"
                                class="w-full"
                                name="phone"
                                :default-value="user.phone"
                                autocomplete="tel"
                                placeholder="Phone number"
                            />
                            <InputError class="mt-2" :message="errors.phone" />
                        </div>
                    </div>

                    <div class="w-full grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="w-full"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="errors.email" />
                    </div>

                    <!-- Professional Information Section -->
                    <div class="w-full grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Role Field (Read-only) -->
                        <div class="w-full grid gap-2">
                            <Label for="role">Role</Label>
                            <div class="w-full flex items-center h-10 px-3 py-2 text-sm border border-input bg-muted rounded-md">
                                {{ roles[user.role] || user.role }}
                            </div>
                            <input type="hidden" name="role" :value="user.role" />
                            <InputError class="mt-2" :message="errors.role" />
                        </div>

                        <!-- Office Field -->
                        <div class="w-full grid gap-2">
                            <Label for="office">Office</Label>
                            <Select name="office" :default-value="user.office">
                                <SelectTrigger class="w-full">
                                    <SelectValue :placeholder="getOfficeDisplayValue()">
                                        <template #default>
                                            {{ getOfficeDisplayValue() }}
                                        </template>
                                    </SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Offices</SelectLabel>
                                        <SelectItem 
                                            v-for="(label, value) in offices" 
                                            :key="value" 
                                            :value="value"
                                        >
                                            {{ label }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-2" :message="errors.office" />
                        </div>

                        <!-- Position Field -->
                        <div class="w-full grid gap-2">
                            <Label for="position">Position</Label>
                            <Select name="position" :default-value="user.position">
                                <SelectTrigger class="w-full">
                                    <SelectValue :placeholder="getPositionDisplayValue()">
                                        <template #default>
                                            {{ getPositionDisplayValue() }}
                                        </template>
                                    </SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Positions</SelectLabel>
                                        <SelectItem 
                                            v-for="(label, value) in positions" 
                                            :key="value" 
                                            :value="value"
                                        >
                                            {{ label }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError class="mt-2" :message="errors.position" />
                        </div>
                    </div>

                    <!-- Email Verification Section -->
                    <div v-if="mustVerifyEmail && !user.email_verified_at" class="w-full">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="send()"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            A new verification link has been sent to your email
                            address.
                        </div>
                    </div>

                    <!-- Last Update Summary -->
                    <div class="w-full pt-4 border-t">
                        <div class="w-full grid gap-2">
                            <Label class="text-muted-foreground">Last Profile Update</Label>
                            <div class="w-full flex items-center justify-between p-3 bg-muted rounded-md">
                                <span class="text-sm font-medium">{{ lastUpdate }}</span>
                                <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                    <span>Profile ID: {{ user.id }}</span>
                                    <span>•</span>
                                    <span>Member since {{ new Date(user.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full flex items-center gap-4">
                        <Button
                            :disabled="processing"
                            data-test="update-profile-button"
                        >
                            Save Changes
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </Form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>