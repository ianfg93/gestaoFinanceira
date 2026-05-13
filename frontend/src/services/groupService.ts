import api from './api'

export const groupService = {
  async list() {
    const res = await api.get('/groups')
    return res.data.data
  },
  async members(groupId: number) {
    const res = await api.get(`/groups/${groupId}/members`)
    return res.data
  },
  async invite(groupId: number, email: string, role = 'editor') {
    const res = await api.post(`/groups/${groupId}/invite`, { email, role })
    return res.data
  },
  async updateMember(groupId: number, userId: number, payload: { name?: string; email?: string; password?: string; role?: 'admin' | 'editor' | 'viewer' }) {
    const res = await api.patch(`/groups/${groupId}/members/${userId}`, payload)
    return res.data
  },
}
