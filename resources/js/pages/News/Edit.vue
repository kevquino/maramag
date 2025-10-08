<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save, Upload, Trash2, Eye, Star } from 'lucide-vue-next';
import { ref, computed } from 'vue';
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
import { Toggle } from '@/components/ui/toggle';
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

// Define categories based on the migration enum
const categories = [
  'Business',
  'Finance', 
  'Events', 
  'Partnerships', 
  'Sustainability', 
  'Company News',
  'Announcement',
  'Update',
  'Event',
  'Maintenance'
] as const;

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
  _method: 'PUT',
  title: props.article.title,
  excerpt: props.article.excerpt,
  content: props.article.content,
  category: props.article.category,
  status: props.article.status,
  is_featured: props.article.is_featured,
  image: null as File | null,
  remove_existing_image: false,
});

// Image preview
const imagePreview = ref<string | null>(props.article.image_url);
const imageFileInput = ref<HTMLInputElement>();

// Track if we had an initial image
const hadInitialImage = ref(!!props.article.image_url);

// Dialog states
const saveDialogOpen = ref(false);
const cancelDialogOpen = ref(false);
const deleteDialogOpen = ref(false);
const deleting = ref(false);

// Check if form has unsaved changes
const hasUnsavedChanges = computed(() => {
  return form.title !== props.article.title ||
         form.excerpt !== props.article.excerpt ||
         form.content !== props.article.content ||
         form.category !== props.article.category ||
         form.status !== props.article.status ||
         form.is_featured !== props.article.is_featured ||
         form.image !== null ||
         form.remove_existing_image;
});

// Get article summary for confirmation dialogs
const articleSummary = computed(() => {
  return {
    title: form.title || 'Untitled Article',
    category: form.category || 'No category',
    status: form.status,
    isFeatured: form.is_featured,
    hasImage: !!form.image || !!imagePreview.value || (!!props.article.image_url && !form.remove_existing_image)
  };
});

// Handle image selection
const handleImageSelect = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  
  if (file) {
    form.image = file;
    form.remove_existing_image = false; // Reset remove flag when new image is selected
    
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
  form.remove_existing_image = true; // Set flag to remove existing image
  
  // Clear preview
  if (hadInitialImage.value) {
    // If there was an initial image, keep the preview but show a "removed" state
    imagePreview.value = null;
  } else {
    imagePreview.value = null;
  }
  
  if (imageFileInput.value) {
    imageFileInput.value.value = '';
  }
};

