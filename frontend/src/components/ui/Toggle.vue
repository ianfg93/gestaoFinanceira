<template>
  <label class="flex items-center gap-3 cursor-pointer" :class="disabled ? 'opacity-50 cursor-not-allowed' : ''">
    <button
      type="button"
      :disabled="disabled"
      @click="!disabled && emit('update:modelValue', !modelValue)"
      class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
      :class="modelValue ? 'bg-blue-600' : 'bg-gray-200'"
    >
      <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform" :class="modelValue ? 'translate-x-4' : 'translate-x-0.5'" />
    </button>
    <span class="text-sm text-gray-700">{{ label }}</span>
  </label>
</template>

<script setup lang="ts">
defineProps<{ modelValue: boolean; label: string; disabled?: boolean }>()
const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>()
</script>
