<template>
  <div class="card">
    <div class="p-4 border-b border-gray-100">
      <h3 class="font-semibold text-sm text-gray-900">Por Responsável</h3>
    </div>
    <div class="p-4">
      <VueApexCharts type="bar" height="200" :options="chartOptions" :series="series" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import { formatCurrency } from '@/utils/format'

const props = defineProps<{ data: Array<{ id: number; name: string; type: string; total: number }> }>()

const members = computed(() => [...new Set(props.data.map(d => d.name))])
const getTotal = (name: string, type: string) =>
  props.data.find(d => d.name === name && d.type === type)?.total ?? 0

const series = computed(() => [
  { name: 'Despesas',      data: members.value.map(m => getTotal(m, 'expense')) },
  { name: 'Investimentos', data: members.value.map(m => getTotal(m, 'investment')) },
])

const chartOptions = computed(() => ({
  chart:  { type: 'bar' as const, toolbar: { show: false } },
  xaxis:  { categories: members.value },
  colors: ['#ef4444', '#3b82f6'],
  dataLabels: { enabled: false },
  tooltip: { y: { formatter: (v: number) => formatCurrency(v) } },
  plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
}))
</script>
