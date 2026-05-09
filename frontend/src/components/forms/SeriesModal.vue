<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="$emit('close')">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[80vh] flex flex-col">

      <!-- Header -->
      <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 flex-shrink-0">
        <div>
          <h3 class="text-base font-bold text-gray-900">{{ title }}</h3>
          <p class="text-xs text-gray-400 mt-0.5">{{ summary }}</p>
        </div>
        <button @click="$emit('close')" class="p-1 rounded-lg hover:bg-gray-100">
          <XMarkIcon class="w-5 h-5 text-gray-400" />
        </button>
      </div>

      <!-- Body -->
      <div class="flex-1 overflow-y-auto">
        <div v-if="loading" class="flex items-center justify-center py-12">
          <div class="w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
        </div>

        <table v-else class="w-full text-sm">
          <thead class="bg-gray-50 sticky top-0">
            <tr>
              <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500 w-20">Parcela</th>
              <th class="text-left px-4 py-2 text-xs font-semibold text-gray-500">Vencimento</th>
              <th class="text-right px-4 py-2 text-xs font-semibold text-gray-500">Valor</th>
              <th class="text-center px-4 py-2 text-xs font-semibold text-gray-500">Status</th>
              <th class="w-10 px-2 py-2"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr
              v-for="t in transactions"
              :key="t.id"
              class="hover:bg-gray-50 transition-colors"
              :class="{ 'opacity-60': t.status === 'cancelled' }"
            >
              <td class="px-4 py-2.5 font-medium text-gray-700">
                {{ t.installment_number }}/{{ t.total_installments }}
              </td>
              <td class="px-4 py-2.5 text-gray-600">{{ formatDate(t.due_date) }}</td>
              <td class="px-4 py-2.5 text-right font-semibold text-gray-800">{{ formatCurrency(t.amount) }}</td>
              <td class="px-4 py-2.5 text-center">
                <button
                  @click="toggleStatus(t)"
                  :disabled="togglingId === t.id"
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium transition-all"
                  :class="statusClass(t.status)"
                  :title="t.status === 'paid' ? 'Marcar como pendente' : 'Marcar como pago'"
                >
                  <span v-if="togglingId === t.id" class="w-3 h-3 border border-current border-t-transparent rounded-full animate-spin mr-1"></span>
                  {{ STATUS_LABELS[t.status] ?? t.status }}
                </button>
              </td>
              <td class="px-2 py-2.5 text-center text-xs text-gray-400">
                <span v-if="t.reference_month === currentMonth" class="text-blue-500 font-medium" title="Mês atual">●</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Footer totals -->
      <div class="flex-shrink-0 px-5 py-3 border-t border-gray-100 bg-gray-50 rounded-b-2xl flex items-center gap-5 text-sm">
        <span class="text-gray-500">{{ paidCount }}/{{ transactions.length }} pagas</span>
        <span class="text-green-600 font-medium">Pago: {{ formatCurrency(paidTotal) }}</span>
        <span class="text-orange-600 font-medium ml-auto">Restante: {{ formatCurrency(remainingTotal) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { transactionService } from '@/services/transactionService'
import { formatCurrency, formatDate, STATUS_LABELS } from '@/utils/format'
import { useToast } from 'vue-toastification'
import type { Transaction } from '@/types'

const props = defineProps<{ seriesId: number; currentMonth: string }>()
const emit  = defineEmits<{ close: []; updated: [] }>()

const authStore = useAuthStore()
const toast     = useToast()

const loading     = ref(true)
const togglingId  = ref<number | null>(null)
const transactions = ref<Transaction[]>([])

const title = computed(() => {
  const first = transactions.value[0]
  return first ? first.name : 'Série de parcelas'
})

const summary = computed(() => {
  const n = transactions.value.length
  return n ? `${n} parcela${n > 1 ? 's' : ''} no total` : ''
})

const paidCount    = computed(() => transactions.value.filter(t => t.status === 'paid').length)
const paidTotal    = computed(() => transactions.value.filter(t => t.status === 'paid').reduce((s, t) => s + t.amount, 0))
const remainingTotal = computed(() => transactions.value.filter(t => t.status !== 'paid' && t.status !== 'cancelled').reduce((s, t) => s + t.amount, 0))

function statusClass(status: string) {
  const map: Record<string, string> = {
    paid:      'bg-green-100 text-green-700 hover:bg-green-200',
    pending:   'bg-yellow-100 text-yellow-700 hover:bg-yellow-200',
    overdue:   'bg-red-100 text-red-700 hover:bg-red-200',
    cancelled: 'bg-gray-100 text-gray-500',
    partial:   'bg-blue-100 text-blue-700 hover:bg-blue-200',
  }
  return map[status] ?? 'bg-gray-100 text-gray-600'
}

async function toggleStatus(t: Transaction) {
  if (togglingId.value) return
  const newStatus = t.status === 'paid' ? 'pending' : 'paid'
  const newPaidDate = newStatus === 'paid' ? new Date().toISOString().split('T')[0] : null

  togglingId.value = t.id
  try {
    const gid = authStore.currentGroup!.id
    await transactionService.patch(gid, t.id, 'status', newStatus)
    if (newStatus === 'paid') await transactionService.patch(gid, t.id, 'paid_date', newPaidDate)
    t.status    = newStatus as any
    t.paid_date = newPaidDate
    emit('updated')
  } catch {
    toast.error('Não foi possível atualizar o status.')
  } finally {
    togglingId.value = null
  }
}

onMounted(async () => {
  const gid = authStore.currentGroup?.id
  if (!gid) return
  try {
    const res = await transactionService.list(gid, { series_id: String(props.seriesId), per_page: 360 })
    transactions.value = res.data.sort((a, b) => (a.installment_number ?? 0) - (b.installment_number ?? 0))
  } catch {
    toast.error('Não foi possível carregar as parcelas.')
  } finally {
    loading.value = false
  }
})
</script>
