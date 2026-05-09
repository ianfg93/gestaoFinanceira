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
}
