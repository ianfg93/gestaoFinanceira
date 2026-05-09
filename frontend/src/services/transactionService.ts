import api from './api'
import type { Transaction, TransactionFilters, PaginatedResponse } from '@/types'

export const transactionService = {
  async list(groupId: number, filters: TransactionFilters = {}): Promise<PaginatedResponse<Transaction>> {
    const params = Object.fromEntries(Object.entries(filters).filter(([, v]) => v !== undefined && v !== ''))
    const res = await api.get(`/groups/${groupId}/transactions`, { params })
    return res.data
  },

  async get(groupId: number, id: number): Promise<Transaction> {
    const res = await api.get(`/groups/${groupId}/transactions/${id}`)
    return res.data.data
  },

  async create(groupId: number, data: Partial<Transaction> & { name: string }): Promise<Transaction> {
    const res = await api.post(`/groups/${groupId}/transactions`, data)
    return res.data.data
  },

  async update(groupId: number, id: number, data: Partial<Transaction> & { scope?: string }): Promise<Transaction> {
    const res = await api.patch(`/groups/${groupId}/transactions/${id}`, data)
    return res.data.data
  },

  async patch(groupId: number, id: number, field: string, value: unknown): Promise<Transaction> {
    const res = await api.patch(`/groups/${groupId}/transactions/${id}`, { [field]: value })
    return res.data.data
  },

  async remove(groupId: number, id: number, scope = 'this'): Promise<void> {
    await api.delete(`/groups/${groupId}/transactions/${id}`, { params: { scope } })
  },

  async history(groupId: number, id: number) {
    const res = await api.get(`/groups/${groupId}/transactions/${id}/history`)
    return res.data
  },
}
