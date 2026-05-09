<template>
  <div class="p-6 max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-bold text-gray-900">Notificações</h2>
      <button v-if="store.unreadCount > 0" @click="store.markAllRead()" class="btn-secondary btn-sm">
        Marcar todas como lidas
      </button>
    </div>

    <div class="card divide-y divide-gray-50">
      <div v-if="store.loading" class="p-8 text-center text-gray-400 text-sm">Carregando...</div>
      <div v-else-if="store.items.length === 0" class="p-8 text-center text-gray-400 text-sm">Nenhuma notificação</div>
      <div
        v-for="n in store.items"
        :key="n.id"
        @click="read(n)"
        class="p-4 flex items-start gap-3 cursor-pointer hover:bg-gray-50 transition-colors"
        :class="!n.read_at ? 'bg-blue-50/40' : ''"
      >
        <div class="w-2.5 h-2.5 rounded-full mt-1.5 flex-shrink-0" :class="!n.read_at ? 'bg-blue-500' : 'bg-gray-200'" />
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-900">{{ n.title }}</p>
          <p v-if="n.body" class="text-xs text-gray-500 mt-0.5">{{ n.body }}</p>
          <p class="text-xs text-gray-400 mt-1">{{ formatDate(n.created_at) }}</p>
        </div>
        <span :class="typeBadge(n.type)" class="badge text-xs">{{ typeLabel(n.type) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useNotificationStore } from '@/stores/notifications'
import { formatDate } from '@/utils/format'
import type { Notification } from '@/types'

const store  = useNotificationStore()
const router = useRouter()

onMounted(() => store.load())

async function read(n: Notification) {
  if (!n.read_at) await store.markRead(n.id)
  if (n.action_url) router.push(n.action_url)
}

const typeBadge = (t: string) => ({ due_tomorrow: 'badge-yellow', due_today: 'badge-red', overdue: 'badge-red', monthly_summary: 'badge-blue', system: 'badge-gray' }[t] ?? 'badge-gray')
const typeLabel = (t: string) => ({ due_tomorrow: 'Vence amanhã', due_today: 'Vence hoje', overdue: 'Atrasada', monthly_summary: 'Resumo', system: 'Sistema' }[t] ?? t)
</script>
