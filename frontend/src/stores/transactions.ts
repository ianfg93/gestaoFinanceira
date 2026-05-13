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
  type CachePayload = { data: Transaction[]; total: number; lastPage: number; currentPage: number }
  const cache = new Map<string, CachePayload>()
  const inFlight = new Map<string, Promise<CachePayload>>()
  let lastLoadedKey = ''

  function buildCacheKey(groupId: number, filters: TransactionFilters) {
    const normalized = Object.entries(filters)
      .filter(([, v]) => v !== undefined && v !== '')
      .sort(([a], [b]) => a.localeCompare(b))
      .map(([k, v]) => `${k}:${String(v)}`)
      .join('|')
    return `${groupId}|${normalized}`
  }

  function setFromPayload(payload: CachePayload) {
    items.value = payload.data
    total.value = payload.total
    lastPage.value = payload.lastPage
    currentPage.value = payload.currentPage
  }

  function hasCache(groupId: number, filters: TransactionFilters) {
    const key = buildCacheKey(groupId, filters)
    return cache.has(key)
  }

  async function load(groupId: number, filters: TransactionFilters, options?: { force?: boolean }) {
    const key = buildCacheKey(groupId, filters)
    const cached = cache.get(key)

    if (cached) {
      setFromPayload(cached)
    }

    // Same query already loaded and cached: avoid redundant request.
    if (cached && key === lastLoadedKey && !options?.force) {
      loading.value = false
      return
    }

    // If same query is already being fetched, reuse that request.
    if (inFlight.has(key)) {
      const payload = await inFlight.get(key)!
      setFromPayload(payload)
      return
    }

    const requestId = ++lastRequestId
    loading.value = true
    const request = (async (): Promise<CachePayload> => {
      const res = await transactionService.list(groupId, filters)
      return {
        data: res.data,
        total: res.meta.total,
        lastPage: res.meta.last_page,
        currentPage: res.meta.current_page,
      }
    })()
    inFlight.set(key, request)

    try {
      const payload = await request
      if (requestId !== lastRequestId) return
      cache.set(key, payload)
      lastLoadedKey = key
      setFromPayload(payload)
    } finally {
      inFlight.delete(key)
      if (requestId === lastRequestId) {
        loading.value = false
      }
    }
  }

  async function prefetch(groupId: number, filters: TransactionFilters) {
    const key = buildCacheKey(groupId, filters)
    if (cache.has(key) || inFlight.has(key)) return

    const request = (async (): Promise<CachePayload> => {
      const res = await transactionService.list(groupId, filters)
      return {
        data: res.data,
        total: res.meta.total,
        lastPage: res.meta.last_page,
        currentPage: res.meta.current_page,
      }
    })()

    inFlight.set(key, request)
    try {
      const payload = await request
      cache.set(key, payload)
    } finally {
      inFlight.delete(key)
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

  return { items, loading, total, lastPage, currentPage, hasCache, load, prefetch, create, update, remove }
})
