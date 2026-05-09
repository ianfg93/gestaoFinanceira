<template>
  <div class="px-4 py-3 flex items-center gap-3">
    <div class="w-1.5 h-8 rounded-full flex-shrink-0" :style="{ background: transaction.category?.color || '#e5e7eb' }" />
    <div class="flex-1 min-w-0">
      <p class="text-sm font-medium text-gray-900 truncate">{{ transaction.name }}</p>
      <p class="text-xs text-gray-400">{{ transaction.category?.name }} · Vence {{ formatDate(transaction.due_date) }}</p>
    </div>
    <div class="text-right">
      <p class="text-sm font-semibold text-red-600">{{ formatCurrency(transaction.amount) }}</p>
      <span :class="STATUS_CLASSES[transaction.status]">{{ STATUS_LABELS[transaction.status] }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Transaction } from '@/types'
import { formatCurrency, formatDate, STATUS_CLASSES, STATUS_LABELS } from '@/utils/format'
defineProps<{ transaction: Transaction }>()
</script>
