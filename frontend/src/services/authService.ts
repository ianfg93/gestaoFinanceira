import api from './api'
import type { User, Group } from '@/types'

export const authService = {
  async register(data: { name: string; email: string; password: string; password_confirmation: string; group_name?: string }) {
    const res = await api.post('/auth/register', data)
    return res.data as { user: User; group: Group; token: string }
  },

  async login(email: string, password: string) {
    const res = await api.post('/auth/login', { email, password })
    return res.data as { user: User; group: Group | null; token: string }
  },

  async logout() {
    await api.post('/auth/logout')
  },

  async me() {
    const res = await api.get('/auth/me')
    return res.data
  },

  async switchGroup(groupId: number) {
    const res = await api.post('/auth/switch-group', { group_id: groupId })
    return res.data
  },
}
