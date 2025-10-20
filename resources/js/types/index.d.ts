import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
    badge?: string | number;
    badgeVariant?: 'default' | 'secondary' | 'destructive' | 'outline' | 'success' | 'warning';
    badgeClass?: string;
    badgeShape?: 'auto' | 'circle' | 'rounded' | 'square';
    visible?: boolean;
    children?: NavItem[];
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    badgeCounts?: BadgeCounts;
    userData?: User;
    formOptions?: {
        roles: Record<string, string>;
        offices: Record<string, string>;
        positions: Record<string, string>;
    };
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    role: string;
    office: string;
    is_active: boolean;
    last_login_at: string | null;
    permissions: string[];
    // Add missing fields
    phone?: string;
    position?: string;
    last_login_ip?: string | null;
    login_count?: number;
    timezone?: string;
    locale?: string;
    two_factor_secret?: string;
    two_factor_recovery_codes?: string;
    two_factor_confirmed_at?: string | null;
    remember_token?: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface PaginationLinks {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginationData {
    current_page: number;
    data: any[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: PaginationLinks[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

export interface FlashMessages {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
}

// Dashboard Types
export interface BadgeCounts {
  news?: {
    total: number;
    draft: number;
    pending: number;
  };
  bids_awards?: {
    total: number;
    active: number;
    closed: number;
  };
  full_disclosure?: {
    total: number;
    recent: number;
  };
  tourism?: {
    total: number;
    active: number;
    featured: number;
  };
  awards_recognition?: {
    total: number;
    active: number;
    featured: number;
  };
  sangguniang_bayan?: {
    total: number;
    active: number;
  };
  ordinance_resolutions?: {
    total: number;
    ordinances: number;
    resolutions: number;
  };
  users?: {
    total: number;
    active: number;
    pending: number;
  };
  activity_logs?: number;
  trash?: number;
}

export interface SystemStats {
  users?: {
    total: number;
    active: number;
    pending: number;
    inactive: number;
    superadmins: number;
    admins: number;
    staff: number;
    users: number;
  };
  content?: {
    news: number;
    published_news: number;
    bids_awards: number;
    tourism_packages: number;
    active_tourism: number;
    awards: number;
    active_awards: number;
    sb_members: number;
    active_sb_members: number;
    ordinances: number;
    resolutions: number;
    disclosures: number;
  };
  system?: {
    activities: number;
    storage_used: string;
    database_size: string;
    backup_status: string;
    last_maintenance: string;
  };
}

export interface RecentActivity {
  id: number;
  action: string;
  description: string;
  time: string;
  user: string;
  icon: string;
  color: string;
}

export interface DashboardModule {
  title: string;
  description: string;
  icon: string;
  route: string;
  color: string;
  bgColor?: string;
  stats: string;
}

export interface QuickStat {
  label: string;
  value: string;
  change: string;
  icon: string;
  color: string;
  bgColor: string;
}