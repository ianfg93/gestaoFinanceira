import { defineStore } from 'pinia'
import { ref } from 'vue'
import { dashboardService } from '@/services/dashboardService'
import type { DashboardData } from '@/types'

export const useDashboardStore = defineStore('dashboard', () => {
  const data    = ref<DashboardData | null>(null)
  const loading = ref(false)
  const evolution = ref<Record<string, any>>({})

  async function load(groupId: number, month: string) {
    loading.value = true
    try {
      data.value = await dashboardService.monthly(groupId, month)
    } finally {
      loading.value = false
    }
  }

  async function loadEvolution(groupId: number, from: string, to: string) {
    evolution.value = await dashboardService.evolution(groupId, from, to)
  }

  return { data, loading, evolution, load, loadEvolution }
})
