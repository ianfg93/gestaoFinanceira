<template>
  <div class="card p-5">
    <div class="flex items-center justify-between mb-3">
      <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ label }}</span>
      <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="iconBg">
        <component :is="icon" class="w-4 h-4" :class="iconColor" />
      </div>
    </div>
    <p class="text-2xl font-bold" :class="valueColor">{{ formatted }}</p>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { formatCurrency } from '@/utils/format'

const props = defineProps<{ label: string; value: number; color: 'green' | 'red' | 'blue' | 'purple'; icon: any }>()

const colors = {
  green:  { bg: 'bg-green-100',  icon: 'text-green-600',  value: 'text-green-700' },
  red:    { bg: 'bg-red-100',    icon: 'text-red-600',    value: 'text-red-700' },
  blue:   { bg: 'bg-blue-100',   icon: 'text-blue-600',   value: 'text-blue-700' },
  purple: { bg: 'bg-purple-100', icon: 'text-purple-600', value: 'text-purple-700' },
}

const iconBg    = computed(() => colors[props.color].bg)
const iconColor = computed(() => colors[props.color].icon)
const valueColor = computed(() => colors[props.color].value)
const formatted  = computed(() => formatCurrency(props.value))
</script>
