<template>
  <div class="card">
    <div class="p-4 border-b border-gray-100">
      <h3 class="font-semibold text-sm text-gray-900">Despesas por Categoria</h3>
    </div>
    <div class="p-4">
      <div v-if="!data || data.length === 0" class="text-center text-sm text-gray-400 py-8">Sem despesas neste mês</div>
      <VueApexCharts v-else type="donut" height="260" :options="chartOptions" :series="series" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import { formatCurrency } from '@/utils/format'

const props = defineProps<{ data: Array<{ id: number; name: string; color: string | null; total: number }> }>()

const series = computed(() => props.data.map(d => d.total))
const chartOptions = computed(() => ({
  chart:    { type: 'donut' as const, toolbar: { show: false } },
  labels:   props.data.map(d => d.name),
  colors:   props.data.map(d => d.color || '#3b82f6'),
  legend:   { position: 'bottom' as const, fontSize: '12px' },
  dataLabels: { enabled: false },
  tooltip:  { y: { formatter: (v: number) => formatCurrency(v) } },
  plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', formatter: (w: any) => formatCurrency(w.globals.seriesTotals.reduce((a: number, b: number) => a + b, 0)) } } } } },
}))
</script>
