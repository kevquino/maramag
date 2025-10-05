<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { news } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// Define the news article interface
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
}

// Mock data
const newsArticles = ref<NewsArticle[]>([
  {
    id: 1,
    title: 'New Product Launch Exceeds Expectations',
    excerpt: 'Our latest product has seen record sales in the first week of release, surpassing all projections.',
    content: 'Full article content would go here...',
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
  {
    id: 3,
    title: 'Industry Conference Highlights Innovation',
    excerpt: 'Our team presented groundbreaking research at the annual industry conference.',
    content: 'Full article content would go here...',
    publishedAt: '2023-10-05',
    author: 'Sarah Johnson',
    category: 'Events',
    status: 'draft'
  },
  {
    id: 4,
    title: 'Partnership with Tech Giant Announced',
    excerpt: 'New strategic partnership set to revolutionize the industry with combined expertise.',
    content: 'Full article content would go here...',
    publishedAt: '2023-09-28',
    author: 'Michael Chen',
    category: 'Partnerships',
    status: 'published',
    isFeatured: true
  },
  {
    id: 5,
    title: 'Sustainability Initiative Reaches Milestone',
    excerpt: 'Company achieves carbon neutrality goal ahead of schedule.',
    content: 'Full article content would go here...',
    publishedAt: '2023-09-20',
    author: 'Emma Wilson',
    category: 'Sustainability',
    status: 'archived'
  },
  {
    id: 6,
    title: 'New Office Opening in Downtown',
    excerpt: 'Expanding our presence with a new state-of-the-art office location.',
    content: 'Full article content would go here...',
    publishedAt: '2023-09-15',
    author: 'Robert Brown',
    category: 'Company News',
    status: 'published'
  }
]);

// Delete modal state
const showDeleteModal = ref(false);
const articleToDelete = ref<NewsArticle | null>(null);

// Search and filter states
const searchQuery = ref('');
const statusFilter = ref('all');
const categoryFilter = ref('all');
const showMobileFilters = ref(false);

// Pagination state
const currentPage = ref(1);
const pageSize = ref(6);

// Computed properties
const categories = computed(() => {
  return [...new Set(newsArticles.value.map(article => article.category))];
});

const filteredArticles = computed(() => {
  return newsArticles.value.filter(article => {
    const matchesSearch = article.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         article.excerpt.toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchesStatus = statusFilter.value === 'all' || article.status === statusFilter.value;
    const matchesCategory = categoryFilter.value === 'all' || article.category === categoryFilter.value;
    
    return matchesSearch && matchesStatus && matchesCategory;
  });
});

const paginatedArticles = computed(() => {
  const startIndex = (currentPage.value - 1) * pageSize.value;
  const endIndex = startIndex + pageSize.value;
  return filteredArticles.value.slice(startIndex, endIndex);
});

const totalPages = computed(() => {
  return Math.ceil(filteredArticles.value.length / pageSize.value);
});

const pageNumbers = computed(() => {
  const pages = [];
  const maxVisiblePages = 5;
  
  let startPage = Math.max(1, currentPage.value - Math.floor(maxVisiblePages / 2));
  let endPage = Math.min(totalPages.value, startPage + maxVisiblePages - 1);
  
  if (endPage - startPage + 1 < maxVisiblePages) {
    startPage = Math.max(1, endPage - maxVisiblePages + 1);
  }
  
  for (let i = startPage; i <= endPage; i++) {
    pages.push(i);
  }
  
  return pages;
});

// Actions
const createArticle = () => {
  router.visit('/news/create');
};

const showArticle = (id: number) => {
  router.visit(`/news/${id}`);
};

const editArticle = (id: number) => {
  router.visit(`/news/${id}/edit`);
};

const openDeleteModal = (article: NewsArticle) => {
  articleToDelete.value = article;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  showDeleteModal.value = false;
  articleToDelete.value = null;
};

const confirmDelete = () => {
  if (articleToDelete.value) {
    const id = articleToDelete.value.id;
    newsArticles.value = newsArticles.value.filter(article => article.id !== id);
    if (paginatedArticles.value.length === 0 && currentPage.value > 1) {
      currentPage.value--;
    }
    closeDeleteModal();
  }
};

const toggleFeatured = (id: number) => {
  const article = newsArticles.value.find(a => a.id === id);
  if (article) {
    article.isFeatured = !article.isFeatured;
  }
};

const updateStatus = (id: number, status: 'published' | 'draft' | 'archived') => {
  const article = newsArticles.value.find(a => a.id === id);
  if (article) {
    article.status = status;
  }
};

const goToPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'News Management',
    href: news().url,
  },
];
</script>

