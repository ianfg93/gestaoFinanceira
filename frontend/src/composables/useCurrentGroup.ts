import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

export function useCurrentGroup() {
  const authStore = useAuthStore()
  const groupId   = computed(() => authStore.currentGroup?.id ?? 0)
  const group     = computed(() => authStore.currentGroup)
  return { groupId, group }
}
