<template>
  <div class="p-6 max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-bold text-gray-900">Categorias</h2>
      <button @click="openForm()" class="btn-primary btn-sm"><PlusIcon class="w-4 h-4" /> Nova categoria</button>
    </div>

    <div class="card divide-y divide-gray-50">
      <div v-if="store.loading" class="p-8 text-center text-gray-400 text-sm">Carregando...</div>
      <div v-else-if="store.items.length === 0" class="p-8 text-center text-gray-400 text-sm">Nenhuma categoria. Crie a primeira!</div>
      <div v-for="cat in store.items" :key="cat.id" class="p-4 flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center" :style="{ background: cat.color + '20', color: cat.color || '#6b7280' }">
          <span class="text-base">{{ cat.icon || '📁' }}</span>
        </div>
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-900">{{ cat.name }}</p>
          <p class="text-xs text-gray-400">{{ typeLabel(cat.type) }}</p>
        </div>
        <div class="flex items-center gap-2">
          <button @click="openForm(cat)" class="btn-ghost btn-sm"><PencilIcon class="w-3.5 h-3.5" /></button>
          <button @click="remove(cat)" class="btn-ghost btn-sm text-red-500 hover:bg-red-50"><TrashIcon class="w-3.5 h-3.5" /></button>
        </div>
      </div>
    </div>

    <!-- Form modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="showForm = false">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <h3 class="font-bold text-gray-900 mb-4">{{ editing ? 'Editar' : 'Nova' }} categoria</h3>
        <form @submit.prevent="save" class="space-y-4">
          <div>
            <label class="label">Nome</label>
            <input v-model="form.name" type="text" class="input" required />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="label">Cor</label>
              <input v-model="form.color" type="color" class="input h-10 p-1 cursor-pointer" />
            </div>
            <div>
              <label class="label">Tipo</label>
              <select v-model="form.type" class="input">
                <option value="all">Todos</option>
                <option value="expense">Despesa</option>
                <option value="income">Receita</option>
                <option value="investment">Investimento</option>
              </select>
            </div>
          </div>
          <div>
            <label class="label">Ícone (emoji)</label>
            <input v-model="form.icon" type="text" class="input" placeholder="🏠" maxlength="4" />
          </div>
          <div class="flex gap-3 justify-end pt-2">
            <button type="button" @click="showForm = false" class="btn-secondary">Cancelar</button>
            <button type="submit" :disabled="saving" class="btn-primary">{{ saving ? 'Salvando...' : 'Salvar' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'
import { useCategoryStore } from '@/stores/categories'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'vue-toastification'
import type { Category } from '@/types'

const store   = useCategoryStore()
const auth    = useAuthStore()
const toast   = useToast()
const showForm = ref(false)
const editing  = ref<Category | null>(null)
const saving   = ref(false)
const form     = ref({ name: '', color: '#3b82f6', type: 'all', icon: '' })

const typeLabel = (t: string) => ({ all: 'Todos', expense: 'Despesa', income: 'Receita', investment: 'Investimento' }[t] ?? t)

function openForm(cat?: Category) {
  editing.value = cat ?? null
  form.value = cat ? { name: cat.name, color: cat.color || '#3b82f6', type: cat.type, icon: cat.icon || '' } : { name: '', color: '#3b82f6', type: 'all', icon: '' }
  showForm.value = true
}

async function save() {
  saving.value = true
  try {
    const gid = auth.currentGroup!.id
    if (editing.value) { await store.update(gid, editing.value.id, form.value); toast.success('Categoria atualizada') }
    else { await store.create(gid, form.value); toast.success('Categoria criada') }
    showForm.value = false
  } catch { toast.error('Erro ao salvar') }
  finally { saving.value = false }
}

async function remove(cat: Category) {
  if (!confirm(`Excluir "${cat.name}"?`)) return
  await store.remove(auth.currentGroup!.id, cat.id)
  toast.success('Categoria excluída')
}

onMounted(() => { if (auth.currentGroup?.id) store.load(auth.currentGroup.id) })
</script>
