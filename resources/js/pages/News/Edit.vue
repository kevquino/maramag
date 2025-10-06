<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save, Upload, Trash2, List, Eye } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Label } from '@/components/ui/label';
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
    title: 'Edit Article',
    href: `/news/${props.article.id}/edit`,
  },
];

// Form handling - Use POST method with _method field
const form = useForm({
  _method: 'PUT', // This tells Laravel to treat it as PUT
  title: props.article.title,
  excerpt: props.article.excerpt,
  content: props.article.content,
  category: props.article.category,
  status: props.article.status,
  image: null as File | null,
  remove_image: false, // New field to indicate image removal
});

// Image preview
const imagePreview = ref<string | null>(props.article.image_url);
const imageFileInput = ref<HTMLInputElement>();
const hasRemovedImage = ref(false); // Track if image was removed

// Dialog states
const deleteDialogOpen = ref(false);
const saveDialogOpen = ref(false);
const deleting = ref(false);
const saving = ref(false);

// Handle image selection
const handleImageSelect = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  
  if (file) {
    form.image = file;
    form.remove_image = false; // Reset remove flag when new image is selected
    hasRemovedImage.value = false;
    
    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
  }
};

// Remove image
const removeImage = () => {
  form.image = null;
  form.remove_image = true; // Set flag to remove image on server
  imagePreview.value = null;
  hasRemovedImage.value = true;
  
  if (imageFileInput.value) {
    imageFileInput.value.value = '';
  }
};