// Handle form submission - Use POST with _method=PUT
const submit = () => {
  form.post(`/news/${props.article.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Article updated successfully!');
    },
    onError: (errors) => {
      toast.error('Failed to update article. Please check the form.');
    },
  });
};

// Confirm save
const confirmSave = () => {
  saveDialogOpen.value = false;
  submit();
};

// Open save confirmation
const openSaveDialog = () => {
  if (validateForm()) {
    saveDialogOpen.value = true;
  }
};

// Validate form before submission
const validateForm = (): boolean => {
  if (!form.title.trim()) {
    toast.error('Please enter a title for the article.');
    return false;
  }
  if (!form.category) {
    toast.error('Please select a category.');
    return false;
  }
  if (!form.content.trim()) {
    toast.error('Please enter content for the article.');
    return false;
  }
  return true;
};

// Open cancel confirmation
const openCancelDialog = () => {
  if (hasUnsavedChanges.value) {
    cancelDialogOpen.value = true;
  } else {
    cancel();
  }
};

// Confirm cancel
const confirmCancel = () => {
  cancelDialogOpen.value = false;
  cancel();
};

// Cancel and go back
const cancel = () => {
  router.visit('/news');
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
    <div class="w-full p-4 sm:p-6">
      <div class="w-full max-w-none mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
          <div class="flex-1 min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-foreground truncate">Edit News Article</h1>
            <p class="text-muted-foreground mt-1">Update the news article details</p>
          </div>
        </div>

        <!-- Error summary -->
        <div v-if="Object.keys(form.errors).length" class="mb-6 p-4 bg-destructive/15 border border-destructive/50 text-destructive rounded-lg">
          <h3 class="font-semibold mb-2">Please fix the following errors:</h3>
          <ul class="list-disc list-inside space-y-1">
            <li v-for="(error, field) in form.errors" :key="field">
              {{ error }}
            </li>
          </ul>
        </div>

        <!-- Main Form Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
          <!-- Left Column - Main Content -->
          <div class="xl:col-span-2 space-y-6">
            <!-- Basic Information Card -->
            <div class="bg-card rounded-lg border shadow-sm">
              <div class="p-4 sm:p-6 border-b">
                <h2 class="text-lg font-semibold">Basic Information</h2>
              </div>
              <div class="p-4 sm:p-6 space-y-6">
                <!-- Title -->
                <div class="space-y-2">
                  <Label for="title" class="text-sm font-medium">Title *</Label>
                  <Input
                    id="title"
                    v-model="form.title"
                    type="text"
                    placeholder="Enter article title"
                    :class="form.errors.title ? 'border-destructive' : ''"
                    class="w-full"
                  />
                  <p v-if="form.errors.title" class="text-sm text-destructive">{{ form.errors.title }}</p>
                </div>

                <!-- Excerpt -->
                <div class="space-y-2">
                  <Label for="excerpt" class="text-sm font-medium">Excerpt</Label>
                  <Textarea
                    id="excerpt"
                    v-model="form.excerpt"
                    placeholder="Brief description of the article (optional)"
                    :class="form.errors.excerpt ? 'border-destructive' : ''"
                    rows="3"
                    class="w-full resize-vertical min-h-[100px]"
                  />
                  <p v-if="form.errors.excerpt" class="text-sm text-destructive">{{ form.errors.excerpt }}</p>
                </div>

                <!-- Content -->
                <div class="space-y-2">
                  <Label for="content" class="text-sm font-medium">Content *</Label>
                  <Textarea
                    id="content"
                    v-model="form.content"
                    placeholder="Write your article content here..."
                    :class="form.errors.content ? 'border-destructive' : ''"
                    rows="15"
                    class="w-full resize-vertical min-h-[300px]"
                  />
                  <p v-if="form.errors.content" class="text-sm text-destructive">{{ form.errors.content }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column - Sidebar -->
          <div class="space-y-6">
            <!-- Settings Card -->
            <div class="bg-card rounded-lg border shadow-sm">
              <div class="p-4 sm:p-6 border-b">
                <h2 class="text-lg font-semibold">Settings</h2>
              </div>
              <div class="p-4 sm:p-6 space-y-6">
                <!-- Status -->
                <div class="space-y-2">
                  <Label for="status" class="text-sm font-medium">Status *</Label>
                  <Select v-model="form.status">
                    <SelectTrigger class="w-full">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="draft">
                        <div class="flex items-center space-x-2">
                          <Badge variant="outline">Draft</Badge>
                        </div>
                      </SelectItem>
                      <SelectItem value="published">
                        <div class="flex items-center space-x-2">
                          <Badge variant="default">Published</Badge>
                        </div>
                      </SelectItem>
                      <SelectItem value="archived">
                        <div class="flex items-center space-x-2">
                          <Badge variant="secondary">Archived</Badge>
                        </div>
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <!-- Category -->
                <div class="space-y-2">
                  <Label for="category" class="text-sm font-medium">Category *</Label>
                  <Select v-model="form.category" :class="form.errors.category ? 'border-destructive' : ''">
                    <SelectTrigger class="w-full">
                      <SelectValue placeholder="Select a category" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem
                        v-for="category in categories"
                        :key="category"
                        :value="category"
                      >
                        {{ category }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                  <p v-if="form.errors.category" class="text-sm text-destructive">{{ form.errors.category }}</p>
                </div>

                <!-- Featured Toggle -->
                <div class="space-y-2">
                  <Label class="text-sm font-medium">Featured Article</Label>
                  <div class="flex items-center justify-between p-1 border rounded-lg bg-muted/50">
                    <div class="flex items-center space-x-2">
                      <Toggle 
                        :pressed="form.is_featured"
                        @click="form.is_featured = !form.is_featured"
                        aria-label="Toggle featured"
                        :class="form.is_featured ? 'bg-primary text-primary-foreground' : ''"
                      >
                        <Star class="h-4 w-4" :class="form.is_featured ? 'text-yellow-500 fill-yellow-500' : 'text-muted-foreground'" />
                      </Toggle>
                      <span class="text-sm">Mark as featured</span>
                    </div>
                  </div>
                  <p class="text-xs text-muted-foreground">
                    Featured articles will be highlighted with a star and appear first in listings
                  </p>
                </div>
              </div>
            </div>

            <!-- Media Card -->
            <div class="bg-card rounded-lg border shadow-sm">
              <div class="p-4 sm:p-6 border-b">
                <h2 class="text-lg font-semibold">Featured Image</h2>
              </div>
              <div class="p-4 sm:p-6 space-y-4">
                <!-- Image Upload Area -->
                <div class="space-y-4">
                  <!-- File Input -->
                  <div class="space-y-2">
                    <Label for="image" class="text-sm font-medium">Upload Image</Label>
                    <Input
                      id="image"
                      ref="imageFileInput"
                      type="file"
                      accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                      @change="handleImageSelect"
                      :class="form.errors.image ? 'border-destructive' : ''"
                      class="w-full cursor-pointer"
                    />
                    <p class="text-xs text-muted-foreground">
                      Supported formats: JPEG, PNG, JPG, GIF, WEBP. Max size: 10MB
                    </p>
                    <p v-if="form.errors.image" class="text-sm text-destructive">{{ form.errors.image }}</p>
                  </div>

                  <!-- Image Preview -->
                  <div v-if="imagePreview" class="space-y-3">
                    <div class="relative group">
                      <img 
                        :src="imagePreview" 
                        alt="Featured image preview" 
                        class="w-full h-48 sm:h-64 object-cover rounded-lg border"
                      />
                      <Button
                        @click="removeImage"
                        variant="destructive"
                        size="sm"
                        class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity"
                      >
                        <Trash2 class="h-4 w-4" />
                      </Button>
                    </div>
                    <div class="flex items-center justify-between text-xs text-muted-foreground">
                      <span>Preview</span>
                      <Button
                        variant="outline"
                        size="sm"
                        @click="removeImage"
                        class="h-7 text-xs"
                      >
                        Remove Image
                      </Button>
                    </div>
                  </div>

                  <!-- Upload Prompt (when no image) -->
                  <div v-if="!imagePreview && !form.remove_existing_image && props.article.image_url" class="space-y-3">
                    <div class="relative">
                      <img 
                        :src="props.article.image_url" 
                        alt="Current featured image" 
                        class="w-full h-48 sm:h-64 object-cover rounded-lg border"
                      />
                      <Button
                        @click="removeImage"
                        variant="destructive"
                        size="sm"
                        class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity"
                      >
                        <Trash2 class="h-4 w-4" />
                      </Button>
                    </div>
                    <div class="flex items-center justify-between text-xs text-muted-foreground">
                      <span>Current Image</span>
                      <Button
                        variant="outline"
                        size="sm"
                        @click="removeImage"
                        class="h-7 text-xs"
                      >
                        Remove Image
                      </Button>
                    </div>
                  </div>

                  <div v-if="!imagePreview && (!props.article.image_url || form.remove_existing_image)" class="border-2 border-dashed border-muted-foreground/25 rounded-lg p-6 text-center">
                    <Upload class="h-8 w-8 text-muted-foreground mx-auto mb-2" />
                    <p class="text-sm font-medium text-foreground mb-1">No image selected</p>
                    <p class="text-xs text-muted-foreground">
                      Choose a file above to upload
                    </p>
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
                    @click="openSaveDialog"
                    :disabled="form.processing"
                    class="w-full"
                    size="lg"
                  >
                    <Save class="h-4 w-4 mr-2" />
                    <span v-if="form.processing">Saving...</span>
                    <span v-else>Save Changes</span>
                  </Button>
                  
                  <Button
                    type="button"
                    variant="outline"
                    @click="openCancelDialog"
                    :disabled="form.processing"
                    class="w-full"
                  >
                    <ArrowLeft class="h-4 w-4 mr-2" />
                    Cancel
                  </Button>

                  <Button
                    type="button"
                    variant="destructive"
                    @click="openDeleteDialog"
                    :disabled="form.processing"
                    class="w-full"
                  >
                    <Trash2 class="h-4 w-4 mr-2" />
                    Delete Article
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Save Changes Confirmation Dialog -->
    <AlertDialog v-model:open="saveDialogOpen">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Save Changes?</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to save the changes to this news article?
            <div class="mt-4 p-3 bg-muted rounded-lg space-y-2">
              <div class="flex justify-between">
                <span class="font-medium">Title:</span>
                <span>{{ articleSummary.title }}</span>
              </div>
              <div class="flex justify-between">  
                <span class="font-medium">Category:</span>
                <span>{{ articleSummary.category }}</span>
              </div>
              <div class="flex justify-between">
                <span class="font-medium">Status:</span>
                <Badge :variant="articleSummary.status === 'published' ? 'default' : articleSummary.status === 'draft' ? 'outline' : 'secondary'">
                  {{ articleSummary.status }}
                </Badge>
              </div>
              <div class="flex justify-between">
                <span class="font-medium">Featured:</span>
                <span>{{ articleSummary.isFeatured ? 'Yes' : 'No' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="font-medium">Image:</span>
                <span>{{ articleSummary.hasImage ? 'Yes' : 'No' }}</span>
              </div>
            </div>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="saveDialogOpen = false">
            Continue Editing
          </AlertDialogCancel>
          <AlertDialogAction @click="confirmSave">
            Save Changes
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- Cancel Confirmation Dialog -->
    <AlertDialog v-model:open="cancelDialogOpen">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Cancel Changes?</AlertDialogTitle>
          <AlertDialogDescription>
            <span v-if="hasUnsavedChanges">
              You have unsaved changes. If you cancel now, all your changes will be lost.
            </span>
            <span v-else>
              This will cancel the editing and return you to the news list.
            </span>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="cancelDialogOpen = false">
            Continue Editing
          </AlertDialogCancel>
          <AlertDialogAction @click="confirmCancel">
            Cancel Changes
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

<style scoped>
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