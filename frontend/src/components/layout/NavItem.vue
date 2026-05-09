<template>
  <RouterLink
    :to="to"
    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors"
    :class="isActive
      ? 'bg-blue-50 text-blue-700 font-medium'
      : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
  >
    <component :is="icon" class="w-4 h-4 flex-shrink-0" />
    <span class="flex-1">{{ label }}</span>
    <span
      v-if="badge"
      class="text-xs px-1.5 py-0.5 rounded-full font-semibold"
      :class="badgeVariant === 'red' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'"
    >{{ badge }}</span>
  </RouterLink>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'

const props = defineProps<{
  to: string
  icon: any
  label: string
  badge?: string
  badgeVariant?: 'blue' | 'red'
}>()

const route = useRoute()
const isActive = computed(() => route.path === props.to || route.path.startsWith(props.to + '/'))
</script>
