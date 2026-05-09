<template>
  <div class="relative">
    <button @click="open = !open" class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors">
      <BellIcon class="w-5 h-5 text-gray-600" />
      <span
        v-if="store.unreadCount > 0"
        class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold"
      >{{ store.unreadCount > 9 ? '9+' : store.unreadCount }}</span>
    </button>

    <Transition name="dropdown">
      <div v-if="open" class="absolute right-0 top-full mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 z-50">
        <div class="flex items-center justify-between p-4 border-b border-gray-100">
          <span class="font-semibold text-sm text-gray-900">Notificações</span>
          <button v-if="store.unreadCount > 0" @click="markAll" class="text-xs text-blue-600 hover:underline">Marcar todas como lidas</button>
        </div>
        <div class="max-h-80 overflow-y-auto divide-y divide-gray-50">
          <div v-if="store.items.length === 0" class="p-6 text-center text-sm text-gray-400">Nenhuma notificação</div>
          <div
            v-for="n in store.items.slice(0, 10)"
            :key="n.id"
            @click="read(n)"
            class="p-3 hover:bg-gray-50 cursor-pointer transition-colors"
            :class="!n.read_at ? 'bg-blue-50/50' : ''"
          >
            <div class="flex items-start gap-2">
              <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0" :class="!n.read_at ? 'bg-blue-500' : 'bg-transparent'" />
              <div>
                <p class="text-sm font-medium text-gray-900">{{ n.title }}</p>
                <p v-if="n.body" class="text-xs text-gray-500 mt-0.5">{{ n.body }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ formatTime(n.created_at) }}</p>
              </div>
            </div>
          </div>
        </div>
        <div class="p-3 border-t border-gray-100 text-center">
          <RouterLink to="/notifications" @click="open = false" class="text-xs text-blue-600 hover:underline">Ver todas</RouterLink>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { BellIcon } from '@heroicons/vue/24/outline'
import { useNotificationStore } from '@/stores/notifications'
import type { Notification } from '@/types'

const store  = useNotificationStore()
const router = useRouter()
const open   = ref(false)

onMounted(() => { store.load(); store.loadUnreadCount() })

function formatTime(dt: string) {
  return new Intl.RelativeTimeFormat('pt-BR', { numeric: 'auto' }).format(
    Math.round((new Date(dt).getTime() - Date.now()) / 60000), 'minute'
  )
}

async function read(n: Notification) {
  if (!n.read_at) await store.markRead(n.id)
  if (n.action_url) { open.value = false; router.push(n.action_url) }
}

async function markAll() { await store.markAllRead() }
</script>

<style scoped>
.dropdown-enter-active, .dropdown-leave-active { transition: all 0.15s ease; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: translateY(-8px); }
</style>
