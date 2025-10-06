<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import { X, Upload } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'News Management',
    href: '/news',
  },
  {
    title: 'Create Article',
    href: '/news/create',
  },
];

// Form data
const form = ref({
  title: '',
  excerpt: '',
  content: '',
  category: '',
  published_at: '',
  status: 'draft',
  is_featured: false,
});

const loading = ref(false);
const errors = ref<Record<string, string>>({});
const imagePreview = ref<string | null>(null);
const imageFile = ref<File | null>(null);

// Categories from backend
const categories = [
  'Business', 'Finance', 'Events', 'Partnerships', 
  'Sustainability', 'Company News', 'Announcement', 
  'Update', 'Event', 'Maintenance'
];

// Handle image selection
const handleImageChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    const file = target.files[0];
    imageFile.value = file;
    
    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
  }
};

// Remove selected image
const removeImage = () => {
  imageFile.value = null;
  imagePreview.value = null;
  // Reset file input
  const fileInput = document.getElementById('image') as HTMLInputElement;
  if (fileInput) {
    fileInput.value = '';
  }
};

// Validation function
const validateForm = () => {
  const newErrors: Record<string, string> = {};

  if (!form.value.title.trim()) {
    newErrors.title = 'Title is required.';
  } else if (form.value.title.length < 2) {
    newErrors.title = 'Title must be at least 2 characters.';
  } else if (form.value.title.length > 255) {
    newErrors.title = 'Title must not exceed 255 characters.';
  }

  if (form.value.excerpt.length > 500) {
    newErrors.excerpt = 'Excerpt must not exceed 500 characters.';
  }

  if (!form.value.content.trim()) {
    newErrors.content = 'Content is required.';
  } else if (form.value.content.length < 10) {
    newErrors.content = 'Content must be at least 10 characters.';
  }

  if (!form.value.category) {
    newErrors.category = 'Category is required.';
  }

  return newErrors;
};

// Handle form submission
const submit = () => {
  const validationErrors = validateForm();
  
  if (Object.keys(validationErrors).length > 0) {
    errors.value = validationErrors;
    return;
  }

  loading.value = true;
  errors.value = {};

  // Create FormData to handle file upload
  const formData = new FormData();
  formData.append('title', form.value.title);
  formData.append('excerpt', form.value.excerpt);
  formData.append('content', form.value.content);
  formData.append('category', form.value.category);
  formData.append('status', form.value.status);
  formData.append('is_featured', form.value.is_featured ? '1' : '0');
  
  if (form.value.published_at) {
    formData.append('published_at', form.value.published_at);
  }
  
  if (imageFile.value) {
    formData.append('image', imageFile.value);
  }

  router.post('/news', formData, {
    onSuccess: () => {
      toast.success('Article created successfully!');
    },
    onError: (err) => {
      errors.value = err as Record<string, string>;
      toast.error('Failed to create article. Please check the form for errors.');
    },
    onFinish: () => {
      loading.value = false;
    },
  });
};

// Cancel and go back
const cancel = () => {
  router.visit('/news');
};

// Character count for excerpt
const excerptCount = computed(() => form.value.excerpt.length);
</script>

<template>
  <Head title="Create News Article" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full p-4 sm:p-6">
      <div class="w-full max-w-none mx-auto">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
          <div class="flex-1 min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-foreground truncate">Create News Article</h1>
            <p class="text-muted-foreground mt-1">Add a new article to your news section</p>
          </div>
        </div>

        <!-- Error summary -->
        <div v-if="Object.keys(errors).length" class="mb-6 p-4 bg-destructive/15 border border-destructive/50 text-destructive rounded-lg">
          <h3 class="font-semibold mb-2">Please fix the following errors:</h3>
          <ul class="list-disc list-inside space-y-1">
            <li v-for="(error, field) in errors" :key="field">
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
                    :class="errors.title ? 'border-destructive' : ''"
                    class="w-full"
                  />
                  <p v-if="errors.title" class="text-sm text-destructive">{{ errors.title }}</p>
                </div>

                <!-- Excerpt -->
                <div class="space-y-2">
                  <Label for="excerpt" class="text-sm font-medium">Excerpt</Label>
                  <Textarea
                    id="excerpt"
                    v-model="form.excerpt"
                    placeholder="Brief description of the article (optional)"
                    :class="errors.excerpt ? 'border-destructive' : ''"
                    rows="3"
                    class="w-full resize-vertical min-h-[100px]"
                  />
                  <div class="flex justify-between items-center">
                    <p v-if="errors.excerpt" class="text-sm text-destructive">{{ errors.excerpt }}</p>
                    <p class="text-xs text-muted-foreground ml-auto" :class="excerptCount > 500 ? 'text-destructive' : ''">
                      {{ excerptCount }}/500 characters
                    </p>
                  </div>
                </div>

                <!-- Category -->
                <div class="space-y-2">
                  <Label for="category" class="text-sm font-medium">Category *</Label>
                  <Select v-model="form.category" :class="errors.category ? 'border-destructive' : ''">
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
                  <p v-if="errors.category" class="text-sm text-destructive">{{ errors.category }}</p>
                </div>
              </div>
            </div>

            <!-- Content Card -->
            <div class="bg-card rounded-lg border shadow-sm">
              <div class="p-4 sm:p-6 border-b">
                <h2 class="text-lg font-semibold">Content</h2>
              </div>
              <div class="p-4 sm:p-6">
                <div class="space-y-2">
                  <Label for="content" class="text-sm font-medium">Content *</Label>
                  <Textarea
                    id="content"
                    v-model="form.content"
                    placeholder="Write your article content here..."
                    :class="errors.content ? 'border-destructive' : ''"
                    rows="15"
                    class="w-full resize-vertical min-h-[300px]"
                  />
                  <p v-if="errors.content" class="text-sm text-destructive">{{ errors.content }}</p>
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
                    </SelectContent>
                  </Select>
                </div>

                <!-- Published Date -->
                <div class="space-y-2">
                  <Label for="published_at" class="text-sm font-medium">Publish Date</Label>
                  <Input
                    id="published_at"
                    v-model="form.published_at"
                    type="datetime-local"
                    class="w-full"
                  />
                  <p class="text-xs text-muted-foreground">
                    Leave empty to use current date when publishing
                  </p>
                </div>

                <!-- Featured -->
                <div class="space-y-2">
                  <div class="flex items-center space-x-2">
                    <Checkbox
                      id="is_featured"
                      v-model:checked="form.is_featured"
                    />
                    <Label for="is_featured" class="text-sm font-medium cursor-pointer">
                      Feature this article
                    </Label>
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
                      type="file"
                      accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                      @change="handleImageChange"
                      :class="errors.image ? 'border-destructive' : ''"
                      class="w-full cursor-pointer"
                    />
                    <p class="text-xs text-muted-foreground">
                      Supported formats: JPEG, PNG, JPG, GIF, WEBP. Max size: 10MB
                    </p>
                    <p v-if="errors.image" class="text-sm text-destructive">{{ errors.image }}</p>
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
                        <X class="h-4 w-4" />
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
                  <div v-if="!imagePreview" class="border-2 border-dashed border-muted-foreground/25 rounded-lg p-6 text-center">
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
                    type="submit"
                    @click="submit"
                    :disabled="loading"
                    class="w-full"
                    size="lg"
                  >
                    <span v-if="loading">Creating...</span>
                    <span v-else>Create Article</span>
                  </Button>
                  
                  <Button
                    type="button"
                    variant="outline"
                    @click="cancel"
                    :disabled="loading"
                    class="w-full"
                  >
                    Cancel
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
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