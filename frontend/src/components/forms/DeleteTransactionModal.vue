<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="emit('cancel')">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6">
      <h3 class="font-bold text-gray-900 mb-2">Confirmar exclus\u00E3o</h3>
      <p class="text-sm text-gray-500 mb-5">{{ description }}</p>

      <div v-if="options.length > 1" class="space-y-2">
        <button
          v-for="opt in options"
          :key="opt.value"
          @click="emit('confirm', opt.value)"
          class="w-full text-left p-3 rounded-lg border border-gray-200 hover:border-blue-400 hover:bg-blue-50 transition-colors"
        >
          <p class="text-sm font-medium text-gray-900">{{ opt.label }}</p>
          <p v-if="opt.description" class="text-xs text-gray-400 mt-0.5">{{ opt.description }}</p>
        </button>
      </div>

      <div v-else>
        <button @click="emit('confirm', options[0].value)" class="w-full btn-danger btn-sm">
          {{ options[0].label }}
        </button>
      </div>

      <button @click="emit('cancel')" class="mt-3 w-full btn-secondary btn-sm">Cancelar</button>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  description: string
  options: Array<{
    value: 'this' | 'future' | 'all'
    label: string
    description?: string
  }>
}>()

const emit = defineEmits<{ confirm: [scope: 'this' | 'future' | 'all']; cancel: [] }>()
</script>
