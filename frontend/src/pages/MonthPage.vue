<template>
  <div class="flex flex-col h-full">
    <!-- Toolbar -->
    <div class="flex-shrink-0 px-6 py-4 bg-white border-b border-gray-200 flex items-center gap-3 flex-wrap">
      <div class="flex items-center gap-2">
        <button @click="prevM" class="btn-secondary btn-sm"><ChevronLeftIcon class="w-4 h-4" /></button>
        <span class="text-base font-bold text-gray-900 min-w-36 text-center">{{ formatMonth(month) }}</span>
        <button @click="nextM" class="btn-secondary btn-sm"><ChevronRightIcon class="w-4 h-4" /></button>
      </div>

      <div class="flex items-center gap-2 flex-1">
        <div class="relative flex-1 max-w-xs">
          <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input v-model="filters.q" type="text" placeholder="Buscar..." class="input pl-9 py-1.5 text-sm" @input="onSearchInput" />
        </div>

        <select v-model="filters.type" @change="onFilterChanged" class="input py-1.5 text-sm w-36">
          <option value="">Todos os tipos</option>
          <option value="expense">Despesas</option>
          <option value="income">Receitas</option>
          <option value="investment">Investimentos</option>
        </select>

        <select v-model="filters.status" @change="onFilterChanged" class="input py-1.5 text-sm w-36">
          <option value="">Todos os status</option>
          <option value="pending">Pendente</option>
          <option value="paid">Pago</option>
          <option value="overdue">Atrasado</option>
          <option value="cancelled">Cancelado</option>
        </select>
      </div>

      <button @click="showForm = true" class="btn-primary btn-sm ml-auto">
        <PlusIcon class="w-4 h-4" /> Nova
      </button>
    </div>

    <!-- Summary bar -->
    <div class="flex-shrink-0 px-6 py-2 bg-gray-50 border-b border-gray-200 flex items-center gap-6 text-sm">
      <span class="text-gray-500">{{ transactionStore.total }} registros</span>
      <span class="text-green-600 font-medium">Receitas: {{ formatCurrency(totals.income) }}</span>
      <span class="text-red-600 font-medium">Despesas: {{ formatCurrency(totals.expense) }}</span>
      <span class="text-blue-600 font-medium">Investimentos: {{ formatCurrency(totals.investment) }}</span>
      <span :class="totals.balance >= 0 ? 'text-green-700' : 'text-red-700'" class="font-bold">
        Saldo: {{ formatCurrency(totals.balance) }}
      </span>
      <span v-if="savingCount > 0" class="ml-auto text-xs text-gray-400 flex items-center gap-1">
        <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span> Salvando...
      </span>
      <span v-else-if="lastSaved" class="ml-auto text-xs text-gray-400">
        Salvo &agrave;s {{ lastSaved.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) }}
      </span>
    </div>

    <!-- AG Grid -->
    <div class="flex-1 overflow-hidden relative">
      <AgGridVue
        class="ag-theme-alpine w-full h-full"
        :columnDefs="columnDefs"
        :defaultColDef="defaultColDef"
        :rowSelection="rowSelection"
        :getRowId="(p: any) => String(p.data.id)"
        :getRowClass="getRowClass"
        :stopEditingWhenCellsLoseFocus="true"
        :enableCellChangeFlash="true"
        @cell-value-changed="onCellChanged"
        @grid-ready="onGridReady"
        @selection-changed="onSelectionChanged"
      />

      <div
        v-if="showBlockingGridLoading"
        class="absolute inset-0 bg-white/75 backdrop-blur-[1px] flex items-center justify-center z-10"
      >
        <div class="flex items-center gap-3 text-sm text-gray-600">
          <span class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></span>
          <span>Carregando registros...</span>
        </div>
      </div>

      <div
        v-else-if="transactionStore.loading"
        class="absolute top-2 right-2 z-10 bg-white/90 border border-gray-200 rounded-full px-2 py-1 text-xs text-gray-600 flex items-center gap-1"
      >
        <span class="w-3 h-3 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></span>
        <span>Atualizando</span>
      </div>
    </div>

    <!-- Footer totals -->
    <div class="flex-shrink-0 px-6 py-2 bg-white border-t border-gray-200 flex items-center gap-4 text-xs text-gray-500">
      <span>{{ selectedCount > 0 ? `${selectedCount} selecionados` : '' }}</span>
      <button v-if="selectedCount > 0" @click="deleteSelected" class="text-red-500 hover:underline">Excluir selecionados</button>
      <div class="ml-auto flex items-center gap-2">
        <label class="text-gray-500">Por p&aacute;gina</label>
        <select v-model.number="perPage" @change="changePerPage" class="input py-1 text-xs w-20">
          <option :value="50">50</option>
          <option :value="100">100</option>
          <option :value="200">200</option>
        </select>
        <button @click="prevPage" :disabled="currentPage <= 1" class="btn-secondary btn-sm disabled:opacity-50">Anterior</button>
        <span class="min-w-20 text-center">P&aacute;gina {{ currentPage }} / {{ transactionStore.lastPage }}</span>
        <button @click="nextPage" :disabled="currentPage >= transactionStore.lastPage" class="btn-secondary btn-sm disabled:opacity-50">Pr&oacute;xima</button>
      </div>
    </div>
  </div>

  <!-- Transaction form modal -->
  <TransactionForm
    v-if="showForm"
    :month="month"
    @close="showForm = false"
    @created="onCreated"
  />

  <!-- Scope modal for recurring edits -->
  <ScopeModal
    v-if="pendingEdit"
    @confirm="applyEdit"
    @cancel="pendingEdit = null"
  />

  <DeleteTransactionModal
    v-if="pendingDelete"
    :description="pendingDelete.description"
    :options="pendingDelete.options"
    @confirm="onDeleteModalConfirm"
    @cancel="pendingDelete = null"
  />

  <!-- Series / installments modal -->
  <SeriesModal
    v-if="viewingSeries"
    :series-id="viewingSeries.seriesId"
    :current-month="month"
    @close="viewingSeries = null"
    @updated="loadData"
  />
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted, toRaw } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { AgGridVue } from 'ag-grid-vue3'
import type { ColDef, GridApi, CellValueChangedEvent, GridReadyEvent } from 'ag-grid-community'
import { ModuleRegistry, AllCommunityModule } from 'ag-grid-community'
import { useDebounceFn } from '@vueuse/core'

