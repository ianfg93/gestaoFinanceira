import api from './api'
import type { DashboardData } from '@/types'

export const dashboardService = {
  async monthly(groupId: number, month: string): Promise<DashboardData> {
    const res = await api.get(`/groups/${groupId}/dashboard`, { params: { month } })
    return res.data
  },

  async evolution(groupId: number, from: string, to: string) {
    const res = await api.get(`/groups/${groupId}/dashboard/evolution`, { params: { from, to } })
    return res.data
  },
}
