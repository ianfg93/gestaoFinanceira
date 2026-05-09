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

  async function load(groupId: number, filters: TransactionFilters) {
    loading.value = true
    try {
      const res = await transactionService.list(groupId, filters)
      items.value = res.data
      total.value = res.meta.total
      lastPage.value = res.meta.last_page
    } finally {
      loading.value = false
    }
  }

  async function create(groupId: number, data: Partial<Transaction> & { name: string }) {
    const t = await transactionService.create(groupId, data)
    if (t) items.value.unshift(t)
    return t
  }

  async function update(groupId: number, id: number, data: Partial<Transaction> & { scope?: string }) {
    const t = await transactionService.update(groupId, id, data)
    const idx = items.value.findIndex(i => i.id === id)
    if (idx >= 0) items.value[idx] = t
    return t
  }

  async function remove(groupId: number, id: number, scope = 'this') {
    await transactionService.remove(groupId, id, scope)
    if (scope === 'this') {
      items.value = items.value.filter(i => i.id !== id)
    }
  }

  return { items, loading, total, lastPage, currentPage, load, create, update, remove }
})
