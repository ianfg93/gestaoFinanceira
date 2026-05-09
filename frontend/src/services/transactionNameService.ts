import api from './api'

export const transactionNameService = {
  async search(groupId: number, q: string) {
    const res = await api.get(`/groups/${groupId}/transaction-names`, { params: { q } })
    return res.data as Array<{ id: number; name: string; category_id: number | null; usage_count: number }>
  },
}
