<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="$emit('close')">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between p-5 border-b border-gray-100">
        <h3 class="text-base font-bold text-gray-900">Nova movimentação</h3>
        <button @click="$emit('close')" class="p-1 rounded-lg hover:bg-gray-100"><XMarkIcon class="w-5 h-5 text-gray-400" /></button>
      </div>

      <form @submit.prevent="submit" class="p-5 space-y-4">
        <!-- Type -->
        <div>
          <label class="label">Tipo</label>
          <div class="grid grid-cols-3 gap-2">
            <button
              v-for="t in types" :key="t.value" type="button"
              @click="form.type = t.value"
              class="py-2 px-3 rounded-lg border-2 text-sm font-medium transition-all"
              :class="form.type === t.value ? t.activeClass : 'border-gray-200 text-gray-600 hover:border-gray-300'"
            >{{ t.label }}</button>
          </div>
        </div>

        <!-- Name autocomplete -->
        <div class="relative">
          <label class="label">Nome <span class="text-red-500">*</span></label>
          <input
            v-model="form.name"
            type="text"
            class="input"
            placeholder="Ex: Internet, Aluguel, Salário..."
            required
            autocomplete="off"
            @input="searchNames"
          />
          <div v-if="nameSuggestions.length" class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1">
            <button
              v-for="s in nameSuggestions" :key="s.id"
              type="button"
              @click="selectName(s)"
              class="w-full px-3 py-2 text-left text-sm hover:bg-gray-50 flex items-center justify-between"
            >
              <span>{{ s.name }}</span>
              <span class="text-xs text-gray-400">usado {{ s.usage_count }}x</span>
            </button>
          </div>
        </div>

        <!-- Category -->
        <div>
          <label class="label">Categoria</label>
          <select v-model="form.category_id" class="input">
            <option :value="null">Sem categoria</option>
            <option v-for="cat in flatCategories" :key="cat.id" :value="cat.id">
              {{ cat.parent_id ? '  ↳ ' : '' }}{{ cat.name }}
            </option>
          </select>
        </div>

        <!-- Amount + Date -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label">Valor <span class="text-red-500">*</span></label>
            <input v-model.number="form.amount" type="number" step="0.01" min="0" class="input" required />
          </div>
          <div>
            <label class="label">Vencimento <span class="text-red-500">*</span></label>
            <input v-model="form.due_date" type="date" class="input" required />
          </div>
        </div>

        <!-- Status + Responsible -->
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label">Status</label>
            <select v-model="form.status" class="input">
              <option value="pending">Pendente</option>
              <option value="paid">Pago</option>
            </select>
          </div>
          <div>
            <label class="label">Responsável</label>
            <select v-model="form.responsible_id" class="input">
              <option :value="null">Nenhum</option>
              <option v-for="m in members" :key="m.id" :value="m.id">{{ m.name }}</option>
            </select>
          </div>
        </div>

        <!-- Tags -->
        <div>
          <label class="label">Tags</label>
          <div class="flex flex-wrap gap-2 p-2 border border-gray-300 rounded-lg min-h-10">
            <span
              v-for="tag in selectedTags" :key="tag.id"
              class="flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium cursor-pointer"
              :style="{ background: (tag.color || '#6b7280') + '20', color: tag.color || '#6b7280' }"
              @click="toggleTag(tag)"
            >
              #{{ tag.name }} <XMarkIcon class="w-3 h-3" />
            </span>
            <button type="button" @click="showTagPicker = !showTagPicker" class="text-xs text-gray-400 hover:text-gray-600 px-1">+ Tag</button>
          </div>
          <div v-if="showTagPicker" class="flex flex-wrap gap-1.5 mt-2">
            <button
              v-for="tag in availableTags" :key="tag.id"
              type="button"
              @click="toggleTag(tag)"
              class="px-2 py-0.5 rounded-full text-xs font-medium border"
              :style="{ borderColor: tag.color || '#6b7280', color: tag.color || '#6b7280' }"
            >#{{ tag.name }}</button>
          </div>
        </div>

        <!-- Transaction mode: single / recurring / installment -->
        <div class="border border-gray-200 rounded-lg p-4 space-y-3">
          <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Frequência</label>
          <div class="grid grid-cols-3 gap-2 mt-1">
            <label
              v-for="m in transactionModes" :key="m.value"
              class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg border-2 text-sm font-medium cursor-pointer transition-all"
              :class="transactionMode === m.value
                ? 'border-blue-500 bg-blue-50 text-blue-700'
                : 'border-gray-200 text-gray-500 hover:border-gray-300'"
            >
              <input type="radio" v-model="transactionMode" :value="m.value" class="sr-only" />
              {{ m.label }}
            </label>
          </div>

          <!-- Recurring options -->
          <div v-if="transactionMode === 'recurring'" class="space-y-3 pt-3 border-t border-gray-100">
            <div>
              <label class="label">Frequência</label>
              <select v-model="form.recurrence_type" class="input">
                <option value="monthly">Mensal</option>
                <option value="weekly">Semanal</option>
                <option value="biweekly">Quinzenal</option>
                <option value="yearly">Anual</option>
              </select>
            </div>
            <div>
              <label class="label">Termina</label>
              <select v-model="recurrenceDuration" class="input">
                <option value="indefinite">Indefinidamente</option>
                <option value="until">Até uma data</option>
              </select>
            </div>
            <div v-if="recurrenceDuration === 'until'">
              <label class="label">Data final</label>
              <input v-model="form.ends_at" type="date" class="input" />
            </div>
          </div>

          <!-- Installment options -->
          <div v-if="transactionMode === 'installment'" class="pt-3 border-t border-gray-100">
            <label class="label">Número de parcelas <span class="text-red-500">*</span></label>
            <input
              v-model.number="installmentCount"
              type="number"
              min="2"
              max="360"
              class="input"
              placeholder="Ex: 12"
              required
            />
            <p v-if="installmentCount >= 2" class="text-xs text-gray-400 mt-1">
              {{ installmentCount }}x de {{ formatCurrency(form.amount / installmentCount) }}
            </p>
          </div>
        </div>

        <!-- Notes -->
        <div>
          <label class="label">Observação</label>
          <textarea v-model="form.notes" class="input" rows="2" placeholder="Anotações opcionais..."></textarea>
        </div>

        <!-- Error -->
        <div v-if="error" class="text-sm text-red-600 bg-red-50 rounded-lg p-3">{{ error }}</div>

        <!-- Actions -->
        <div class="flex gap-3 justify-end pt-2">
          <button type="button" @click="$emit('close')" class="btn-secondary">Cancelar</button>
          <button type="submit" :disabled="saving" class="btn-primary">
            <span v-if="saving">Criando...</span>
            <span v-else-if="transactionMode === 'installment'">Criar {{ installmentCount ?? '?' }} parcelas</span>
            <span v-else-if="transactionMode === 'recurring'">Criar série recorrente</span>
            <span v-else>Criar movimentação</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import { useDebounceFn } from '@vueuse/core'
