import api from './api'

export const notificationService = {
  async list(page = 1) {
    const res = await api.get('/notifications', { params: { page } })
    return res.data
  },
  async unreadCount(): Promise<number> {
    const res = await api.get('/notifications/unread-count')
    return res.data.count
  },
  async markRead(id: number) {
    await api.patch(`/notifications/${id}/read`)
  },
  async markAllRead() {
    await api.post('/notifications/read-all')
  },
  async getPreferences() {
    const res = await api.get('/notifications/preferences')
    return res.data
  },
  async updatePreferences(data: Record<string, boolean>) {
    const res = await api.put('/notifications/preferences', data)
    return res.data
  },
}
