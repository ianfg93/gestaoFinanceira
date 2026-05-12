import { defineStore } from 'pinia'
import { ref } from 'vue'
import { transactionService } from '@/services/transactionService'
import type { Transaction, TransactionFilters } from '@/types'

export const useTransactionStore = defineStore('transactions', () => {
  const items      = ref<Transaction[]>([])
  const loading    = ref(false)
  const total      = ref(0)
  const lastPage   = ref(1)
  const currentPage = ref(1)
  let lastRequestId = 0
  const cache = new Map<string, { data: Transaction[]; total: number; lastPage: number; currentPage: number }>()

  function buildCacheKey(groupId: number, filters: TransactionFilters) {
    const normalized = Object.entries(filters)
      .filter(([, v]) => v !== undefined && v !== '')
      .sort(([a], [b]) => a.localeCompare(b))
      .map(([k, v]) => `${k}:${String(v)}`)
      .join('|')
    return `${groupId}|${normalized}`
  }

  function setFromPayload(payload: { data: Transaction[]; total: number; lastPage: number; currentPage: number }) {
    items.value = payload.data
    total.value = payload.total
    lastPage.value = payload.lastPage
    currentPage.value = payload.currentPage
  }

  async function load(groupId: number, filters: TransactionFilters) {
    const requestId = ++lastRequestId
    const key = buildCacheKey(groupId, filters)
    const cached = cache.get(key)
    if (cached) {
      setFromPayload(cached)
    }

    loading.value = true
    try {
      const res = await transactionService.list(groupId, filters)
      if (requestId !== lastRequestId) return
      const payload = {
        data: res.data,
        total: res.meta.total,
        lastPage: res.meta.last_page,
        currentPage: res.meta.current_page,
      }
      cache.set(key, payload)
      setFromPayload(payload)
    } finally {
      if (requestId === lastRequestId) {
        loading.value = false
      }
    }
  }

  async function create(groupId: number, data: Partial<Transaction> & { name: string }) {
    const t = await transactionService.create(groupId, data)
    cache.clear()
    if (t) items.value.unshift(t)
    return t
  }

  async function update(groupId: number, id: number, data: Partial<Transaction> & { scope?: string }) {
    const t = await transactionService.update(groupId, id, data)
    cache.clear()
    const idx = items.value.findIndex(i => i.id === id)
    if (idx >= 0) items.value[idx] = t
    return t
  }

  async function remove(groupId: number, id: number, scope = 'this') {
    await transactionService.remove(groupId, id, scope)
    cache.clear()
    if (scope === 'this') {
      items.value = items.value.filter(i => i.id !== id)
    }
  }

  return { items, loading, total, lastPage, currentPage, load, create, update, remove }
})