import { useTransactionStore } from '@/stores/transactions'
import { useAuthStore } from '@/stores/auth'
import { useCategoryStore } from '@/stores/categories'
import { useTagStore } from '@/stores/tags'
import { transactionNameService } from '@/services/transactionNameService'
import { groupService } from '@/services/groupService'
import { formatCurrency } from '@/utils/format'
import type { Tag } from '@/types'

const props = defineProps<{ month: string }>()
const emit  = defineEmits<{ close: []; created: [] }>()

const transStore  = useTransactionStore()
const authStore   = useAuthStore()
const catStore    = useCategoryStore()
const tagStore    = useTagStore()

const saving    = ref(false)
const error     = ref('')
const members   = ref<any[]>([])
const nameSuggestions = ref<any[]>([])
const showTagPicker   = ref(false)
const selectedTagIds  = ref<number[]>([])
const recurrenceDuration = ref('indefinite')

// Single radio state for transaction mode — prevents both being active at once
const transactionMode = ref<'single' | 'recurring' | 'installment'>('single')
const installmentCount = ref<number | null>(null)  // null = empty, forces user to type

const transactionModes = [
  { value: 'single',      label: 'Única'      },
  { value: 'recurring',   label: 'Recorrente' },
  { value: 'installment', label: 'Parcelado'  },
]

const form = ref({
  type: 'expense' as string,
  name: '',
  category_id: null as number | null,
  amount: 0,
  status: 'pending',
  due_date: props.month + '-01',
  responsible_id: null as number | null,
  notes: '',
  recurrence_type: 'monthly',
  ends_at: undefined as string | undefined,
})

const types = [
  { value: 'expense',    label: 'Despesa',      activeClass: 'border-red-500 bg-red-50 text-red-700' },
  { value: 'income',     label: 'Receita',       activeClass: 'border-green-500 bg-green-50 text-green-700' },
  { value: 'investment', label: 'Investimento',  activeClass: 'border-blue-500 bg-blue-50 text-blue-700' },
]

const flatCategories = computed(() => catStore.flat())
const selectedTags   = computed(() => tagStore.items.filter(t => selectedTagIds.value.includes(t.id)))
const availableTags  = computed(() => tagStore.items.filter(t => !selectedTagIds.value.includes(t.id)))

function toggleTag(tag: Tag) {
  const i = selectedTagIds.value.indexOf(tag.id)
  if (i >= 0) selectedTagIds.value.splice(i, 1)
  else selectedTagIds.value.push(tag.id)
}

const searchNames = useDebounceFn(async () => {
  const q = form.value.name.trim()
  if (q.length < 2) { nameSuggestions.value = []; return }
  const gid = authStore.currentGroup?.id
  if (!gid) return
  nameSuggestions.value = await transactionNameService.search(gid, q)
}, 300)

function selectName(s: any) {
  form.value.name = s.name
  if (s.category_id && !form.value.category_id) form.value.category_id = s.category_id
  nameSuggestions.value = []
}

async function submit() {
  error.value = ''

  if (transactionMode.value === 'installment') {
    if (!installmentCount.value || installmentCount.value < 2) {
      error.value = 'Informe o número de parcelas (mínimo 2).'
      return
    }
  }

  saving.value = true
  try {
    const gid = authStore.currentGroup!.id
    await transStore.create(gid, {
      ...form.value,
      is_series:           transactionMode.value === 'recurring',
      is_installment:      transactionMode.value === 'installment',
      total_installments:  transactionMode.value === 'installment' ? installmentCount.value : undefined,
      ends_at:             transactionMode.value === 'recurring' && recurrenceDuration.value !== 'indefinite'
                             ? form.value.ends_at
                             : undefined,
      tags: selectedTagIds.value as any,
    } as any)
    emit('created')
  } catch (e: any) {
    const errs = e.response?.data?.errors
    error.value = errs ? Object.values(errs).flat().join(' ') : 'Erro ao criar movimentação.'
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  const gid = authStore.currentGroup?.id
  if (!gid) return
  members.value = await groupService.members(gid)
  if (!catStore.items.length) catStore.load(gid)
  if (!tagStore.items.length) tagStore.load(gid)
})
</script>