ModuleRegistry.registerModules([AllCommunityModule])
import { useToast } from 'vue-toastification'
import {
  ChevronLeftIcon, ChevronRightIcon, PlusIcon, MagnifyingGlassIcon
} from '@heroicons/vue/24/outline'

import { useTransactionStore } from '@/stores/transactions'
import { useAuthStore } from '@/stores/auth'
import { useCategoryStore } from '@/stores/categories'
import { useTagStore } from '@/stores/tags'
import api from '@/services/api'
import { transactionService } from '@/services/transactionService'
import { formatCurrency, formatDate, formatMonth, prevMonth, nextMonth, STATUS_LABELS, TYPE_LABELS } from '@/utils/format'
import TransactionForm from '@/components/forms/TransactionForm.vue'
import ScopeModal from '@/components/forms/ScopeModal.vue'
import SeriesModal from '@/components/forms/SeriesModal.vue'
import DeleteTransactionModal from '@/components/forms/DeleteTransactionModal.vue'

import 'ag-grid-community/styles/ag-grid.css'
import 'ag-grid-community/styles/ag-theme-alpine.css'

const route            = useRoute()
const router           = useRouter()
const toast            = useToast()
const transactionStore = useTransactionStore()
const authStore        = useAuthStore()
const categoryStore    = useCategoryStore()
const tagStore         = useTagStore()

const month        = ref(route.params.month as string)
const showForm     = ref(false)
const savingCount  = ref(0)
const lastSaved    = ref<Date | null>(null)
const selectedCount = ref(0)
const currentPage  = ref(1)
const perPage      = ref(100)
const pendingEdit   = ref<{ rowId: number; field: string; newValue: any; oldValue: any; data: any } | null>(null)
const pendingDelete = ref<{
  mode: 'single' | 'bulk'
  id?: number
  description: string
  options: Array<{
    value: 'this' | 'future' | 'all'
    label: string
    description?: string
  }>
} | null>(null)
const viewingSeries = ref<{ seriesId: number } | null>(null)
const gridApi        = ref<GridApi | null>(null)
const groupMembers   = ref<{ id: number; name: string }[]>([])
const loadedContextGroupId = ref<number | null>(null)
const monthSwitching = ref(false)
const showBlockingGridLoading = computed(() =>
  transactionStore.loading && (transactionStore.items.length === 0 || monthSwitching.value)
)
let loadSeq = 0

