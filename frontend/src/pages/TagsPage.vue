<template>
  <div class="p-6 max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-xl font-bold text-gray-900">Tags</h2>
      <button @click="showForm = true" class="btn-primary btn-sm"><PlusIcon class="w-4 h-4" /> Nova tag</button>
    </div>
    <div class="card p-4 flex flex-wrap gap-2">
      <div v-if="store.items.length === 0" class="text-sm text-gray-400 w-full text-center py-6">Nenhuma tag criada</div>
      <div v-for="tag in store.items" :key="tag.id" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium" :style="{ background: (tag.color || '#6b7280') + '20', color: tag.color || '#6b7280' }">
        <span>#{{ tag.name }}</span>
        <button @click="remove(tag)" class="hover:opacity-70 ml-1"><XMarkIcon class="w-3.5 h-3.5" /></button>
      </div>
    </div>

    <div v-if="showForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" @click.self="showForm = false">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
        <h3 class="font-bold text-gray-900 mb-4">Nova tag</h3>
        <form @submit.prevent="save" class="space-y-4">
          <div><label class="label">Nome</label><input v-model="form.name" type="text" class="input" placeholder="viagem" required /></div>
          <div><label class="label">Cor</label><input v-model="form.color" type="color" class="input h-10 p-1 cursor-pointer" /></div>
          <div class="flex gap-3 justify-end">
            <button type="button" @click="showForm = false" class="btn-secondary">Cancelar</button>
            <button type="submit" class="btn-primary">Criar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { PlusIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { useTagStore } from '@/stores/tags'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'vue-toastification'
import type { Tag } from '@/types'

const store    = useTagStore()
const auth     = useAuthStore()
const toast    = useToast()
const showForm = ref(false)
const form     = ref({ name: '', color: '#3b82f6' })

async function save() {
  await store.create(auth.currentGroup!.id, form.value.name, form.value.color)
  showForm.value = false; form.value = { name: '', color: '#3b82f6' }
  toast.success('Tag criada')
}
async function remove(tag: Tag) {
  if (!confirm(`Excluir "#${tag.name}"?`)) return
  await store.remove(auth.currentGroup!.id, tag.id)
}
onMounted(() => { if (auth.currentGroup?.id) store.load(auth.currentGroup.id) })
</script>
