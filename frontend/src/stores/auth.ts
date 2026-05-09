import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService } from '@/services/authService'
import type { User, Group } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const user         = ref<User | null>(null)
  const currentGroup = ref<Group | null>(null)
  const groups       = ref<Array<Group & { role: string }>>([])
  const token        = ref<string | null>(localStorage.getItem('auth_token'))

  const isAuthenticated = computed(() => !!user.value && !!token.value)

  async function login(email: string, password: string) {
    const data = await authService.login(email, password)
    token.value = data.token
    user.value  = data.user
    if (data.group) currentGroup.value = data.group
    localStorage.setItem('auth_token', data.token)
  }

  async function register(payload: { name: string; email: string; password: string; password_confirmation: string; group_name?: string }) {
    const data = await authService.register(payload)
    token.value = data.token
    user.value  = data.user
    currentGroup.value = data.group
    localStorage.setItem('auth_token', data.token)
  }

  async function logout() {
    try { await authService.logout() } catch {}
    user.value  = null
    token.value = null
    currentGroup.value = null
    localStorage.removeItem('auth_token')
  }

  async function fetchMe() {
    try {
      const data = await authService.me()
      user.value         = data.user
      currentGroup.value = data.current_group
      groups.value       = data.groups ?? []
    } catch {
      token.value = null
      localStorage.removeItem('auth_token')
    }
  }

  async function switchGroup(groupId: number) {
    await authService.switchGroup(groupId)
    const g = groups.value.find(g => g.id === groupId)
    if (g) currentGroup.value = g
  }

  return { user, currentGroup, groups, token, isAuthenticated, login, register, logout, fetchMe, switchGroup }
})
