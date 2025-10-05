<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import type {
  ColumnDef,
  ColumnFiltersState,
  SortingState,
  VisibilityState,
} from "@tanstack/vue-table"
import {
  FlexRender,
  getCoreRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useVueTable,
} from "@tanstack/vue-table"
import { ArrowUpDown, ChevronDown, MoreHorizontal, Eye, Edit, Archive, Trash2, Star } from "lucide-vue-next"
import { h, ref, onMounted, computed } from "vue"
import { router } from '@inertiajs/vue3'

import { Button } from "@/components/ui/button"
import { Checkbox } from "@/components/ui/checkbox"
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Input } from "@/components/ui/input"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { Badge } from "@/components/ui/badge"

// Utility function to handle value updates
function valueUpdater<T>(updaterOrValue: T | ((old: T) => T), ref: { value: T }) {
  if (typeof updaterOrValue === 'function') {
    ref.value = (updaterOrValue as (old: T) => T)(ref.value)
  } else {
    ref.value = updaterOrValue
  }
}

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'News Management',
    href: '/news',
  },
];

export interface News {
  id: string
  title: string
  slug: string
  excerpt: string
  content: string
  status: "draft" | "published" | "archived"
  category: string
  author: {
    id: string
    name: string
    email: string
  }
  published_at: string
  created_at: string
  updated_at: string
  is_featured: boolean
  image_path: string | null
  image_url: string | null
}

// Reactive data
const data = ref<News[]>([])
const loading = ref(true)
const total = ref(0)
const currentPage = ref(1)
const pageSize = ref(5) // Changed from 10 to 5
const error = ref<string | null>(null)
const searchQuery = ref('')

