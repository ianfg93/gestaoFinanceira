export interface User {
  id: number
  name: string
  email: string
  avatar_url: string | null
  timezone: string
  locale: string
}

export interface Group {
  id: number
  name: string
  slug: string
  currency: string
  owner_id: number
  members?: GroupMember[]
}

export interface GroupMember extends User {
  role: 'owner' | 'admin' | 'editor' | 'viewer'
}

export type TransactionType   = 'expense' | 'income' | 'investment'
export type TransactionStatus = 'pending' | 'paid' | 'overdue' | 'cancelled' | 'partial'

export interface Category {
  id: number
  name: string
  color: string | null
  icon: string | null
  type: string
  parent_id: number | null
  children?: Category[]
}

export interface Tag {
  id: number
  name: string
  color: string | null
}

export interface Transaction {
  id: number
  group_id: number
  series_id: number | null
  installment_number: number | null
  total_installments: number | null
  type: TransactionType
  name: string
  transaction_name_id: number
  category_id: number | null
  category: Category | null
  amount: number
  status: TransactionStatus
  due_date: string
  paid_date: string | null
  reference_month: string
  responsible_id: number | null
  responsible: User | null
  notes: string | null
  is_recurring: boolean
  notifications_muted: boolean
  tags: Tag[]
  attachments_count: number
  created_by: number
  updated_by: number | null
  created_at: string
  updated_at: string
}

export interface DashboardTotals {
  income: number
  expense: number
  investment: number
  balance: number
}

export interface DashboardData {
  month: string
  totals: DashboardTotals
  by_status: Array<{ status: string; qty: number; total: number }>
  by_category: Array<{ id: number; name: string; color: string; total: number }>
  by_responsible: Array<{ id: number; name: string; type: string; total: number }>
  due_soon: Transaction[]
  overdue: Transaction[]
  recent_paid: Transaction[]
}

export interface Notification {
  id: number
  type: string
  title: string
  body: string | null
  read_at: string | null
  action_url: string | null
  created_at: string
  transaction_id: number | null
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: { current_page: number; last_page: number; per_page: number; total: number }
}

export interface TransactionFilters {
  month?: string
  type?: string
  status?: string
  category_id?: string
  responsible_id?: string
  series_id?: string
  q?: string
  sort_by?: string
  sort_dir?: 'asc' | 'desc'
  per_page?: number
}
