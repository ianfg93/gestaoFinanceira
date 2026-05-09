<template>
  <aside class="w-60 bg-white border-r border-gray-200 flex flex-col flex-shrink-0 hidden lg:flex">
    <!-- Logo -->
    <div class="h-14 flex items-center px-5 border-b border-gray-200">
      <div class="flex items-center gap-2">
        <div class="w-7 h-7 bg-blue-600 rounded-lg flex items-center justify-center">
          <BanknotesIcon class="w-4 h-4 text-white" />
        </div>
        <span class="text-base font-bold text-gray-900">FinanceFlow</span>
      </div>
    </div>

    <!-- Group selector -->
    <div class="px-3 pt-3 pb-1">
      <select
        :value="authStore.currentGroup?.id"
        @change="switchGroup(+($event.target as HTMLSelectElement).value)"
        class="w-full text-xs rounded-lg border-gray-200 bg-gray-50 font-medium text-gray-700 focus:ring-blue-500 py-1.5"
      >
        <option v-for="g in authStore.groups" :key="g.id" :value="g.id">{{ g.name }}</option>
      </select>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-2 space-y-0.5 overflow-y-auto">
      <NavItem to="/dashboard" :icon="HomeIcon" label="Dashboard" />
      <div class="pt-2 pb-1">
        <span class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Meses</span>
      </div>
      <NavItem
        v-for="m in recentMonths"
        :key="m.value"
        :to="`/months/${m.value}`"
        :icon="CalendarDaysIcon"
        :label="m.label"
        :badge="m.current ? 'Atual' : undefined"
      />
      <div class="pt-2 pb-1">
        <span class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Configurar</span>
      </div>
      <NavItem to="/categories" :icon="TagIcon" label="Categorias" />
      <NavItem to="/tags" :icon="HashtagIcon" label="Tags" />
    </nav>

    <!-- Bottom -->
    <div class="p-3 border-t border-gray-200 space-y-0.5">
      <NavItem to="/notifications" :icon="BellIcon" label="Notificações" :badge="unreadCount > 0 ? String(unreadCount) : undefined" badge-variant="red" />
      <NavItem to="/settings" :icon="Cog6ToothIcon" label="Configurações" />
      <button @click="logout" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-100 transition-colors">
        <ArrowRightOnRectangleIcon class="w-4 h-4 flex-shrink-0" />
        <span>Sair</span>
      </button>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import {
  HomeIcon, CalendarDaysIcon, TagIcon, HashtagIcon,
  BellIcon, Cog6ToothIcon, ArrowRightOnRectangleIcon, BanknotesIcon
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notifications'
import { formatMonth, currentMonth } from '@/utils/format'
import NavItem from './NavItem.vue'

const authStore  = useAuthStore()
const notifStore = useNotificationStore()
const router     = useRouter()
const unreadCount = computed(() => notifStore.unreadCount)

function switchGroup(id: number) { authStore.switchGroup(id) }
async function logout() {
  await authStore.logout()
  router.push('/login')
}

const recentMonths = computed(() => {
  const curr = currentMonth()
  const months = []
  for (let i = -1; i <= 2; i++) {
    const d = new Date()
    d.setDate(1)
    d.setMonth(d.getMonth() + i)
    const val = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
    months.push({ value: val, label: formatMonth(val), current: val === curr })
  }
  return months
})
</script>
