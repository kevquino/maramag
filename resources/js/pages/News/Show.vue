<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { news } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

interface NewsArticle {
  id: number;
  title: string;
  excerpt: string;
  content: string;
  publishedAt: string;
  author: string;
  category: string;
  status: 'published' | 'draft' | 'archived';
  isFeatured?: boolean;
  image?: string | null;
}

// Mock data - in real app, this would come from props
const newsArticles: NewsArticle[] = [
  {
    id: 1,
    title: 'New Product Launch Exceeds Expectations',
    excerpt: 'Our latest product has seen record sales in the first week of release, surpassing all projections.',
    content: `
      <p>We are thrilled to announce that our latest product launch has exceeded all expectations, with record-breaking sales in the first week alone. The innovative features and user-centric design have resonated strongly with our customer base.</p>
      
      <h3>Key Achievements</h3>
      <ul>
        <li>150% increase in first-week sales compared to previous launches</li>
        <li>98% customer satisfaction rating</li>
        <li>Featured in top industry publications</li>
      </ul>

      <p>The success of this launch demonstrates our commitment to innovation and customer satisfaction. Our team worked tirelessly to ensure every aspect of the product met the highest standards of quality and performance.</p>

      <blockquote>
        "This product represents a significant leap forward in our industry. The response from customers has been overwhelmingly positive."
      </blockquote>

      <p>We look forward to continuing this momentum and bringing more innovative solutions to our customers in the coming months.</p>
    `,
    publishedAt: '2023-10-15',
    author: 'Jane Smith',
    category: 'Business',
    status: 'published',
    isFeatured: true
  },
  {
    id: 2,
    title: 'Company Announces Quarterly Results',
    excerpt: 'Strong growth reported across all business segments with a 25% increase in revenue.',
    content: 'Full article content would go here...',
    publishedAt: '2023-10-10',
    author: 'John Doe',
    category: 'Finance',
    status: 'published'
  },
  // ... other articles
];

// Get article ID from route
const props = defineProps<{
  id: string;
}>();

// Delete modal state
const showDeleteModal = ref(false);

const article = ref<NewsArticle | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'News Management',
    href: news().url,
  },
  {
    title: 'View Article',
    href: '#',
  },
];

// Find the article by ID
onMounted(() => {
  const foundArticle = newsArticles.find(a => a.id === parseInt(props.id));
  if (foundArticle) {
    article.value = foundArticle;
  }
});

const openDeleteModal = () => {
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  showDeleteModal.value = false;
};

const confirmDelete = () => {
  // In real app, this would make an API call to delete the article
  console.log('Deleting article:', article.value?.id);
  
  // Redirect back to news index after deletion
  router.visit(news().url);
  closeDeleteModal();
};
</script>

