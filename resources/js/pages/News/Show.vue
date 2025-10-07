<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Edit, Trash2, Calendar, User, Eye, Star } from 'lucide-vue-next';
import { ref } from 'vue';
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
  news: {
    id: string;
    title: string;
    slug: string;
    excerpt: string;
    content: string;
    status: 'draft' | 'published' | 'archived';
    category: string;
    author: {
      id: string;
      name: string;
      email: string;
    };
    published_at: string;
    created_at: string;
    updated_at: string;
    is_featured: boolean;
    image_path: string | null;
    image_url: string | null;
  };
}>();

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
  {
    title: 'News Management',
    href: '/news',
  },
  {
    title: props.news.title,
    href: `/news/${props.news.id}`,
  },
];

// Delete dialog state
const deleteDialogOpen = ref(false);
const deleting = ref(false);

// Handle back to news list
const handleBackToList = () => {
  router.get('/news');
};

// Handle edit article
const handleEdit = () => {
  router.get(`/news/${props.news.id}/edit`);
};

// Delete news article using Inertia
const deleteNews = async () => {
  deleting.value = true;
  try {
    router.delete(`/news/${props.news.id}`, {
      preserveScroll: false,
      onSuccess: () => {
        toast.success('Article deleted successfully!');
        router.get('/news');
      },
      onError: (errors) => {
        const errorMsg = errors.message || 'Failed to delete article';
        toast.error(errorMsg);
        deleting.value = false;
        deleteDialogOpen.value = false;
      },
    });
  } catch (err) {
    console.error('Failed to delete article:', err);
    const errorMsg = err instanceof Error ? err.message : 'Failed to delete article';
    toast.error(errorMsg);
    deleting.value = false;
    deleteDialogOpen.value = false;
  }
};

// Open delete confirmation dialog
const openDeleteDialog = () => {
  deleteDialogOpen.value = true;
};

