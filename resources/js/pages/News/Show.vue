<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, User, Tag, Eye, Edit, Trash2, Star } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
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
import { toast } from 'vue-sonner';

// Props
const props = defineProps<{
  article: {
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

// Get auth user from Inertia page props
const page = usePage();
const authUser = computed(() => page.props.auth.user);
const isAuthenticated = computed(() => !!authUser.value);
const canManageNews = computed(() => {
  return isAuthenticated.value && authUser.value?.can_manage_news;
});

// Breadcrumbs
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
  {
    title: 'News Management',
    href: '/news',
  },
  {
    title: props.article.title,
    href: `/news/${props.article.id}`,
  },
]);

// Reactive states
const deleteDialogOpen = ref(false);
const deleting = ref(false);

// Format date
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

// Status configuration
const statusConfig = {
  draft: { variant: 'outline' as const, label: 'Draft', color: 'text-orange-600 bg-orange-50 border-orange-200' },
  published: { variant: 'default' as const, label: 'Published', color: 'text-green-600 bg-green-50 border-green-200' },
  archived: { variant: 'secondary' as const, label: 'Archived', color: 'text-blue-600 bg-blue-50 border-blue-200' },
};

// Handle back to news list using Inertia
const handleBack = () => {
  router.get('/news');
};

// Handle edit using Inertia
const handleEdit = () => {
  router.get(`/news/${props.article.id}/edit`);
};

// Handle status change using Inertia
const handleStatusChange = async (newStatus: string) => {
  try {
    router.post(`/news/${props.article.id}/status`, { status: newStatus }, {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Status updated successfully!');
        router.reload();
      },
      onError: (errors) => {
        const errorMsg = errors.message || 'Failed to update status';
        toast.error(errorMsg);
      },
    });
  } catch (err) {
    console.error('Failed to update status:', err);
    const errorMsg = err instanceof Error ? err.message : 'Failed to update status';
    toast.error(errorMsg);
  }
};

// Handle feature toggle using Inertia
const handleFeatureToggle = async () => {
  try {
    router.post(`/news/${props.article.id}/toggle-featured`, {}, {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Feature status updated!');
        router.reload();
      },
      onError: (errors) => {
        const errorMsg = errors.message || 'Failed to toggle feature';
        toast.error(errorMsg);
      },
    });
  } catch (err) {
    console.error('Failed to toggle feature:', err);
    const errorMsg = err instanceof Error ? err.message : 'Failed to toggle feature';
    toast.error(errorMsg);
  }
};