const filters = ref({ q: '', type: '', status: '' })

watch(() => route.params.month, (m) => {
  const nextMonthParam = m as string
  if (!nextMonthParam || nextMonthParam === month.value) return
  month.value = nextMonthParam
  currentPage.value = 1
  monthSwitching.value = true
  loadData()
})

function prevM() {
  monthSwitching.value = true
  router.push(`/months/${prevMonth(month.value)}`)
}

function nextM() {
  monthSwitching.value = true
  router.push(`/months/${nextMonth(month.value)}`)
}

const totals = computed(() => {
  const items = transactionStore.items
  let income = 0
  let expense = 0
  let investment = 0

  for (const t of items) {
    if (t.type === 'income') income += t.amount
    else if (t.type === 'expense') expense += t.amount
    else if (t.type === 'investment') investment += t.amount
  }

  return { income, expense, investment, balance: income - expense - investment }
})

const defaultColDef: ColDef = {
  resizable: true,
  sortable: true,
  filter: true,
  editable: false,
}

const rowSelection: any = { mode: 'multiRow', checkboxes: true, headerCheckbox: true }

const columnDefs = computed<ColDef[]>(() => {
  const cats    = categoryStore.flat()

  return [
    {
      field: 'type',
      headerName: 'Tipo',
      width: 130,
      editable: true,
      cellEditor: 'agSelectCellEditor',
      cellEditorParams: { values: ['expense', 'income', 'investment'] },
      valueFormatter: (p: any) => TYPE_LABELS[p.value] ?? p.value,
      cellStyle: (p: any) => ({
        color: p.value === 'income' ? '#15803d' : p.value === 'investment' ? '#1d4ed8' : '#b91c1c',
        fontWeight: '500',
      }),
    },
    {
      field: 'name',
      headerName: 'Nome',
      flex: 2,
      minWidth: 160,
      editable: true,
    },
    {
      field: 'category',
      headerName: 'Categoria',
      flex: 1,
      minWidth: 120,
      editable: true,
      cellEditor: 'agSelectCellEditor',
      cellEditorParams: { values: ['', ...cats.map(c => c.name)] },
      valueGetter: (p: any) => p.data?.category?.name ?? '',
      valueSetter: (p: any) => {
        const cat = cats.find(c => c.name === p.newValue)
        p.data.category    = cat ?? null
        p.data.category_id = cat?.id ?? null
        return true
      },
    },
    {
      field: 'amount',
      headerName: 'Valor',
      width: 140,
      editable: true,
      cellEditor: 'agNumberCellEditor',
      valueFormatter: (p: any) => formatCurrency(p.value),
      cellStyle: (p: any) => ({
        color: p.data?.type === 'income' ? '#15803d' : '#b91c1c',
        fontWeight: '600',
      }),
    },
    {
      field: 'status',
      headerName: 'Status',
      width: 130,
      editable: true,
      cellEditor: 'agSelectCellEditor',
      cellEditorParams: { values: ['pending', 'paid', 'overdue', 'cancelled', 'partial'] },
      valueFormatter: (p: any) => STATUS_LABELS[p.value] ?? p.value,
      cellStyle: (p: any) => {
        const styles: Record<string, string> = {
          pending:   '#92400e', paid:      '#166534',
          overdue:   '#991b1b', cancelled: '#374151', partial: '#1e40af',
        }
        return { color: styles[p.value] ?? '#374151', fontWeight: '500' }
      },
    },
    {
      field: 'due_date',
      headerName: 'Vencimento',
      width: 130,
      editable: true,
      cellEditor: 'agDateStringCellEditor',
      valueFormatter: (p: any) => formatDate(p.value),
      cellStyle: (p: any) => {
        if (!p.data) return {}
        const today = new Date().toISOString().split('T')[0]
        if (p.data.status === 'pending' && p.value < today) return { color: '#dc2626', fontWeight: '600' }
        if (p.data.status === 'pending' && p.value === today) return { color: '#d97706', fontWeight: '600' }
        return {}
      },
    },
    {
      field: 'paid_date',
      headerName: 'Pago em',
      width: 130,
      editable: true,
      cellEditor: 'agDateStringCellEditor',
      valueFormatter: (p: any) => formatDate(p.value),
    },
    {
      field: 'responsible',
      headerName: 'Respons\u00E1vel',
      width: 150,
      editable: true,
      cellEditor: 'agSelectCellEditor',
      cellEditorParams: () => ({ values: ['', ...groupMembers.value.map(m => m.name)] }),
      valueGetter: (p: any) => p.data?.responsible?.name ?? '',
      valueSetter: (p: any) => {
        const m = groupMembers.value.find(mb => mb.name === p.newValue)
        p.data.responsible    = m ? { id: m.id, name: m.name } : null
        p.data.responsible_id = m?.id ?? null
        return true
      },
    },
    {
      field: 'notes',
      headerName: 'Obs.',
      flex: 1,
      minWidth: 100,
      editable: true,
    },
    {
      field: 'installment_number',
      headerName: 'Parcela',
      width: 90,
      sortable: false,
      filter: false,
      cellRenderer: (p: any) => {
        if (!p.data?.series_id) return '<span class="text-gray-300 text-xs">-</span>'
        const label = p.value ? `${p.value}/${p.data.total_installments}` : 'Serie'
        return `<button
          class="px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors"
          data-action="view-series"
          data-series-id="${p.data.series_id}"
          title="Ver todas as parcelas"
        >${label}</button>`
      },
    },
    {
      field: 'attachments_count',
      headerName: 'Anexos',
      width: 60,
      suppressMenu: true,
      sortable: false,
      filter: false,
      valueFormatter: (p: any) => p.value > 0 ? String(p.value) : '',
    },
    {
      headerName: '',
      width: 48,
      pinned: 'right',
      suppressMenu: true,
      sortable: false,
      filter: false,
      editable: false,
      cellRenderer: (p: any) => `<div class="flex items-center justify-center h-full">
        <button class="p-1 rounded hover:bg-red-50 text-gray-400 hover:text-red-600" data-action="delete" data-id="${p.data.id}" title="Excluir"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-9 0V5h6v2m-7 0l1 12h6l1-12" /></svg></button>
      </div>`,
    },
  ]
})

