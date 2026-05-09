import api from './api'
import type { Tag } from '@/types'

export const tagService = {
  async list(groupId: number): Promise<Tag[]> {
    const res = await api.get(`/groups/${groupId}/tags`)
    return res.data.data
  },
  async create(groupId: number, name: string, color?: string): Promise<Tag> {
    const res = await api.post(`/groups/${groupId}/tags`, { name, color })
    return res.data.data
  },
  async remove(groupId: number, id: number): Promise<void> {
    await api.delete(`/groups/${groupId}/tags/${id}`)
  },
}
