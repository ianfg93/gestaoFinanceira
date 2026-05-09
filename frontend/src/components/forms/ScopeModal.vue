<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
      <h3 class="font-bold text-gray-900 mb-2">Editar movimentação recorrente</h3>
      <p class="text-sm text-gray-500 mb-5">Esta movimentação faz parte de uma série. O que você deseja alterar?</p>
      <div class="space-y-2">
        <button
          v-for="opt in options" :key="opt.value"
          @click="emit('confirm', opt.value)"
          class="w-full text-left p-3 rounded-lg border border-gray-200 hover:border-blue-400 hover:bg-blue-50 transition-colors"
        >
          <p class="text-sm font-medium text-gray-900">{{ opt.label }}</p>
          <p class="text-xs text-gray-400 mt-0.5">{{ opt.description }}</p>
        </button>
      </div>
      <button @click="emit('cancel')" class="mt-4 w-full btn-secondary btn-sm">Cancelar</button>
    </div>
  </div>
</template>

<script setup lang="ts">
const emit = defineEmits<{ confirm: [scope: string]; cancel: [] }>()

const options = [
  { value: 'this',   label: 'Apenas esta ocorrência',      description: 'Altera somente o registro atual' },
  { value: 'future', label: 'Esta e as futuras',           description: 'Altera este e todos os próximos' },
  { value: 'all',    label: 'Toda a série',                description: 'Altera todos os registros da série' },
]
</script>