<template>
  <Head :title="article?.title || 'News Article'" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <!-- Header Section -->
      <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-2">
            <span 
              class="inline-flex items-center rounded-full border px-3 py-1 text-sm font-semibold"
              :class="{
                'bg-green-100 text-green-800 border-green-200 dark:bg-green-900 dark:text-green-200 dark:border-green-800': article?.status === 'published',
                'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900 dark:text-yellow-200 dark:border-yellow-800': article?.status === 'draft',
                'bg-red-100 text-red-800 border-red-200 dark:bg-red-900 dark:text-red-200 dark:border-red-800': article?.status === 'archived'
              }"
            >
              {{ article?.status }}
            </span>
            <span 
              v-if="article?.isFeatured"
              class="inline-flex items-center rounded-full border px-3 py-1 text-sm font-semibold bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900 dark:text-yellow-200 dark:border-yellow-800"
            >
              Featured
            </span>
          </div>
          <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-2">
            {{ article?.title }}
          </h1>
          <p class="text-lg text-gray-600 dark:text-gray-400">
            {{ article?.excerpt }}
          </p>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex items-center gap-3">
          <Link
            :href="`/news/${article?.id}/edit`"
            class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-300 bg-white hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 h-10 px-4 py-2 text-gray-700 dark:text-gray-300"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
          </Link>
          <button
            @click="openDeleteModal"
            class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-300 bg-white hover:bg-red-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-red-900/20 h-10 px-4 py-2 text-red-600 dark:text-red-400"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Delete
          </button>
        </div>
      </div>

      <!-- Article Content -->
      <div class="grid gap-6 lg:grid-cols-4">
        <!-- Main Content -->
        <div class="lg:col-span-3">
          <div class="rounded-lg border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-gray-800">
            <div class="p-6">
              <!-- Article Meta -->
              <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  <span>By {{ article?.author }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span>{{ article ? new Date(article.publishedAt).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '' }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                  </svg>
                  <span>{{ article?.category }}</span>
                </div>
              </div>

              <!-- Article Content -->
              <div class="prose max-w-none dark:prose-invert">
                <div v-html="article?.content"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Article Information -->
          <div class="rounded-lg border border-sidebar-border/70 bg-white p-6 shadow-sm dark:border-sidebar-border dark:bg-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Article Information
            </h3>
            <div class="space-y-3 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Article ID:</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ props.id }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Status:</span>
                <span 
                  class="font-medium"
                  :class="{
                    'text-green-600 dark:text-green-400': article?.status === 'published',
                    'text-yellow-600 dark:text-yellow-400': article?.status === 'draft',
                    'text-red-600 dark:text-red-400': article?.status === 'archived'
                  }"
                >
                  {{ article?.status }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Featured:</span>
                <span class="font-medium text-gray-900 dark:text-white">
                  {{ article?.isFeatured ? 'Yes' : 'No' }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Word Count:</span>
                <span class="font-medium text-gray-900 dark:text-white">
                  {{ article?.content ? article.content.replace(/<[^>]*>/g, '').split(/\s+/).filter(word => word.length > 0).length : 0 }} words
                </span>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="rounded-lg border border-sidebar-border/70 bg-white p-6 shadow-sm dark:border-sidebar-border dark:bg-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Quick Actions
            </h3>
            <div class="space-y-3">
              <Link
                :href="`/news/${article?.id}/edit`"
                class="w-full inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 h-10 px-4 py-2"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Article
              </Link>
              <Link
                :href="news().url"
                class="w-full inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-300 bg-white hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 h-10 px-4 py-2 text-gray-700 dark:text-gray-300"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to List
              </Link>
            </div>
          </div>

          <!-- Status Guide -->
          <div class="rounded-lg border border-sidebar-border/70 bg-white p-6 shadow-sm dark:border-sidebar-border dark:bg-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
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

    <!-- Delete Confirmation Modal -->
    <div 
      v-if="showDeleteModal" 
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity"
      @click="closeDeleteModal"
    >
      <div 
        class="bg-white rounded-lg max-w-md w-full mx-auto shadow-xl dark:bg-gray-800 transform transition-all"
        @click.stop
      >
        <div class="p-6">
          <!-- Warning Icon -->
          <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full dark:bg-red-900">
            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>

          <!-- Modal Content -->
          <div class="text-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
              Delete Article
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
              Are you sure you want to delete 
              <span class="font-medium text-gray-900 dark:text-white">"{{ article?.title }}"</span>? 
              This action cannot be undone.
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
              <button
                @click="closeDeleteModal"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-300 bg-white hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 h-10 px-4 py-2 text-gray-700 dark:text-gray-300 flex-1 sm:flex-none"
              >
                Cancel
              </button>
              <button
                @click="confirmDelete"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-red-600 text-white hover:bg-red-700 h-10 px-4 py-2 flex-1 sm:flex-none"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete Article
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.prose {
  line-height: 1.75;
}

.prose h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-top: 1.5rem;
  margin-bottom: 0.5rem;
  color: #1f2937;
}

.dark .prose h3 {
  color: #f3f4f6;
}

.prose ul {
  list-style-type: disc;
  padding-left: 1.5rem;
  margin-bottom: 1rem;
}

.prose li {
  margin-bottom: 0.25rem;
}

.prose blockquote {
  border-left: 4px solid #e5e7eb;
  padding-left: 1rem;
  font-style: italic;
  color: #6b7280;
  margin: 1.5rem 0;
}

.dark .prose blockquote {
  border-left-color: #4b5563;
  color: #9ca3af;
}

.prose p {
  margin-bottom: 1rem;
}

/* Smooth backdrop blur transition */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}
</style>