// Fetch news data from backend
const fetchNews = async (page = 1, search = '') => {
  loading.value = true
  error.value = null
  try {
    const url = `/news?page=${page}&search=${encodeURIComponent(search)}&per_page=${pageSize.value}&ajax=1`
    
    const response = await fetch(url, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    
    const result = await response.json()
    
    data.value = result.data || []
    total.value = result.total || 0
    currentPage.value = result.current_page || 1
    pageSize.value = result.per_page || 5
    
  } catch (err) {
    console.error('Failed to fetch news:', err)
    error.value = err instanceof Error ? err.message : 'Failed to load news articles'
    data.value = []
  } finally {
    loading.value = false
  }
}

// Initialize data
onMounted(() => {
  fetchNews()
})

const columns: ColumnDef<News>[] = [
  {
    id: "select",
    header: ({ table }) => h(Checkbox, {
      "checked": table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && "indeterminate"),
      "onUpdate:checked": (value: boolean | "indeterminate") => table.toggleAllPageRowsSelected(!!value),
      "ariaLabel": "Select all",
    }),
    cell: ({ row }) => h(Checkbox, {
      "checked": row.getIsSelected(),
      "onUpdate:checked": (value: boolean | "indeterminate") => row.toggleSelected(!!value),
      "ariaLabel": "Select row",
    }),
    enableSorting: false,
    enableHiding: false,
  },
  {
    accessorKey: "title",
    header: "Title",
    cell: ({ row }) => h("div", { class: "font-medium max-w-xs" }, [
      h("div", { class: "flex items-center space-x-2" }, [
        row.original.is_featured && h(Star, { class: "h-3 w-3 text-yellow-500 fill-yellow-500" }),
        h("span", { class: `truncate ${row.original.is_featured ? "font-bold" : ""}` }, row.getValue("title") || 'No title'),
      ]),
      row.original.excerpt && h("div", { class: "text-sm text-muted-foreground mt-1 line-clamp-1" }, row.original.excerpt)
    ]),
  },
  {
    accessorKey: "category",
    header: "Category",
    cell: ({ row }) => h(Badge, { 
      variant: "secondary"
    }, row.getValue("category") || 'Uncategorized'),
  },
  {
    accessorKey: "status",
    header: "Status",
    cell: ({ row }) => {
      const status = row.getValue("status") as string
      const statusConfig = {
        draft: { variant: "outline" as const, label: "Draft" },
        published: { variant: "default" as const, label: "Published" },
        archived: { variant: "secondary" as const, label: "Archived" }
      }
      const config = statusConfig[status as keyof typeof statusConfig] || statusConfig.draft
      return h(Badge, { 
        variant: config.variant
      }, config.label)
    },
  },
  {
    accessorKey: "author.name",
    header: ({ column }) => {
      return h(Button, {
        variant: "ghost",
        onClick: () => column.toggleSorting(column.getIsSorted() === "asc"),
      }, () => ["Author", h(ArrowUpDown, { class: "ml-2 h-4 w-4" })])
    },
    cell: ({ row }) => h("div", { class: "" }, row.original.author?.name || 'Unknown Author'),
  },
  {
    accessorKey: "published_at",
    header: "Published",
    cell: ({ row }) => {
      const date = row.getValue("published_at") as string
      return h("div", { class: "text-sm" }, date ? new Date(date).toLocaleDateString() : 'Not published')
    },
  },
  {
    accessorKey: "created_at",
    header: "Created",
    cell: ({ row }) => {
      const date = row.getValue("created_at") as string
      return h("div", { class: "text-sm" }, date ? new Date(date).toLocaleDateString() : 'Unknown')
    },
  },
  {
    id: "actions",
    enableHiding: false,
    cell: ({ row }) => {
      const news = row.original

      const handleView = () => {
        window.open(`/news/${news.slug}`, '_blank')
      }

      const handleEdit = () => {
        router.visit(`/news/${news.id}/edit`)
      }

      const handleStatusChange = async (newStatus: string) => {
        try {
          const response = await fetch(`/news/${news.id}/status`, {
            method: 'PATCH',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ status: newStatus })
          })
          
          if (response.ok) {
            await fetchNews(currentPage.value, searchQuery.value)
          }
        } catch (error) {
          console.error('Failed to update status:', error)
        }
      }

      const handleFeatureToggle = async () => {
        try {
          const response = await fetch(`/news/${news.id}/feature`, {
            method: 'PATCH',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
          })
          
          if (response.ok) {
            await fetchNews(currentPage.value, searchQuery.value)
          }
        } catch (error) {
          console.error('Failed to toggle feature:', error)
        }
      }

      const handleDelete = async () => {
        if (confirm('Are you sure you want to delete this article? This action cannot be undone.')) {
          try {
            const response = await fetch(`/news/${news.id}`, {
              method: 'DELETE',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
              }
            })
            
            if (response.ok) {
              await fetchNews(currentPage.value, searchQuery.value)
            }
          } catch (error) {
            console.error('Failed to delete article:', error)
          }
        }
      }

      return h(DropdownMenu, {}, [
        h(DropdownMenuTrigger, { asChild: true }, [
          h(Button, {
            variant: "ghost",
            class: "h-8 w-8 p-0"
          }, [
            h("span", { class: "sr-only" }, "Open menu"),
            h(MoreHorizontal, { class: "h-4 w-4" })
          ])
        ]),
        h(DropdownMenuContent, { align: "end" }, [
          h(DropdownMenuLabel, {}, "Actions"),
          h(DropdownMenuItem, {
            onClick: handleView,
            class: "flex items-center space-x-2 cursor-pointer"
          }, [
            h(Eye, { class: "h-4 w-4" }),
            h("span", "View")
          ]),
          h(DropdownMenuItem, {
            onClick: handleEdit,
            class: "flex items-center space-x-2 cursor-pointer"
          }, [
            h(Edit, { class: "h-4 w-4" }),
            h("span", "Edit")
          ]),
          h(DropdownMenuSeparator),
          h(DropdownMenuItem, {
            onClick: handleFeatureToggle,
            class: "flex items-center space-x-2 cursor-pointer"
          }, [
            h(Star, { class: `h-4 w-4 ${news.is_featured ? 'text-yellow-500 fill-yellow-500' : ''}` }),
            h("span", news.is_featured ? "Unfeature" : "Feature")
          ]),
          h(DropdownMenuSeparator),
          h(DropdownMenuItem, {
            onClick: () => handleStatusChange(news.status === 'published' ? 'draft' : 'published'),
            class: `flex items-center space-x-2 cursor-pointer ${news.status === 'published' ? 'text-orange-600' : 'text-green-600'}`
          }, [
            h(Archive, { class: "h-4 w-4" }),
            h("span", news.status === 'published' ? "Unpublish" : "Publish")
          ]),
          h(DropdownMenuItem, {
            onClick: () => handleStatusChange('archived'),
            class: "flex items-center space-x-2 cursor-pointer text-blue-600"
          }, [
            h(Archive, { class: "h-4 w-4" }),
            h("span", "Archive")
          ]),
          h(DropdownMenuSeparator),
          h(DropdownMenuItem, {
            onClick: handleDelete,
            class: "flex items-center space-x-2 cursor-pointer text-destructive"
          }, [
            h(Trash2, { class: "h-4 w-4" }),
            h("span", "Delete")
          ]),
        ])
      ])
    },
  },
]

const sorting = ref<SortingState>([])
const columnFilters = ref<ColumnFiltersState>([])
const columnVisibility = ref<VisibilityState>({})
const rowSelection = ref({})

// Create a computed property for table data that updates reactively
const tableData = computed(() => data.value)

