import api from './api'
import type { Category } from '@/types'

export const categoryService = {
  async list(groupId: number): Promise<Category[]> {
    const res = await api.get(`/groups/${groupId}/categories`)
    return res.data.data
  },
  async create(groupId: number, data: Partial<Category>): Promise<Category> {
    const res = await api.post(`/groups/${groupId}/categories`, data)
    return res.data.data
  },
  async update(groupId: number, id: number, data: Partial<Category>): Promise<Category> {
    const res = await api.put(`/groups/${groupId}/categories/${id}`, data)
    return res.data.data
  },
  async remove(groupId: number, id: number): Promise<void> {
    await api.delete(`/groups/${groupId}/categories/${id}`)
  },
}
