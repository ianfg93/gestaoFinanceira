import { defineStore } from 'pinia'
import { ref } from 'vue'
import { notificationService } from '@/services/notificationService'
import type { Notification } from '@/types'

export const useNotificationStore = defineStore('notifications', () => {
  const items       = ref<Notification[]>([])
  const unreadCount = ref(0)
  const loading     = ref(false)

  async function load() {
    loading.value = true
    try {
      const res = await notificationService.list()
      items.value = res.data
    } finally {
      loading.value = false
    }
  }

  async function loadUnreadCount() {
    unreadCount.value = await notificationService.unreadCount()
  }

  async function markRead(id: number) {
    await notificationService.markRead(id)
    const n = items.value.find(i => i.id === id)
    if (n) { n.read_at = new Date().toISOString(); unreadCount.value = Math.max(0, unreadCount.value - 1) }
  }

  async function markAllRead() {
    await notificationService.markAllRead()
    items.value.forEach(n => { if (!n.read_at) n.read_at = new Date().toISOString() })
    unreadCount.value = 0
  }

  return { items, unreadCount, loading, load, loadUnreadCount, markRead, markAllRead }
})