<template>
  <Head title="News Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <!-- Header with Stats - Mobile First -->
      <div class="grid gap-3 sm:gap-4 grid-cols-2 lg:grid-cols-4">
        <!-- Total Articles Card -->
        <div class="rounded-lg border border-sidebar-border/70 bg-white shadow-sm p-4 sm:p-6 dark:border-sidebar-border dark:bg-gray-800">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400">Total Articles</p>
              <p class="text-xl sm:text-2xl font-bold mt-1 text-gray-900 dark:text-white">{{ newsArticles.length }}</p>
            </div>
            <div class="p-2 bg-blue-100 rounded-lg dark:bg-blue-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
              </svg>
            </div>
          </div>
        </div>
        
        <!-- Published Card -->
        <div class="rounded-lg border border-sidebar-border/70 bg-white shadow-sm p-4 sm:p-6 dark:border-sidebar-border dark:bg-gray-800">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400">Published</p>
              <p class="text-xl sm:text-2xl font-bold mt-1 text-gray-900 dark:text-white">{{ newsArticles.filter(a => a.status === 'published').length }}</p>
            </div>
            <div class="p-2 bg-green-100 rounded-lg dark:bg-green-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
        
        <!-- Drafts Card -->
        <div class="rounded-lg border border-sidebar-border/70 bg-white shadow-sm p-4 sm:p-6 dark:border-sidebar-border dark:bg-gray-800">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400">Drafts</p>
              <p class="text-xl sm:text-2xl font-bold mt-1 text-gray-900 dark:text-white">{{ newsArticles.filter(a => a.status === 'draft').length }}</p>
            </div>
            <div class="p-2 bg-yellow-100 rounded-lg dark:bg-yellow-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </div>
          </div>
        </div>
        
        <!-- Categories Card -->
        <div class="rounded-lg border border-sidebar-border/70 bg-white shadow-sm p-4 sm:p-6 dark:border-sidebar-border dark:bg-gray-800">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400">Categories</p>
              <p class="text-xl sm:text-2xl font-bold mt-1 text-gray-900 dark:text-white">{{ categories.length }}</p>
            </div>
            <div class="p-2 bg-purple-100 rounded-lg dark:bg-purple-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Bar - Mobile Friendly -->
      <div class="rounded-lg border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-gray-800">
        <div class="p-4">
          <!-- Mobile Filter Toggle -->
          <div class="flex justify-between items-center mb-4 lg:hidden">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">News Articles</h2>
            <button
              @click="showMobileFilters = !showMobileFilters"
              class="p-2 border border-gray-300 rounded-lg dark:border-gray-600"
            >
              <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
              </svg>
            </button>
          </div>

          <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <!-- Search and Filters -->
            <div class="flex flex-col lg:flex-row gap-3 flex-1 w-full" :class="{ 'hidden lg:flex': !showMobileFilters }">
              <!-- Search -->
              <div class="relative w-full lg:w-64">
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search articles..."
                  class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 pl-10 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400"
                  @input="currentPage = 1"
                >
                <svg class="w-4 h-4 absolute left-3 top-3 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>

              <!-- Status Filter -->
              <select
                v-model="statusFilter"
                @change="currentPage = 1"
                class="flex h-10 w-full lg:w-32 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="all">All Status</option>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
                <option value="archived">Archived</option>
              </select>

              <!-- Category Filter -->
              <select
                v-model="categoryFilter"
                @change="currentPage = 1"
                class="flex h-10 w-full lg:w-40 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <option value="all">All Categories</option>
                <option v-for="category in categories" :key="category" :value="category">{{ category }}</option>
              </select>
            </div>

            <!-- Create Button -->
            <button
              @click="createArticle"
              class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 h-10 px-4 py-2 w-full lg:w-auto"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Create News
            </button>
          </div>
        </div>
      </div>

      <!-- Data Table - Responsive Design -->
      <div class="rounded-lg border border-sidebar-border/70 bg-white shadow-sm dark:border-sidebar-border dark:bg-gray-800">
        <div class="p-4 sm:p-6">
          <div class="mb-4 hidden lg:block">
            <h2 class="text-lg font-semibold leading-none tracking-tight text-gray-900 dark:text-white">News Articles</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
              Showing {{ paginatedArticles.length }} of {{ filteredArticles.length }} articles
            </p>
          </div>
          
          <!-- Desktop Table (Large screens only) -->
          <div class="hidden xl:block rounded-md border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50">
                  <th class="h-12 px-4 text-left align-middle font-medium text-gray-600 dark:text-gray-400">Article</th>
                  <th class="h-12 px-4 text-left align-middle font-medium text-gray-600 dark:text-gray-400">Category</th>
                  <th class="h-12 px-4 text-left align-middle font-medium text-gray-600 dark:text-gray-400">Status</th>
                  <th class="h-12 px-4 text-left align-middle font-medium text-gray-600 dark:text-gray-400">Date</th>
                  <th class="h-12 px-4 text-left align-middle font-medium text-gray-600 dark:text-gray-400">Author</th>
                  <th class="h-12 px-4 text-left align-middle font-medium text-gray-600 dark:text-gray-400 w-[160px]">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="article in paginatedArticles" 
                  :key="article.id"
                  class="border-b border-gray-200 dark:border-gray-700 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
                >
                  <td class="p-4 align-middle">
                    <div class="flex items-center space-x-3">
                      <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-lg flex items-center justify-center dark:bg-blue-900">
                        <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                      </div>
                      <div class="min-w-0 flex-1">
                        <div class="flex items-center">
                          <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                            {{ article.title }}
                          </p>
                          <span 
                            v-if="article.isFeatured" 
                            class="ml-2 inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 border-transparent bg-yellow-100 text-yellow-800 hover:bg-yellow-200 dark:bg-yellow-900 dark:text-yellow-200 dark:hover:bg-yellow-800"
                          >
                            Featured
                          </span>
                        </div>
                        <p class="truncate text-sm text-gray-600 dark:text-gray-400">
                          {{ article.excerpt }}
                        </p>
                      </div>
                    </div>
                  </td>
                  <td class="p-4 align-middle">
                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                      {{ article.category }}
                    </span>
                  </td>
                  <td class="p-4 align-middle">
                    <span 
                      class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 border-transparent"
                      :class="{
                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': article.status === 'published',
                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': article.status === 'draft',
                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': article.status === 'archived'
                      }"
                    >
                      {{ article.status }}
                    </span>
                  </td>
                  <td class="p-4 align-middle text-sm text-gray-600 dark:text-gray-400">
                    {{ new Date(article.publishedAt).toLocaleDateString() }}
                  </td>
                  <td class="p-4 align-middle text-sm text-gray-600 dark:text-gray-400">
                    {{ article.author }}
                  </td>
                  <td class="p-4 align-middle">
                    <div class="flex items-center space-x-1">
                      <!-- View Button -->
                      <Link
                        :href="`/news/${article.id}`"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-300 bg-white hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 h-8 w-8 p-0 text-blue-600 dark:text-blue-400"
                        title="View"
                      >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                      </Link>

                      <!-- Featured Toggle -->
                      <button
                        @click="toggleFeatured(article.id)"
                        :class="article.isFeatured ? 'bg-blue-600 text-white hover:bg-blue-700' : 'border border-gray-300 bg-white hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600'"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-8 w-8 p-0"
                        title="Toggle Featured"
                      >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                      </button>
                      
                      <!-- Edit Button -->
                      <Link
                        :href="`/news/${article.id}/edit`"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-300 bg-white hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 h-8 w-8 p-0"
                        title="Edit"
                      >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </Link>

                      <!-- Status Select -->
                      <select
                        :value="article.status"
                        @change="updateStatus(article.id, ($event.target as HTMLSelectElement).value as any)"
                        class="flex h-8 w-24 rounded-md border border-gray-300 bg-white px-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                      >
                        <option value="published">Publish</option>
                        <option value="draft">Draft</option>
                        <option value="archived">Archive</option>
                      </select>
                      
                      <!-- Delete Button -->
                      <button
                        @click="openDeleteModal(article)"
                        class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-300 bg-white hover:bg-red-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-red-900/20 h-8 w-8 p-0 text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"
                        title="Delete"
                      >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="paginatedArticles.length === 0">
                  <td colspan="6" class="h-24 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                      <svg class="h-8 w-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      No articles found matching your criteria.
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Tablet Cards (Medium screens) -->
          <div class="hidden lg:block xl:hidden space-y-3">
            <div 
              v-for="article in paginatedArticles" 
              :key="article.id"
              class="border border-gray-200 rounded-lg p-4 space-y-3 dark:border-gray-700 dark:bg-gray-800"
            >
              <div class="flex items-start justify-between">
                <div class="flex items-center space-x-3 flex-1">
                  <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center dark:bg-blue-900">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                  </div>
                  <div class="min-w-0 flex-1">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ article.title }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">{{ article.excerpt }}</p>
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <button
                    @click="toggleFeatured(article.id)"
                    :class="article.isFeatured ? 'bg-blue-600 text-white' : 'border border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-700'"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium h-8 w-8 p-0"
                    title="Toggle Featured"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                  </button>
                </div>
              </div>
              
              <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                  {{ article.category }}
                </span>
                <span 
                  class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                  :class="{
                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': article.status === 'published',
                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': article.status === 'draft',
                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': article.status === 'archived'
                  }"
                >
                  {{ article.status }}
                </span>
                <span v-if="article.isFeatured" class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                  Featured
                </span>
              </div>
              
              <div class="grid grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-400">
                <div>
                  <span class="font-medium">Date:</span>
                  {{ new Date(article.publishedAt).toLocaleDateString() }}
                </div>
                <div>
                  <span class="font-medium">Author:</span>
                  {{ article.author }}
                </div>
              </div>
              
              <div class="flex justify-between items-center pt-3 border-t border-gray-200 dark:border-gray-700">
                <select
                  :value="article.status"
                  @change="updateStatus(article.id, ($event.target as HTMLSelectElement).value as any)"
                  class="flex h-9 text-sm rounded-md border border-gray-300 bg-white px-3 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                >
                  <option value="published">Publish</option>
                  <option value="draft">Draft</option>
                  <option value="archived">Archive</option>
                </select>
                
                <div class="flex items-center space-x-2">
                  <Link
                    :href="`/news/${article.id}`"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-gray-300 bg-white hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 h-9 px-4 text-blue-600 dark:text-blue-400"
                  >
                    View
                  </Link>
                  <Link
                    :href="`/news/${article.id}/edit`"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-gray-300 bg-white hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 h-9 px-4 text-gray-700 dark:text-gray-300"
                  >
                    Edit
                  </Link>
                  <button
                    @click="openDeleteModal(article)"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-gray-300 bg-white hover:bg-red-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-red-900/20 h-9 px-4 text-red-600 dark:text-red-400"
                  >
                    Delete
                  </button>
                </div>
              </div>
            </div>
            
            <div v-if="paginatedArticles.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
              <svg class="h-8 w-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              No articles found matching your criteria.
            </div>
          </div>

          <!-- Mobile Cards (Small screens) -->
          <div class="lg:hidden space-y-3">
            <div 
              v-for="article in paginatedArticles" 
              :key="article.id"
              class="border border-gray-200 rounded-lg p-4 space-y-3 dark:border-gray-700 dark:bg-gray-800"
            >
              <div class="flex items-start justify-between">
                <div class="flex items-center space-x-3 flex-1">
                  <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-lg flex items-center justify-center dark:bg-blue-900">
                    <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                    </svg>
                  </div>
                  <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ article.title }}</h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">{{ article.excerpt }}</p>
                  </div>
                </div>
                <div class="flex items-center space-x-1">
                  <button
                    @click="toggleFeatured(article.id)"
                    :class="article.isFeatured ? 'bg-blue-600 text-white' : 'border border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-700'"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium h-7 w-7 p-0"
                  >
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                  </button>
                </div>
              </div>
              
              <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-full border px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                  {{ article.category }}
                </span>
                <span 
                  class="inline-flex items-center rounded-full border px-2 py-1 text-xs font-semibold"
                  :class="{
                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': article.status === 'published',
                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': article.status === 'draft',
                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': article.status === 'archived'
                  }"
                >
                  {{ article.status }}
                </span>
                <span v-if="article.isFeatured" class="inline-flex items-center rounded-full border px-2 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                  Featured
                </span>
              </div>
              
              <div class="flex justify-between items-center text-xs text-gray-500 dark:text-gray-400">
                <span>{{ new Date(article.publishedAt).toLocaleDateString() }}</span>
                <span>By {{ article.author }}</span>
              </div>
              
              <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-700">
                <select
                  :value="article.status"
                  @change="updateStatus(article.id, ($event.target as HTMLSelectElement).value as any)"
                  class="flex h-7 text-xs rounded-md border border-gray-300 bg-white px-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                >
                  <option value="published">Publish</option>
                  <option value="draft">Draft</option>
                  <option value="archived">Archive</option>
                </select>
                
                <div class="flex items-center space-x-2">
                  <Link
                    :href="`/news/${article.id}`"
                    class="inline-flex items-center justify-center rounded-md text-xs font-medium border border-gray-300 bg-white hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 h-7 px-3 text-blue-600 dark:text-blue-400"
                  >
                    View
                  </Link>
                  <Link
                    :href="`/news/${article.id}/edit`"
                    class="inline-flex items-center justify-center rounded-md text-xs font-medium border border-gray-300 bg-white hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 h-7 px-3 text-gray-700 dark:text-gray-300"
                  >
                    Edit
                  </Link>
                  <button
                    @click="openDeleteModal(article)"
                    class="inline-flex items-center justify-center rounded-md text-xs font-medium border border-gray-300 bg-white hover:bg-red-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-red-900/20 h-7 px-3 text-red-600 dark:text-red-400"
                  >
                    Delete
                  </button>
                </div>
              </div>
            </div>
            
            <div v-if="paginatedArticles.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
              <svg class="h-8 w-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              No articles found matching your criteria.
            </div>
          </div>

          <!-- Pagination -->
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-2 py-4">
            <div class="text-sm text-gray-600 dark:text-gray-400 text-center sm:text-left">
              Page {{ currentPage }} of {{ totalPages }} • 
              Showing {{ Math.min(pageSize, paginatedArticles.length) }} of {{ filteredArticles.length }} results
            </div>
            
            <div v-if="totalPages > 1" class="flex items-center space-x-1">
              <button
                @click="goToPage(currentPage - 1)"
                :disabled="currentPage === 1"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-300 bg-white hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 h-9 px-3 text-gray-700 dark:text-gray-300"
                :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }"
              >
                Previous
              </button>
              
              <div class="flex items-center space-x-1">
                <button
                  v-for="page in pageNumbers"
                  :key="page"
                  @click="goToPage(page)"
                  class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-300 bg-white hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 h-9 w-9 p-0 text-gray-700 dark:text-gray-300"
                  :class="currentPage === page ? 'bg-blue-600 text-white hover:bg-blue-700 border-blue-600' : ''"
                >
                  {{ page }}
                </button>
              </div>
              
              <button
                @click="goToPage(currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-300 bg-white hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 h-9 px-3 text-gray-700 dark:text-gray-300"
                :class="{ 'opacity-50 cursor-not-allowed': currentPage === totalPages }"
              >
                Next
              </button>
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
              <span class="font-medium text-gray-900 dark:text-white">"{{ articleToDelete?.title }}"</span>? 
              This action cannot be undone.
            </p>

            <!-- Article Preview -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6 text-left">
              <div class="flex items-center space-x-3">
                <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-lg flex items-center justify-center dark:bg-blue-900">
                  <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                  </svg>
                </div>
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                    {{ articleToDelete?.title }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    {{ articleToDelete?.excerpt }}
                  </p>
                </div>
              </div>
              <div class="flex items-center justify-between mt-3 text-xs text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  {{ articleToDelete?.author }}
                </span>
                <span class="inline-flex items-center">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  {{ articleToDelete ? new Date(articleToDelete.publishedAt).toLocaleDateString() : '' }}
                </span>
              </div>
            </div>

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
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Smooth backdrop blur transition */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}
</style>