<template>
  <div class="card">
    <div class="p-4 border-b border-gray-100">
      <h3 class="font-semibold text-sm text-gray-900">Evolução nos últimos 6 meses</h3>
    </div>
    <div class="p-4">
      <VueApexCharts type="bar" height="260" :options="chartOptions" :series="series" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import { useDashboardStore } from '@/stores/dashboard'
import { useAuthStore } from '@/stores/auth'
import { formatCurrency, formatMonth, prevMonth } from '@/utils/format'

const props  = defineProps<{ month: string }>()
const store  = useDashboardStore()
const auth   = useAuthStore()

const series = ref<any[]>([])
const chartOptions = ref({
  chart:   { type: 'bar' as const, stacked: false, toolbar: { show: false } },
  xaxis:   { categories: [] as string[] },
  colors:  ['#22c55e', '#ef4444', '#3b82f6'],
  dataLabels: { enabled: false },
  yaxis:   { labels: { formatter: (v: number) => 'R$ ' + (v / 1000).toFixed(1) + 'k' } },
  tooltip: { y: { formatter: (v: number) => formatCurrency(v) } },
  legend:  { position: 'top' as const },
  plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
})

async function load() {
  const groupId = auth.currentGroup?.id
  if (!groupId) return
  let from = props.month
  for (let i = 0; i < 5; i++) from = prevMonth(from)
  await store.loadEvolution(groupId, from, props.month)

  const months = Object.keys(store.evolution).sort()
  const incomeData     = months.map(m => store.evolution[m]?.income?.total || 0)
  const expenseData    = months.map(m => store.evolution[m]?.expense?.total || 0)
  const investmentData = months.map(m => store.evolution[m]?.investment?.total || 0)

  chartOptions.value = { ...chartOptions.value, xaxis: { categories: months.map(formatMonth) } }
  series.value = [
    { name: 'Receitas',      data: incomeData },
    { name: 'Despesas',      data: expenseData },
    { name: 'Investimentos', data: investmentData },
  ]
}

watch(() => props.month, load)
watch(() => auth.currentGroup?.id, load)
onMounted(load)
</script>