function getRowClass(p: any) {
  if (!p.data) return ''
  const today = new Date().toISOString().split('T')[0]
  if (p.data.status === 'overdue') return 'bg-red-50/60'
  if (p.data.status === 'pending' && p.data.due_date === today) return 'bg-yellow-50/60'
  if (p.data.status === 'paid') return 'opacity-70'
  return ''
}

function onSelectionChanged() {
  selectedCount.value = gridApi.value?.getSelectedNodes().length ?? 0
}

async function onGridReady(p: GridReadyEvent) {
  gridApi.value = p.api
  const gid = authStore.currentGroup?.id
  if (gid) {
    await ensureGroupContext(gid)
  }
  await loadData()
}

async function ensureGroupContext(groupId: number) {
  if (loadedContextGroupId.value === groupId) return

  loadedContextGroupId.value = groupId
  try {
    const [membersRes] = await Promise.all([
      api.get(`/groups/${groupId}/members`),
      categoryStore.load(groupId),
      tagStore.load(groupId),
    ])
    groupMembers.value = membersRes.data
  } catch {
    groupMembers.value = []
  }
}

// Per-cell lock: prevents duplicate saves from spurious re-fires
const inFlightEdits = new Map<string, boolean>()

function normalizeValue(v: any): any {
  if (v instanceof Date) return v.toISOString().split('T')[0]
  return v
}