const table = useVueTable({
  data: tableData.value,
  columns,
  getCoreRowModel: getCoreRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  onSortingChange: (updaterOrValue) => valueUpdater(updaterOrValue, sorting),
  onColumnFiltersChange: (updaterOrValue) => valueUpdater(updaterOrValue, columnFilters),
  onColumnVisibilityChange: (updaterOrValue) => valueUpdater(updaterOrValue, columnVisibility),
  onRowSelectionChange: (updaterOrValue) => valueUpdater(updaterOrValue, rowSelection),
  state: {
    get sorting() { return sorting.value },
    get columnFilters() { return columnFilters.value },
    get columnVisibility() { return columnVisibility.value },
    get rowSelection() { return rowSelection.value },
  },
})

// Handle search
const handleSearch = (value: string) => {
  searchQuery.value = value
  fetchNews(1, value)
}

// Handle pagination
const handlePreviousPage = () => {
  if (currentPage.value > 1) {
    fetchNews(currentPage.value - 1, searchQuery.value)
  }
}

const handleNextPage = () => {
  if (currentPage.value < Math.ceil(total.value / pageSize.value)) {
    fetchNews(currentPage.value + 1, searchQuery.value)
  }
}

const handleCreate = () => {
  router.visit('/news/create')
}

// Update table when data changes
import { watch } from 'vue'
watch(tableData, () => {
  table.setOptions(prev => ({
    ...prev,
    data: tableData.value,
  }))
})
</script>

<template>
  <Head title="News Management" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full p-6">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-foreground">News Management</h1>
          <p class="text-muted-foreground">Manage your news articles and publications</p>
        </div>
        <Button @click="handleCreate">
          <span class="mr-2">+</span>
          Add New Article
        </Button>
      </div>

      <!-- Error message -->
      <div v-if="error" class="mb-4 p-4 bg-destructive/15 border border-destructive/50 text-destructive rounded-lg">
        <strong>Error:</strong> {{ error }}
      </div>

      <div class="w-full bg-card rounded-lg border shadow-sm">
        <div class="flex items-center justify-between p-4 border-b">
          <Input
            class="max-w-sm"
            placeholder="Search articles..."
            :model-value="searchQuery"
            @update:model-value="(payload: string | number) => handleSearch(payload as string)"
          />
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="outline">
                Columns <ChevronDown class="ml-2 h-4 w-4" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuCheckboxItem
                v-for="column in table.getAllColumns().filter((column) => column.getCanHide())"
                :key="column.id"
                class="capitalize"
                :checked="column.getIsVisible()"
                @update:checked="(value: boolean) => {
                  column.toggleVisibility(!!value)
                }"
              >
                {{ column.id === 'title' ? 'Title' : 
                   column.id === 'category' ? 'Category' : 
                   column.id === 'status' ? 'Status' : 
                   column.id === 'author.name' ? 'Author' : 
                   column.id === 'published_at' ? 'Published Date' : 
                   column.id === 'created_at' ? 'Created Date' : column.id }}
              </DropdownMenuCheckboxItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
        
        <div class="rounded-md">
          <Table>
            <TableHeader>
              <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                <TableHead v-for="header in headerGroup.headers" :key="header.id">
                  <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <template v-if="table.getRowModel().rows?.length">
                <TableRow
                  v-for="row in table.getRowModel().rows"
                  :key="row.id"
                  :data-state="row.getIsSelected() && 'selected'"
                  class="hover:bg-muted/50 transition-colors"
                >
                  <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                    <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                  </TableCell>
                </TableRow>
              </template>

              <TableRow v-else-if="loading">
                <TableCell
                  :colspan="columns.length"
                  class="h-24 text-center"
                >
                  <div class="flex items-center justify-center space-x-2">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                    <span class="text-muted-foreground">Loading articles...</span>
                  </div>
                </TableCell>
              </TableRow>

              <TableRow v-else>
                <TableCell
                  :colspan="columns.length"
                  class="h-24 text-center text-muted-foreground"
                >
                  No news articles found.
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <div class="flex items-center justify-between p-4 border-t">
          <div class="flex-1 text-sm text-muted-foreground">
            {{ table.getFilteredSelectedRowModel().rows.length }} of
            {{ table.getFilteredRowModel().rows.length }} row(s) selected.
          </div>
          <div class="flex items-center space-x-4">
            <div class="text-sm text-muted-foreground">
              Page {{ currentPage }} of {{ Math.ceil(total / pageSize) }}
            </div>
            <div class="flex items-center space-x-2">
              <Button
                variant="outline"
                size="sm"
                :disabled="currentPage <= 1 || loading"
                @click="handlePreviousPage"
              >
                Previous
              </Button>
              <Button
                variant="outline"
                size="sm"
                :disabled="currentPage >= Math.ceil(total / pageSize) || loading"
                @click="handleNextPage"
              >
                Next
              </Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>