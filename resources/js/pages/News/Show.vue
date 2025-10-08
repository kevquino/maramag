<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Edit, Trash2, Calendar, User, Eye, Star } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Toggle } from '@/components/ui/toggle';
import { Label } from '@/components/ui/label';
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
    <div class="w-full p-4 sm:p-6">
      <div class="w-full max-w-none mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-2">
              <h1 class="text-2xl sm:text-3xl font-bold text-foreground truncate">
                {{ news.title }}
              </h1>
              <Star 
                v-if="news.is_featured" 
                class="h-6 w-6 text-yellow-500 fill-yellow-500 flex-shrink-0" 
              />
            </div>
            <p class="text-muted-foreground text-lg">{{ news.excerpt }}</p>
            
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

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
          <!-- Left Column - Main Content -->
          <div class="xl:col-span-2 space-y-6">
            <!-- Article Content Card -->
            <div class="bg-card rounded-lg border shadow-sm">
              <div class="p-4 sm:p-6 border-b">
                <h2 class="text-lg font-semibold">Article Content</h2>
              </div>
              <div class="p-4 sm:p-6">
                <div class="prose prose-gray max-w-none dark:prose-invert">
                  <div class="whitespace-pre-wrap text-foreground leading-relaxed">
                    {{ news.content }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column - Sidebar -->
          <div class="space-y-6">
            <!-- Featured Image Card -->
            <div v-if="news.image_url" class="bg-card rounded-lg border shadow-sm">
              <div class="p-4 sm:p-6 border-b">
                <h2 class="text-lg font-semibold">Featured Image</h2>
              </div>
              <div class="p-4 sm:p-6">
                <img 
                  :src="news.image_url" 
                  :alt="news.title"
                  class="w-full h-48 sm:h-64 object-cover rounded-lg border"
                />
              </div>
            </div>

            <!-- Article Details Card -->
            <div class="bg-card rounded-lg border shadow-sm">
              <div class="p-4 sm:p-6 border-b">
                <h2 class="text-lg font-semibold">Article Details</h2>
              </div>
              <div class="p-4 sm:p-6 space-y-6">
                <!-- Status -->
                <div class="space-y-2">
                  <Label class="text-sm font-medium">Status</Label>
                  <Badge 
                    :variant="
                      news.status === 'published' 
                        ? 'default' 
                        : news.status === 'draft' 
                        ? 'secondary' 
                        : 'destructive'
                    "
                    class="w-full justify-center py-2"
                  >
                    {{ news.status.charAt(0).toUpperCase() + news.status.slice(1) }}
                  </Badge>
                </div>

                <!-- Category -->
                <div class="space-y-2">
                  <Label class="text-sm font-medium">Category</Label>
                  <Badge variant="outline" class="w-full justify-center py-2">
                    {{ news.category }}
                  </Badge>
                </div>

                <!-- Featured Toggle Display -->
                <div class="space-y-2">
                  <Label class="text-sm font-medium">Featured Article</Label>
                  <div class="flex items-center justify-between p-1 border rounded-lg bg-muted/50">
                    <div class="flex items-center space-x-2">
                      <Toggle 
                        :pressed="news.is_featured"
                        disabled
                        aria-label="Featured status"
                        :class="news.is_featured ? 'bg-primary text-primary-foreground' : ''"
                      >
                        <Star class="h-4 w-4" :class="news.is_featured ? 'text-yellow-500 fill-yellow-500' : 'text-muted-foreground'" />
                      </Toggle>
                      <span class="text-sm">{{ news.is_featured ? 'Featured' : 'Not Featured' }}</span>
                    </div>
                  </div>
                  <p class="text-xs text-muted-foreground">
                    Featured articles are highlighted with a star and appear first in listings
                  </p>
                </div>

                <!-- Author -->
                <div class="space-y-2">
                  <Label class="text-sm font-medium">Author</Label>
                  <div class="text-sm">
                    <p class="text-foreground">{{ news.author.name }}</p>
                    <p class="text-xs text-muted-foreground">{{ news.author.email }}</p>
                  </div>
                </div>

                <!-- Dates -->
                <div class="space-y-4">
                  <div class="space-y-2">
                    <Label class="text-sm font-medium">Created</Label>
                    <p class="text-sm text-foreground">{{ formatDate(news.created_at) }}</p>
                  </div>
                  
                  <div class="space-y-2">
                    <Label class="text-sm font-medium">Last Updated</Label>
                    <p class="text-sm text-foreground">{{ formatDate(news.updated_at) }}</p>
                  </div>
                  
                  <div v-if="news.published_at" class="space-y-2">
                    <Label class="text-sm font-medium">Published</Label>
                    <p class="text-sm text-foreground">{{ formatDate(news.published_at) }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions Card -->
            <div class="bg-card rounded-lg border shadow-sm">
              <div class="p-4 sm:p-6">
                <div class="space-y-3">
                  <Button
                    type="button"
                    @click="handleEdit"
                    class="w-full"
                    size="lg"
                  >
                    <Edit class="h-4 w-4 mr-2" />
                    Edit
                  </Button>
                  
                  <Button
                    type="button"
                    variant="outline"
                    @click="handleBackToList"
                    class="w-full"
                  >
                    <ArrowLeft class="h-4 w-4 mr-2" />
                    Cancel
                  </Button>

                  <Button
                    type="button"
                    variant="destructive"
                    @click="openDeleteDialog"
                    class="w-full"
                  >
                    <Trash2 class="h-4 w-4 mr-2" />
                    Delete
                  </Button>
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

.resize-vertical {
  resize: vertical;
}

/* Custom scrollbar for textareas */
textarea::-webkit-scrollbar {
  width: 6px;
}

textarea::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

textarea::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

textarea::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  textarea::-webkit-scrollbar-track {
    background: #374151;
  }

  textarea::-webkit-scrollbar-thumb {
    background: #6b7280;
  }

  textarea::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
  }
}
</style>