// Format date
const formatDate = (dateString: string) => {
  if (!dateString) return 'Not published';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>

<template>
  <Head :title="news.title" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 w-full">
      <!-- Header Section -->
      <div class="flex flex-col w-full">
        <div class="w-full">
          <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-2">
                <h1 class="text-2xl md:text-3xl font-bold text-foreground truncate">
                  {{ news.title }}
                </h1>
                <Star 
                  v-if="news.is_featured" 
                  class="h-6 w-6 text-yellow-500 fill-yellow-500 flex-shrink-0" 
                />
              </div>
              <p class="text-muted-foreground text-lg">{{ news.excerpt }}</p>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-2">
              <Button variant="outline" size="sm" @click="handleBackToList">
                <ArrowLeft class="h-4 w-4 mr-2" />
                Back to News
              </Button>
              <Button size="sm" @click="handleEdit">
                <Edit class="h-4 w-4 mr-2" />
                Edit
              </Button>
              <Button variant="destructive" size="sm" @click="openDeleteDialog">
                <Trash2 class="h-4 w-4 mr-2" />
                Delete
              </Button>
            </div>
          </div>

          <!-- Article Meta Information -->
          <div class="flex flex-wrap items-center gap-4 mt-4 text-sm text-muted-foreground">
            <div class="flex items-center gap-1">
              <User class="h-4 w-4" />
              <span>By {{ news.author.name }}</span>
            </div>
            <div class="flex items-center gap-1">
              <Calendar class="h-4 w-4" />
              <span>Created {{ formatDate(news.created_at) }}</span>
            </div>
            <div v-if="news.published_at" class="flex items-center gap-1">
              <Eye class="h-4 w-4" />
              <span>Published {{ formatDate(news.published_at) }}</span>
            </div>
            <Badge 
              :variant="
                news.status === 'published' 
                  ? 'default' 
                  : news.status === 'draft' 
                  ? 'secondary' 
                  : 'destructive'
              "
            >
              {{ news.status.charAt(0).toUpperCase() + news.status.slice(1) }}
            </Badge>
            <Badge variant="outline">
              {{ news.category }}
            </Badge>
            <Badge v-if="news.is_featured" variant="default">
              Featured
            </Badge>
          </div>
        </div>
      </div>

      <!-- Article Content -->
      <div class="flex flex-col lg:flex-row gap-6 w-full">
        <!-- Main Content -->
        <div class="flex-1 space-y-6">
          <!-- Article Content Card -->
          <div class="bg-card rounded-lg border shadow-sm p-6">
            <div class="prose prose-gray max-w-none dark:prose-invert">
              <div class="whitespace-pre-wrap text-foreground leading-relaxed">
                {{ news.content }}
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:w-80 xl:w-96 flex-shrink-0 space-y-6">
          <!-- Featured Image Card -->
          <div v-if="news.image_url" class="bg-card rounded-lg border shadow-sm overflow-hidden">
            <div class="p-4 border-b">
              <h3 class="text-lg font-semibold text-foreground">Featured Image</h3>
            </div>
            <div class="p-4">
              <img 
                :src="news.image_url" 
                :alt="news.title"
                class="w-full h-48 md:h-64 object-cover rounded-md shadow-sm"
              />
            </div>
          </div>

          <!-- Article Details Card -->
          <div class="bg-card rounded-lg border shadow-sm">
            <div class="p-4 border-b">
              <h3 class="text-lg font-semibold text-foreground">Article Details</h3>
            </div>
            <div class="p-4 space-y-4 text-sm">
              <div>
                <p class="text-muted-foreground font-medium">Status</p>
                <Badge 
                  :variant="
                    news.status === 'published' 
                      ? 'default' 
                      : news.status === 'draft' 
                      ? 'secondary' 
                      : 'destructive'
                  "
                  class="mt-1"
                >
                  {{ news.status.charAt(0).toUpperCase() + news.status.slice(1) }}
                </Badge>
              </div>
              
              <div>
                <p class="text-muted-foreground font-medium">Category</p>
                <Badge variant="outline" class="mt-1">
                  {{ news.category }}
                </Badge>
              </div>
              
              <div>
                <p class="text-muted-foreground font-medium">Featured</p>
                <Badge :variant="news.is_featured ? 'default' : 'secondary'" class="mt-1">
                  {{ news.is_featured ? 'Yes' : 'No' }}
                </Badge>
              </div>
              
              <div>
                <p class="text-muted-foreground font-medium">Author</p>
                <p class="text-foreground mt-1">{{ news.author.name }}</p>
                <p class="text-xs text-muted-foreground">{{ news.author.email }}</p>
              </div>
              
              <div>
                <p class="text-muted-foreground font-medium">Created</p>
                <p class="text-foreground mt-1">{{ formatDate(news.created_at) }}</p>
              </div>
              
              <div>
                <p class="text-muted-foreground font-medium">Last Updated</p>
                <p class="text-foreground mt-1">{{ formatDate(news.updated_at) }}</p>
              </div>
              
              <div v-if="news.published_at">
                <p class="text-muted-foreground font-medium">Published</p>
                <p class="text-foreground mt-1">{{ formatDate(news.published_at) }}</p>
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
            This action cannot be undone. This will permanently delete the news article
            "{{ news.title }}" and remove it from our servers.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="deleting" @click="deleteDialogOpen = false">
            Cancel
          </AlertDialogCancel>
          <AlertDialogAction 
            @click="deleteNews"
            class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
            :disabled="deleting"
          >
            <div v-if="deleting" class="flex items-center space-x-2">
              <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-white"></div>
              <span>Deleting...</span>
            </div>
            <span v-else>Delete Article</span>
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>

<style scoped>
.prose {
  line-height: 1.75;
}

.prose p {
  margin-bottom: 1.25em;
}

.prose :where(ul, ol):not(:where([class~="not-prose"] *)) {
  margin-bottom: 1.25em;
  padding-left: 1.625em;
}

.prose :where(li):not(:where([class~="not-prose"] *)) {
  margin-bottom: 0.5em;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
  .flex-col.lg\:flex-row {
    flex-direction: column;
  }
  
  .lg\:w-80, .xl\:w-96 {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
  }
}
</style>