async function onCellChanged(event: CellValueChangedEvent) {
  const { data, colDef, newValue, oldValue } = event
  const field   = colDef.field as string
  const cellKey = `${data.id}:${field}`

  if (inFlightEdits.has(cellKey)) return

  const nv = normalizeValue(newValue)
  const ov = normalizeValue(oldValue)
  if (nv === ov || nv === undefined) return

  // Map display columns -> actual API field + value
  let apiField = field
  let apiValue: any = nv

  if (field === 'category') {
    apiField = 'category_id'
    apiValue = data.category_id ?? null
  } else if (field === 'responsible') {
    apiField = 'responsible_id'
    apiValue = data.responsible_id ?? null
  }

  const groupId = authStore.currentGroup!.id

  // Recurring transaction: ask scope before saving
  if (data.series_id && ['amount', 'status', 'category_id', 'notes', 'due_date', 'name'].includes(apiField)) {
    pendingEdit.value = { rowId: data.id, field: apiField, newValue: apiValue, oldValue: ov, data }
    return
  }

  inFlightEdits.set(cellKey, true)
  savingCount.value++
  try {
    await transactionService.patch(groupId, data.id, apiField, apiValue)
    lastSaved.value = new Date()

    // Update store so the summary bar totals recalculate immediately
    const idx = transactionStore.items.findIndex(i => i.id === data.id)
    if (idx >= 0) {
      const updated: any = { ...transactionStore.items[idx] }
      if (field === 'category') {
        updated.category    = data.category
        updated.category_id = data.category_id
      } else if (field === 'responsible') {
        updated.responsible    = data.responsible
        updated.responsible_id = data.responsible_id
      } else {
        updated[field] = nv
      }
      transactionStore.items[idx] = updated
    }
  } catch {
    revertCell(data.id, field, oldValue)
    toast.error('N\u00E3o foi poss\u00EDvel salvar.')
  } finally {
    savingCount.value--
    inFlightEdits.delete(cellKey)
  }
}

async function applyEdit(scope: string) {
  if (!pendingEdit.value) return
  const { rowId, field, newValue, oldValue } = pendingEdit.value
  pendingEdit.value = null
  const groupId = authStore.currentGroup!.id

  savingCount.value++
  try {
    await transactionService.update(groupId, rowId, { [field]: newValue, scope })
    lastSaved.value = new Date()
    if (scope !== 'this') {
      await loadData()
    } else {
      const idx = transactionStore.items.findIndex(i => i.id === rowId)
      if (idx >= 0) transactionStore.items[idx] = { ...transactionStore.items[idx], [field]: newValue }
    }
  } catch {
    revertCell(rowId, field, oldValue)
    toast.error('N\u00E3o foi poss\u00EDvel salvar.')
  } finally {
    savingCount.value--
  }
}

function revertCell(id: number, field: string, oldValue: any) {
  const node = gridApi.value?.getRowNode(String(id))
  if (!node) return
  // Direct data mutation - does NOT fire cell-value-changed (unlike setDataValue)
  node.data[field] = oldValue
  gridApi.value?.refreshCells({ rowNodes: [node], columns: [field], force: true })
}

// Handle action button clicks via event delegation
function handleGridClick(e: MouseEvent) {
  const target = e.target as HTMLElement
  const btn    = target.closest('[data-action]') as HTMLElement | null
  if (!btn) return
  const action = btn.dataset.action
  if (action === 'delete') {
    confirmDelete(parseInt(btn.dataset.id ?? '0'))
  } else if (action === 'view-series') {
    const seriesId = parseInt(btn.dataset.seriesId ?? '0')
    if (seriesId) viewingSeries.value = { seriesId }
  }
}

onMounted(() => {
  document.querySelector('.ag-theme-alpine')?.addEventListener('click', handleGridClick as EventListener)
})

onUnmounted(() => {
  document.querySelector('.ag-theme-alpine')?.removeEventListener('click', handleGridClick as EventListener)
})

async function confirmDelete(id: number) {
  const t = transactionStore.items.find(i => i.id === id)
  if (!t) return
  const isSeries = Boolean(t.series_id)
  pendingDelete.value = {
    mode: 'single',
    id,
    description: isSeries
      ? `A movimenta\u00E7\u00E3o "${t.name}" faz parte de uma s\u00E9rie. Escolha o escopo da exclus\u00E3o.`
      : `Deseja excluir "${t.name}"?`,
    options: isSeries
      ? [
          { value: 'this', label: 'Excluir s\u00F3 este m\u00EAs', description: 'Exclui somente o registro deste m\u00EAs.' },
          { value: 'future', label: 'Excluir deste m\u00EAs em diante', description: 'Exclui este e os futuros.' },
          { value: 'all', label: 'Excluir todos, inclusive passados', description: 'Exclui toda a s\u00E9rie.' },
        ]
      : [{ value: 'this', label: 'Excluir', description: 'Exclui apenas este registro.' }],
  }
}

