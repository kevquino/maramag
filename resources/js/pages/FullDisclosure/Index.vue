<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from "vue"
import { router, Head, Link, usePage } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import { Eye, Download, Edit, Trash2, FileText, Calendar, User, Filter, Search, X } from "lucide-vue-next"

import { Button } from "@/components/ui/button"
import { Badge } from "@/components/ui/badge"
import { Input } from "@/components/ui/input"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog"
import {
  Item,
  ItemActions,
  ItemContent,
  ItemDescription,
  ItemTitle,
} from "@/components/ui/item"

interface FullDisclosureDocument {
  id: string
  title: string
  category: string
  description: string | null
  file_path: string
  file_name: string
  file_size: string | null
  file_type: string | null
  is_published: boolean
  created_at: string
  updated_at: string
  user: {
    id: string
    name: string
    email: string
  }
}

interface Props {
  documents: {
    data: FullDisclosureDocument[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  groupedDocuments: Record<string, FullDisclosureDocument[]>
  categories: Record<string, string>
  filters?: {
    search?: string
    category?: string
  }
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: dashboard().url,
  },
  {
    title: 'Full Disclosure',
    href: '/full-disclosure',
  },
]

// Reactive data
const searchQuery = ref(props.filters?.search || '')
const categoryFilter = ref(props.filters?.category || '')
const deleteDialogOpen = ref(false)
const documentToDelete = ref<FullDisclosureDocument | null>(null)
const deleting = ref(false)

// Computed properties for grouped data
const categoryGroups = computed(() => {
  const groups = [
    {
      title: 'Approved Budget',
      category: 'approved_budget',
      items: props.groupedDocuments['approved_budget'] || []
    },
    {
      title: 'Annual Procurement Plan',
      category: 'procurement_plan',
      items: props.groupedDocuments['procurement_plan'] || []
    },
    {
      title: 'Gender and Development',
      category: 'gender_development',
      items: props.groupedDocuments['gender_development'] || []
    },
    {
      title: 'Full Disclosure Policy',
      category: 'full_disclosure_policy',
      items: props.groupedDocuments['full_disclosure_policy'] || []
    },
    {
      title: 'AUDIT REPORT',
      category: 'audit_report',
      items: props.groupedDocuments['audit_report'] || []
    },
    {
      title: 'EXECUTIVE SUMMARY',
      category: 'executive_summary',
      items: props.groupedDocuments['executive_summary'] || []
    },
    {
      title: 'STATEMENT OF INDEBTEDNESS, PAYMENTS AND BALANCES',
      category: 'statement_indebtedness',
      items: props.groupedDocuments['statement_indebtedness'] || []
    }
  ]

  return groups.filter(group => group.items.length > 0)
})

// Search and filter handlers
const handleSearch = () => {
  router.get('/full-disclosure', {
    search: searchQuery.value,
    category: categoryFilter.value
  }, {
    preserveState: true,
    replace: true,
  })
}

const clearFilters = () => {
  searchQuery.value = ''
  categoryFilter.value = ''
  router.get('/full-disclosure', {}, {
    preserveState: true,
    replace: true,
  })
}

// Document actions
const viewDocument = (document: FullDisclosureDocument) => {
  window.open(`/storage/${document.file_path}`, '_blank')
}

const downloadDocument = (document: FullDisclosureDocument) => {
  router.get(`/full-disclosure/${document.id}/download`)
}

const editDocument = (document: FullDisclosureDocument) => {
  router.get(`/full-disclosure/${document.id}/edit`)
}

const openDeleteDialog = (document: FullDisclosureDocument) => {
  documentToDelete.value = document
  deleteDialogOpen.value = true
}

const deleteDocument = () => {
  if (!documentToDelete.value) return

  deleting.value = true
  
  router.delete(`/full-disclosure/${documentToDelete.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      deleteDialogOpen.value = false
      documentToDelete.value = null
      toast.success('Document deleted successfully.')
    },
    onError: () => {
      toast.error('Failed to delete document.')
    },
    onFinish: () => {
      deleting.value = false
    }
  })
}

// Utility functions
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getFileIcon = (fileType: string | null) => {
  if (!fileType) return FileText
  if (fileType.includes('pdf')) return FileText
  if (fileType.includes('word') || fileType.includes('document')) return FileText
  if (fileType.includes('excel') || fileType.includes('spreadsheet')) return FileText
  return FileText
}
</script>

<template>
  <Head title="Full Disclosure" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="text-center sm:text-left">
          <h1 class="text-3xl font-bold text-foreground">Full Disclosure Portal</h1>
          <p class="text-muted-foreground mt-2">Transparent access to government documents and reports</p>
        </div>
        
        <Link href="/full-disclosure/create" as="button" class="w-full sm:w-auto">
          <Button class="w-full sm:w-auto">
            <FileText class="h-4 w-4 mr-2" />
            Upload New Document
          </Button>
        </Link>
      </div>

      <!-- Filters Section -->
      <div class="bg-card rounded-lg border p-4">
        <div class="flex flex-col sm:flex-row gap-4">
          <!-- Search Input -->
          <div class="flex-1">
            <div class="relative">
              <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
              <Input
                v-model="searchQuery"
                placeholder="Search documents..."
                class="pl-10"
                @keyup.enter="handleSearch"
              />
            </div>
          </div>

          <!-- Category Filter -->
          <div class="w-full sm:w-64">
            <Select v-model="categoryFilter" @update:model-value="handleSearch">
              <SelectTrigger>
                <SelectValue placeholder="Filter by category" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="">All Categories</SelectItem>
                <SelectItem v-for="(label, value) in categories" :key="value" :value="value">
                  {{ label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-2">
            <Button @click="handleSearch" variant="default">
              <Filter class="h-4 w-4 mr-2" />
              Apply
            </Button>
            <Button @click="clearFilters" variant="outline">
              <X class="h-4 w-4 mr-2" />
              Clear
            </Button>
          </div>
        </div>
      </div>

      <!-- Documents by Category -->
      <div class="space-y-8">
        <div v-for="group in categoryGroups" :key="group.category" class="space-y-4">
          <!-- Category Header -->
          <div class="border-b pb-2">
            <h2 class="text-2xl font-semibold text-foreground">{{ group.title }}</h2>
            <p class="text-muted-foreground mt-1">
              {{ group.items.length }} document{{ group.items.length !== 1 ? 's' : '' }} available
            </p>
          </div>

          <!-- Documents List -->
          <div class="grid gap-4">
            <Item
              v-for="document in group.items"
              :key="document.id"
              variant="outline"
              class="hover:shadow-md transition-shadow"
            >
              <ItemContent class="flex-1 min-w-0">
                <div class="flex items-start justify-between">
                  <div class="flex-1 min-w-0">
                    <ItemTitle class="text-lg font-semibold truncate">
                      {{ document.title }}
                    </ItemTitle>
                    <ItemDescription class="mt-2 space-y-2">
                      <div class="flex items-center gap-4 text-sm text-muted-foreground flex-wrap">
                        <div class="flex items-center gap-1">
                          <Calendar class="h-3 w-3" />
                          <span>Uploaded: {{ formatDate(document.created_at) }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                          <User class="h-3 w-3" />
                          <span>By: {{ document.user.name }}</span>
                        </div>
                        <Badge v-if="document.file_size" variant="secondary">
                          {{ document.file_size }}
                        </Badge>
                        <Badge variant="outline">
                          {{ document.file_type || 'File' }}
                        </Badge>
                      </div>
                      <p v-if="document.description" class="text-sm text-muted-foreground mt-1">
                        {{ document.description }}
                      </p>
                    </ItemDescription>
                  </div>
                </div>
              </ItemContent>
              <ItemActions class="flex flex-col sm:flex-row gap-2">
                <Button variant="outline" size="sm" @click="viewDocument(document)">
                  <Eye class="h-4 w-4 mr-1" />
                  View
                </Button>
                <Button variant="outline" size="sm" @click="downloadDocument(document)">
                  <Download class="h-4 w-4 mr-1" />
                  Download
                </Button>
                <Button variant="outline" size="sm" @click="editDocument(document)">
                  <Edit class="h-4 w-4 mr-1" />
                  Edit
                </Button>
                <Button variant="destructive" size="sm" @click="openDeleteDialog(document)">
                  <Trash2 class="h-4 w-4 mr-1" />
                  Delete
                </Button>
              </ItemActions>
            </Item>
          </div>

          <!-- Empty State for Category -->
          <div v-if="group.items.length === 0" class="text-center py-8">
            <FileText class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
            <h3 class="text-lg font-medium text-muted-foreground">No documents found</h3>
            <p class="text-muted-foreground mt-1">No documents available in this category.</p>
          </div>
        </div>

        <!-- Empty State for All Categories -->
        <div v-if="categoryGroups.length === 0" class="text-center py-12">
          <FileText class="h-16 w-16 text-muted-foreground mx-auto mb-4" />
          <h3 class="text-xl font-medium text-muted-foreground">No documents found</h3>
          <p class="text-muted-foreground mt-2 mb-6">No documents match your search criteria.</p>
          <Button @click="clearFilters">
            Clear Filters
          </Button>
        </div>
      </div>

      <!-- Delete Confirmation Dialog -->
      <AlertDialog v-model:open="deleteDialogOpen">
        <AlertDialogContent class="max-w-[95vw] sm:max-w-md">
          <AlertDialogHeader>
            <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
            <AlertDialogDescription>
              This action cannot be undone. This will permanently delete the document
              "{{ documentToDelete?.title }}" and remove the file from our servers.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter class="flex flex-col sm:flex-row gap-2">
            <AlertDialogCancel :disabled="deleting" @click="deleteDialogOpen = false" class="w-full sm:w-auto">
              Cancel
            </AlertDialogCancel>
            <AlertDialogAction 
              @click="deleteDocument"
              class="bg-destructive text-destructive-foreground hover:bg-destructive/90 w-full sm:w-auto"
              :disabled="deleting"
            >
              <div v-if="deleting" class="flex items-center justify-center space-x-2">
                <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-white"></div>
                <span>Deleting...</span>
              </div>
              <span v-else>Delete Document</span>
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  </AppLayout>
</template>