// Handle form submission - Use POST with _method=PUT
const submit = () => {
  saving.value = true;
  form.post(`/news/${props.article.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Article updated successfully!');
      // Reset the remove_image flag after successful submission
      form.remove_image = false;
      hasRemovedImage.value = false;
      saving.value = false;
      saveDialogOpen.value = false;
    },
    onError: (errors) => {
      toast.error('Failed to update article. Please check the form.');
      saving.value = false;
      saveDialogOpen.value = false;
    },
  });
};

// Open save confirmation dialog
const openSaveDialog = () => {
  saveDialogOpen.value = true;
};

// Handle back to news list
const handleBackToList = () => {
  router.get('/news');
};

// Handle back to show page
const handleBackToArticle = () => {
  router.get(`/news/${props.article.id}`);
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
  <Head title="Edit News Article" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 w-full">
      <!-- Header Section -->
      <div class="flex flex-col w-full">
        <div class="w-full">
          <h1 class="text-2xl md:text-3xl font-bold text-foreground">Edit Article</h1>
          <p class="text-muted-foreground mt-2">Update the news article details</p>
        </div>
      </div>

      <!-- Edit Form -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6 w-full">
        <!-- Main Form -->
        <div class="lg:col-span-2 space-y-4 md:space-y-6">
          <!-- Basic Information Card -->
          <div class="bg-card rounded-lg border shadow-sm p-4 md:p-6">
            <h2 class="text-lg md:text-xl font-semibold mb-4">Basic Information</h2>
            
            <div class="space-y-4">
              <!-- Title -->
              <div class="space-y-2">
                <Label for="title">Title</Label>
                <Input
                  id="title"
                  v-model="form.title"
                  type="text"
                  placeholder="Enter article title"
                  :class="{ 'border-destructive': form.errors.title }"
                />
                <p v-if="form.errors.title" class="text-sm text-destructive">
                  {{ form.errors.title }}
                </p>
              </div>

              <!-- Excerpt -->
              <div class="space-y-2">
                <Label for="excerpt">Excerpt</Label>
                <Textarea
                  id="excerpt"
                  v-model="form.excerpt"
                  placeholder="Brief description of the article"
                  :class="{ 'border-destructive': form.errors.excerpt }"
                  rows="3"
                />
                <p v-if="form.errors.excerpt" class="text-sm text-destructive">
                  {{ form.errors.excerpt }}
                </p>
              </div>

              <!-- Content -->
              <div class="space-y-2">
                <Label for="content">Content</Label>
                <Textarea
                  id="content"
                  v-model="form.content"
                  placeholder="Write your article content here..."
                  :class="{ 'border-destructive': form.errors.content }"
                  rows="12"
                />
                <p v-if="form.errors.content" class="text-sm text-destructive">
                  {{ form.errors.content }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4 md:space-y-6">
          <!-- Status & Category Card -->
          <div class="bg-card rounded-lg border shadow-sm p-4 md:p-6">
            <h2 class="text-lg md:text-xl font-semibold mb-4">Settings</h2>
            
            <div class="space-y-4">
              <!-- Category -->
              <div class="space-y-2">
                <Label for="category">Category</Label>
                <Input
                  id="category"
                  v-model="form.category"
                  type="text"
                  placeholder="e.g., Technology, Sports"
                  :class="{ 'border-destructive': form.errors.category }"
                />
                <p v-if="form.errors.category" class="text-sm text-destructive">
                  {{ form.errors.category }}
                </p>
              </div>

              <!-- Status -->
              <div class="space-y-2">
                <Label for="status">Status</Label>
                <Select v-model="form.status">
                  <SelectTrigger :class="{ 'border-destructive': form.errors.status }">
                    <SelectValue placeholder="Select status" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="draft">Draft</SelectItem>
                    <SelectItem value="published">Published</SelectItem>
                    <SelectItem value="archived">Archived</SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="form.errors.status" class="text-sm text-destructive">
                  {{ form.errors.status }}
                </p>
              </div>
            </div>
          </div>

          <!-- Featured Image Card -->
          <div class="bg-card rounded-lg border shadow-sm p-4 md:p-6">
            <h2 class="text-lg md:text-xl font-semibold mb-4">Featured Image</h2>
            
            <div class="space-y-4">
              <!-- Image Preview -->
              <div v-if="imagePreview && !hasRemovedImage" class="relative">
                <img
                  :src="imagePreview"
                  alt="Featured image preview"
                  class="w-full h-32 md:h-48 object-cover rounded-lg"
                />
                <Button
                  type="button"
                  variant="destructive"
                  size="sm"
                  class="absolute top-2 right-2"
                  @click="removeImage"
                >
                  <Trash2 class="h-4 w-4" />
                </Button>
              </div>

              <!-- No Image State -->
              <div v-if="!imagePreview || hasRemovedImage" class="border-2 border-dashed border-muted-foreground/25 rounded-lg p-6 text-center">
                <Upload class="h-8 w-8 mx-auto text-muted-foreground mb-2" />
                <p class="text-sm text-muted-foreground">No featured image</p>
              </div>

              <!-- Image Upload -->
              <div class="space-y-2">
                <Label for="image">Upload Image</Label>
                <Input
                  id="image"
                  ref="imageFileInput"
                  type="file"
                  accept="image/*"
                  @change="handleImageSelect"
                  :class="{ 'border-destructive': form.errors.image }"
                />
                <p class="text-sm text-muted-foreground">
                  Recommended size: 1200x630px. Max 2MB.
                </p>
                <p v-if="form.errors.image" class="text-sm text-destructive">
                  {{ form.errors.image }}
                </p>
              </div>
            </div>
          </div>

          <!-- Article Info -->
          <div class="bg-card rounded-lg border shadow-sm p-4 md:p-6">
            <h2 class="text-lg md:text-xl font-semibold mb-4">Article Information</h2>
            
            <div class="space-y-3 text-sm">
              <div class="flex justify-between">
                <span class="text-muted-foreground">Author:</span>
                <span>{{ article.author.name }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">Created:</span>
                <span>{{ new Date(article.created_at).toLocaleDateString() }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">Last Updated:</span>
                <span>{{ new Date(article.updated_at).toLocaleDateString() }}</span>
              </div>
              <div v-if="article.published_at" class="flex justify-between">
                <span class="text-muted-foreground">Published:</span>
                <span>{{ new Date(article.published_at).toLocaleDateString() }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-muted-foreground">Featured:</span>
                <Badge :variant="article.is_featured ? 'default' : 'secondary'">
                  {{ article.is_featured ? 'Yes' : 'No' }}
                </Badge>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-muted-foreground">Image:</span>
                <Badge :variant="(article.image_url && !hasRemovedImage) ? 'default' : 'secondary'">
                  {{ (article.image_url && !hasRemovedImage) ? 'Yes' : 'No' }}
                </Badge>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons - Normal size at bottom -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-8 p-4 bg-card rounded-lg border shadow-sm">
        <!-- Navigation Buttons -->
        <div class="flex flex-wrap gap-2">
          <!-- Back to News List Button -->
          <Button variant="outline" size="sm" @click="handleBackToList">
            <ArrowLeft class="h-4 w-4 mr-2" />
            Back to News
          </Button>
          
          <!-- Back to Article Button -->
          <Button variant="outline" size="sm" @click="handleBackToArticle">
            <Eye class="h-4 w-4 mr-2" />
            View Article
          </Button>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-2">
          <!-- Save Button -->
          <Button 
            size="sm" 
            @click="openSaveDialog" 
            :disabled="form.processing"
          >
            <Save class="h-4 w-4 mr-2" />
            Save Changes
          </Button>
          
          <!-- Delete Button -->
          <Button variant="destructive" size="sm" @click="openDeleteDialog">
            <Trash2 class="h-4 w-4 mr-2" />
            Delete
          </Button>
        </div>
      </div>
    </div>

    <!-- Save Confirmation Dialog -->
    <AlertDialog v-model:open="saveDialogOpen">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Confirm Save</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to save these changes to the article "{{ article.title }}"?
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="saving" @click="saveDialogOpen = false">
            Cancel
          </AlertDialogCancel>
          <AlertDialogAction 
            @click="submit"
            class="bg-primary text-primary-foreground hover:bg-primary/90"
            :disabled="saving"
          >
            <div v-if="saving" class="flex items-center space-x-2">
              <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-white"></div>
              <span>Saving...</span>
            </div>
            <span v-else>Yes, Save Changes</span>
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

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