async function deleteSelected() {
  const nodes = gridApi.value?.getSelectedNodes() ?? []
  if (!nodes.length) return
  pendingDelete.value = {
    mode: 'bulk',
    description: `Deseja excluir ${nodes.length} registro(s) selecionado(s)?`,
    options: [{ value: 'this', label: 'Excluir selecionados', description: 'Exclui apenas os itens selecionados.' }],
  }
}

async function onDeleteModalConfirm(scope: 'this' | 'future' | 'all') {
  if (!pendingDelete.value) return
  const mode = pendingDelete.value.mode

  if (mode === 'single') {
    const id = pendingDelete.value.id
    if (!id) return
    pendingDelete.value = null
    try {
      await transactionStore.remove(authStore.currentGroup!.id, id, scope)
      if (scope === 'this') {
        gridApi.value?.applyTransaction({ remove: [{ id }] })
      } else {
        await loadData()
      }
      toast.success('Exclu\u00EDdo com sucesso')
    } catch {
      toast.error('Erro ao excluir')
    }
    return
  }

  const bulkNodes = gridApi.value?.getSelectedNodes() ?? []
  if (!bulkNodes.length) {
    pendingDelete.value = null
    return
  }

  for (const node of bulkNodes) {
    await transactionStore.remove(authStore.currentGroup!.id, node.data.id)
  }

  pendingDelete.value = null
  toast.success(`${bulkNodes.length} registro(s) excluido(s)`)
  selectedCount.value = 0
  await loadData()
}

async function loadData() {
  const groupId = authStore.currentGroup?.id
  if (!groupId || !gridApi.value) return

  const seq = ++loadSeq
  const query = {
    month:  month.value,
    type:   filters.value.type   || undefined,
    status: filters.value.status || undefined,
    q:      filters.value.q      || undefined,
    page: currentPage.value,
    per_page: perPage.value,
  }
  const hasCachedData = transactionStore.hasCache(groupId, query)
  const shouldBlockForMonthSwitch = monthSwitching.value && !hasCachedData
  const loadPromise = transactionStore.load(groupId, query)

  if (shouldBlockForMonthSwitch) {
    gridApi.value.setGridOption('rowData', [])
  } else {
    // Immediate paint from cached data when available.
    gridApi.value.setGridOption('rowData', toRaw(transactionStore.items))
  }

  await loadPromise
  if (seq !== loadSeq) return

  // Alimenta o grid com dados sem binding reativo (evita loop).
  gridApi.value.setGridOption('rowData', toRaw(transactionStore.items))
  currentPage.value = transactionStore.currentPage
  monthSwitching.value = false
  prefetchNeighborMonths(groupId)
}

function prefetchNeighborMonths(groupId: number) {
  if (filters.value.q || filters.value.type || filters.value.status) return
  if (currentPage.value !== 1) return

  const sharedFilters = {
    type: undefined,
    status: undefined,
    q: undefined,
    page: 1,
    per_page: perPage.value,
  }

  for (const targetMonth of [prevMonth(month.value), nextMonth(month.value)]) {
    void transactionStore.prefetch(groupId, {
      month: targetMonth,
      ...sharedFilters,
    }).catch(() => {
      // Best effort only: keep UX snappy without surfacing noise.
    })
  }
}

const debouncedLoad = useDebounceFn(loadData, 400)

function onSearchInput() {
  currentPage.value = 1
  debouncedLoad()
}

function onFilterChanged() {
  currentPage.value = 1
  loadData()
}

function prevPage() {
  if (currentPage.value <= 1) return
  currentPage.value--
  loadData()
}

function nextPage() {
  if (currentPage.value >= transactionStore.lastPage) return
  currentPage.value++
  loadData()
}

function changePerPage() {
  currentPage.value = 1
  loadData()
}

function onCreated() {
  showForm.value = false
  loadData()
  toast.success('Criado com sucesso!')
}

watch(() => authStore.currentGroup?.id, async (gid) => {
  currentPage.value = 1
  if (!gid) return
  await ensureGroupContext(gid)
  await loadData()
})
</script>
