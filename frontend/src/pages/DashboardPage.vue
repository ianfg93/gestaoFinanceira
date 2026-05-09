<template>
  <div class="p-6 space-y-6">
    <!-- Month navigation -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <button @click="changeMonth(-1)" class="btn-secondary btn-sm"><ChevronLeftIcon class="w-4 h-4" /></button>
        <h2 class="text-lg font-bold text-gray-900 min-w-32 text-center">{{ formatMonth(currentMonth) }}</h2>
        <button @click="changeMonth(1)" class="btn-secondary btn-sm"><ChevronRightIcon class="w-4 h-4" /></button>
      </div>
      <RouterLink :to="`/months/${currentMonth}`" class="btn-primary btn-sm">
        <TableCellsIcon class="w-4 h-4" /> Ver planilha
      </RouterLink>
    </div>

    <!-- Loading skeleton -->
    <div v-if="store.loading" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="i in 4" :key="i" class="card p-5 animate-pulse">
        <div class="h-4 bg-gray-200 rounded w-24 mb-3"></div>
        <div class="h-7 bg-gray-200 rounded w-32"></div>
      </div>
    </div>

    <template v-else-if="store.data">
      <!-- Summary cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <SummaryCard label="Receitas" :value="store.data.totals.income" color="green" :icon="ArrowTrendingUpIcon" />
        <SummaryCard label="Despesas" :value="store.data.totals.expense" color="red" :icon="ArrowTrendingDownIcon" />
        <SummaryCard label="Investido" :value="store.data.totals.investment" color="blue" :icon="ChartBarIcon" />
        <SummaryCard
          label="Saldo"
          :value="store.data.totals.balance"
          :color="store.data.totals.balance >= 0 ? 'green' : 'red'"
          :icon="ScaleIcon"
        />
      </div>

      <!-- Charts row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <ExpensesByCategory :data="store.data.by_category" />
        <MonthlyEvolution :month="currentMonth" />
      </div>

      <!-- Tables row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Overdue -->
        <div class="card">
          <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-sm text-gray-900 flex items-center gap-2">
              <ExclamationTriangleIcon class="w-4 h-4 text-red-500" />
              Atrasadas ({{ store.data.overdue.length }})
            </h3>
          </div>
          <div class="divide-y divide-gray-50">
            <div v-if="store.data.overdue.length === 0" class="p-4 text-sm text-gray-400 text-center">Nenhuma conta atrasada 🎉</div>
            <TransactionRow v-for="t in store.data.overdue" :key="t.id" :transaction="t" />
          </div>
        </div>

        <!-- Due soon -->
        <div class="card">
          <div class="p-4 border-b border-gray-100">
            <h3 class="font-semibold text-sm text-gray-900 flex items-center gap-2">
              <ClockIcon class="w-4 h-4 text-yellow-500" />
              Vencendo em 7 dias ({{ store.data.due_soon.length }})
            </h3>
          </div>
          <div class="divide-y divide-gray-50">
            <div v-if="store.data.due_soon.length === 0" class="p-4 text-sm text-gray-400 text-center">Nenhuma conta vencendo em breve</div>
            <TransactionRow v-for="t in store.data.due_soon" :key="t.id" :transaction="t" />
          </div>
        </div>
      </div>

      <!-- By responsible -->
      <ByResponsible v-if="store.data.by_responsible.length" :data="store.data.by_responsible" />
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import {
  ChevronLeftIcon, ChevronRightIcon, TableCellsIcon,
  ArrowTrendingUpIcon, ArrowTrendingDownIcon, ChartBarIcon, ScaleIcon,
  ExclamationTriangleIcon, ClockIcon
} from '@heroicons/vue/24/outline'
import { useDashboardStore } from '@/stores/dashboard'
import { useAuthStore } from '@/stores/auth'
import { currentMonth as getCurrent, formatMonth, prevMonth, nextMonth } from '@/utils/format'
import SummaryCard from '@/components/dashboard/SummaryCard.vue'
import ExpensesByCategory from '@/components/dashboard/ExpensesByCategory.vue'
import MonthlyEvolution from '@/components/dashboard/MonthlyEvolution.vue'
import ByResponsible from '@/components/dashboard/ByResponsible.vue'
import TransactionRow from '@/components/dashboard/TransactionRow.vue'

const store    = useDashboardStore()
const auth     = useAuthStore()
const currentMonth = ref(getCurrent())

async function load() {
  if (auth.currentGroup?.id) {
    await store.load(auth.currentGroup.id, currentMonth.value)
  }
}

function changeMonth(delta: number) {
  currentMonth.value = delta < 0 ? prevMonth(currentMonth.value) : nextMonth(currentMonth.value)
}

watch(currentMonth, load)
watch(() => auth.currentGroup?.id, load)
onMounted(load)
</script>