// Delete news article using Inertia
const deleteNews = async () => {
  deleting.value = true;
  try {
    router.delete(`/news/${props.article.id}`, {
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
</script>

<template>
  <Head :title="article.title" />

  <AppLayout :breadcrumbs="canManageNews ? breadcrumbs : []">
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 w-full">
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between w-full gap-4">
        <div class="w-full">
          <h1 class="text-2xl sm:text-3xl font-bold text-foreground">{{ article.title }}</h1>
          <p class="text-muted-foreground mt-2 text-base sm:text-lg">{{ article.excerpt }}</p>
        </div>
        
        <!-- Admin Actions - Back, Edit and Delete -->
        <div v-if="canManageNews" class="flex items-center justify-end sm:justify-start space-x-2 w-full sm:w-auto">
          <!-- Mobile: Icon-only buttons -->
          <div class="flex sm:hidden space-x-1">
            <Button variant="outline" size="sm" @click="handleBack" class="px-2">
              <ArrowLeft class="h-4 w-4" />
            </Button>
            <Button variant="outline" size="sm" @click="handleEdit" class="px-2">
              <Edit class="h-4 w-4" />
            </Button>
            <Button variant="destructive" size="sm" @click="openDeleteDialog" class="px-2">
              <Trash2 class="h-4 w-4" />
            </Button>
          </div>
          
          <!-- Desktop: Full buttons with text -->
          <div class="hidden sm:flex items-center space-x-2">
            <Button variant="outline" size="sm" @click="handleBack">
              <ArrowLeft class="h-4 w-4 mr-2" />
              Back to List
            </Button>
            <Button variant="outline" size="sm" @click="handleEdit">
              <Edit class="h-4 w-4 mr-2" />
              Edit
            </Button>
            <Button variant="destructive" size="sm" @click="openDeleteDialog">
              <Trash2 class="h-4 w-4 mr-2" />
              Delete
            </Button>
          </div>
        </div>
      </div>

      <!-- Article Meta Information -->
      <div class="flex flex-col sm:flex-row flex-wrap items-start sm:items-center gap-3 sm:gap-4 text-sm text-muted-foreground bg-muted/50 rounded-lg p-4 w-full">
        <div class="flex items-center space-x-2">
          <User class="h-4 w-4" />
          <span>By {{ article.author.name }}</span>
        </div>
        <div class="flex items-center space-x-2">
          <Calendar class="h-4 w-4" />
          <span>Created: {{ formatDate(article.created_at) }}</span>
        </div>
        <div v-if="article.published_at" class="flex items-center space-x-2">
          <Eye class="h-4 w-4" />
          <span>Published: {{ formatDate(article.published_at) }}</span>
        </div>
        <div class="flex items-center space-x-2">
          <Tag class="h-4 w-4" />
          <span>Category: {{ article.category }}</span>
        </div>
        <Badge v-if="canManageNews" :variant="statusConfig[article.status].variant" :class="statusConfig[article.status].color">
          {{ statusConfig[article.status].label }}
        </Badge>
        <Badge v-if="article.is_featured" variant="default" class="bg-yellow-100 text-yellow-800 border-yellow-200">
          <Star class="h-3 w-3 mr-1 fill-yellow-500 text-yellow-500" />
          Featured
        </Badge>
      </div>

      <!-- Article Content with Image on Right -->
      <div class="flex flex-col lg:flex-row gap-6 w-full">
        <!-- Content Section -->
        <div class="flex-1 bg-card rounded-lg border shadow-sm overflow-hidden">
          <div class="p-4 sm:p-6 md:p-8">
            <div 
              class="prose prose-sm sm:prose-lg max-w-none"
              v-html="article.content"
            ></div>
          </div>
        </div>

        <!-- Image Section - Square and positioned on right -->
        <div v-if="article.image_url" class="lg:w-80 xl:w-96 flex-shrink-0">
          <div class="bg-card rounded-lg border shadow-sm overflow-hidden">
            <div class="aspect-square w-full overflow-hidden">
              <img
                :src="article.image_url"
                :alt="article.title"
                class="w-full h-full object-cover"
              />
            </div>
            <!-- Optional image caption -->
            <div class="p-4 text-center text-sm text-muted-foreground border-t">
              Featured image for "{{ article.title }}"
            </div>
          </div>
        </div>
      </div>

      <!-- Admin Actions Footer -->
      <div v-if="canManageNews" class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-4 bg-muted/50 rounded-lg">
        <div class="flex items-center space-x-2">
          <span class="text-sm text-muted-foreground">Quick Actions:</span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <Button 
            v-if="article.status !== 'published'" 
            variant="outline" 
            size="sm"
            @click="handleStatusChange('published')"
            class="text-xs sm:text-sm"
          >
            <Eye class="h-4 w-4 mr-1 sm:mr-2" />
            Publish
          </Button>
          <Button 
            v-if="article.status === 'published'" 
            variant="outline" 
            size="sm"
            @click="handleStatusChange('draft')"
            class="text-xs sm:text-sm"
          >
            <Eye class="h-4 w-4 mr-1 sm:mr-2" />
            Unpublish
          </Button>
          <Button 
            variant="outline" 
            size="sm"
            @click="handleFeatureToggle"
            class="text-xs sm:text-sm"
          >
            <Star class="h-4 w-4 mr-1 sm:mr-2" :class="article.is_featured ? 'fill-yellow-500 text-yellow-500' : ''" />
            {{ article.is_featured ? 'Unfeature' : 'Feature' }}
          </Button>
          <Button 
            variant="outline" 
            size="sm"
            @click="handleStatusChange('archived')"
            class="text-xs sm:text-sm"
          >
            <Tag class="h-4 w-4 mr-1 sm:mr-2" />
            Archive
          </Button>
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
            "{{ article.title }}" and remove it from our servers.
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

.prose h1 {
  font-size: 1.875rem;
  font-weight: bold;
  margin-top: 2rem;
  margin-bottom: 1rem;
}

.prose h2 {
  font-size: 1.5rem;
  font-weight: bold;
  margin-top: 1.5rem;
  margin-bottom: 0.75rem;
}

.prose h3 {
  font-size: 1.25rem;
  font-weight: bold;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
}

.prose p {
  margin-bottom: 1rem;
}

.prose ul, .prose ol {
  margin-bottom: 1rem;
  padding-left: 1.5rem;
}

.prose li {
  margin-bottom: 0.5rem;
}

.prose blockquote {
  border-left: 4px solid hsl(var(--muted-foreground));
  padding-left: 1rem;
  font-style: italic;
  margin: 1rem 0;
}

.prose img {
  border-radius: 0.5rem;
  margin: 1rem 0;
}

.prose a {
  color: hsl(var(--primary));
  text-decoration: underline;
}

@media (max-width: 640px) {
  .prose h1 {
    font-size: 1.5rem;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
  }

  .prose h2 {
    font-size: 1.25rem;
    margin-top: 1.25rem;
    margin-bottom: 0.5rem;
  }

  .prose h3 {
    font-size: 1.125rem;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
  }
}

/* Ensure the image container maintains square aspect ratio */
.aspect-square {
  aspect-ratio: 1 / 1;
}

/* Responsive adjustments for smaller screens */
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