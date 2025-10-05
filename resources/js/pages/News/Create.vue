<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { news } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'News Management',
        href: news().url,
    },
    {
        title: 'Create News',
        href: '#',
    },
];

const form = useForm({
    title: '',
    excerpt: '',
    content: '',
    category: 'Business',
    published_at: new Date().toISOString().slice(0, 16),
    author: 'Admin User',
    status: 'draft' as 'published' | 'draft' | 'archived',
    isFeatured: false,
    image: null as File | null,
});

const imagePreview = ref<string | null>(null);

const handleImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.image = target.files[0];
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(target.files[0]);
    }
};

const removeImage = () => {
    form.image = null;
    imagePreview.value = null;
};

const submit = () => {
    // Use the correct route name and method
    form.post('/news', {
        onSuccess: () => {
            // This will redirect to the news index page
            // and the new article will be shown there
        },
        onError: (errors) => {
            console.error('Error creating news:', errors);
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <!-- Keep the rest of your template exactly as it is -->
    <Head title="Create News Article" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Create News Article
                    </h1>
                    <p class="mt-1 text-gray-600 dark:text-gray-400">
                        Create and publish new articles for your audience
                    </p>
                </div>
            </div>

            <!-- Form Section -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Form -->
                <div class="lg:col-span-2">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Title Input -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Title *
                            </label>
                            <input
                                id="title"
                                v-model="form.title"
                                type="text"
                                required
                                class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400"
                                placeholder="Enter article title"
                            />
                            <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">
                                {{ form.errors.title }}
                            </p>
                        </div>

                        <!-- Excerpt Input -->
                        <div>
                            <label for="excerpt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Excerpt *
                            </label>
                            <textarea
                                id="excerpt"
                                v-model="form.excerpt"
                                required
                                rows="3"
                                class="flex min-h-[80px] w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm ring-offset-background placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400"
                                placeholder="Enter brief article excerpt"
                            />
                            <p v-if="form.errors.excerpt" class="mt-1 text-sm text-red-600">
                                {{ form.errors.excerpt }}
                            </p>
                        </div>

                        <!-- Content Input -->
                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Content *
                            </label>
                            <textarea
                                id="content"
                                v-model="form.content"
                                required
                                rows="8"
                                class="flex min-h-[120px] w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm ring-offset-background placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400"
                                placeholder="Write your article content here..."
                            />
                            <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">
                                {{ form.errors.content }}
                            </p>
                        </div>

                        <!-- Category, Author, and Date -->
                        <div class="grid gap-4 sm:grid-cols-3">
                            <!-- Category Input -->
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Category *
                                </label>
                                <input
                                    id="category"
                                    v-model="form.category"
                                    type="text"
                                    required
                                    class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400"
                                    placeholder="Category"
                                />
                                <p v-if="form.errors.category" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.category }}
                                </p>
                            </div>

                            <!-- Author Input -->
                            <div>
                                <label for="author" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Author *
                                </label>
                                <input
                                    id="author"
                                    v-model="form.author"
                                    type="text"
                                    required
                                    class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400"
                                    placeholder="Author name"
                                />
                                <p v-if="form.errors.author" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.author }}
                                </p>
                            </div>

                            <!-- Publish Date -->
                            <div>
                                <label for="published_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Publish Date & Time
                                </label>
                                <input
                                    id="published_at"
                                    v-model="form.published_at"
                                    type="datetime-local"
                                    class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400"
                                />
                            </div>
                        </div>

                        <!-- Status and Featured -->
                        <div class="grid gap-4 sm:grid-cols-2">
                            <!-- Status Select -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Status
                                </label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>

                            <!-- Featured Checkbox -->
                            <div class="flex items-center space-x-3 pt-6">
                                <input
                                    id="isFeatured"
                                    v-model="form.isFeatured"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                                />
                                <label for="isFeatured" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Feature this article
                                </label>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6">
                            <button
                                type="button"
                                @click="router.visit(news().url)"
                                class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-300 bg-white hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 h-10 px-4 py-2 text-gray-700 dark:text-gray-300 order-2 sm:order-1"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 h-10 px-4 py-2 order-1 sm:order-2"
                            >
                                <span v-if="form.processing">Creating...</span>
                                <span v-else>Create Article</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Image Upload -->
                    <div class="rounded-lg border border-sidebar-border/70 bg-white p-6 shadow-sm dark:border-sidebar-border dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            Featured Image
                        </h3>
                        
                        <!-- Image Preview -->
                        <div v-if="imagePreview" class="mb-4">
                            <div class="relative aspect-video overflow-hidden rounded-lg">
                                <img 
                                    :src="imagePreview" 
                                    alt="Preview" 
                                    class="h-full w-full object-cover"
                                />
                                <button
                                    type="button"
                                    @click="removeImage"
                                    class="absolute right-2 top-2 rounded-full bg-red-600 p-1 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Upload Area -->
                        <div 
                            v-if="!imagePreview"
                            class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center transition-colors hover:border-gray-400 dark:hover:border-gray-500"
                        >
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <div class="mt-4">
                                <label for="image-upload" class="cursor-pointer inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 h-10 px-4 py-2">
                                    Upload Image
                                </label>
                                <input
                                    id="image-upload"
                                    type="file"
                                    accept="image/*"
                                    @change="handleImageChange"
                                    class="hidden"
                                />
                            </div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                PNG, JPG, GIF up to 10MB
                            </p>
                        </div>
                    </div>

                    <!-- Publishing Tips -->
                    <div class="rounded-lg border border-sidebar-border/70 bg-white p-6 shadow-sm dark:border-sidebar-border dark:bg-gray-800">
                        <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                            Publishing Tips
                        </h3>
                        <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                            <li class="flex items-start gap-3">
                                <svg class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Write clear and engaging titles that capture attention</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Keep excerpts concise but informative</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Use relevant categories for better organization</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Set future dates to schedule publishing</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Feature important articles for prominence</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Status Guide -->
                    <div class="rounded-lg border border-sidebar-border/70 bg-white p-6 shadow-sm dark:border-sidebar-border dark:bg-gray-800">
                        <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">
                            Status Guide
                        </h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Published
                                </span>
                                <span class="text-gray-600 dark:text-gray-300">Visible to all users</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    Draft
                                </span>
                                <span class="text-gray-600 dark:text-gray-300">Only visible to editors</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold border-transparent bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    Archived
                                </span>
                                <span class="text-gray-600 dark:text-gray-300">Hidden from public view</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>