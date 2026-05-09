<template>
  <div class="flex h-screen overflow-hidden bg-gray-50">
    <Sidebar />
    <div class="flex-1 flex flex-col overflow-hidden">
      <header class="h-14 bg-white border-b border-gray-200 flex items-center justify-between px-6 flex-shrink-0">
        <div class="flex items-center gap-2">
          <button class="lg:hidden p-2 rounded-lg hover:bg-gray-100" @click="sidebarOpen = !sidebarOpen">
            <Bars3Icon class="w-5 h-5" />
          </button>
          <h1 class="text-sm font-semibold text-gray-800">{{ pageTitle }}</h1>
        </div>
        <div class="flex items-center gap-3">
          <NotificationBell />
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-semibold">
              {{ authStore.user?.name?.charAt(0).toUpperCase() }}
            </div>
            <span class="text-sm font-medium text-gray-700 hidden sm:block">{{ authStore.user?.name }}</span>
          </div>
        </div>
      </header>
      <main class="flex-1 overflow-auto">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { RouterView, useRoute } from 'vue-router'
import { Bars3Icon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import Sidebar from './Sidebar.vue'
import NotificationBell from '@/components/notifications/NotificationBell.vue'

const authStore = useAuthStore()
const sidebarOpen = ref(false)
const route = useRoute()

const pageTitles: Record<string, string> = {
  dashboard: 'Dashboard',
  month: 'Movimentações Mensais',
  categories: 'Categorias',
  tags: 'Tags',
  notifications: 'Notificações',
  settings: 'Configurações',
}
const pageTitle = computed(() => pageTitles[route.name as string] ?? 'FinanceFlow')
</script>
