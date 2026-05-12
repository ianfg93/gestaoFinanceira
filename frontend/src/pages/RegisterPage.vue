<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8">
      <div class="text-center mb-8">
        <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center mx-auto mb-3">
          <BanknotesIcon class="w-6 h-6 text-white" />
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Criar conta</h1>
        <p class="text-sm text-gray-500 mt-1">Comece a organizar suas finanças</p>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="label">Nome</label>
          <input v-model="form.name" type="text" class="input" placeholder="Seu nome" required />
        </div>
        <div>
          <label class="label">E-mail</label>
          <input v-model="form.email" type="email" class="input" placeholder="seu@email.com" required />
        </div>
        <div>
          <label class="label">Nome do grupo / família</label>
          <input v-model="form.group_name" type="text" class="input" placeholder="Ex: Casa Ian e Helena" />
        </div>
        <div>
          <label class="label">Senha</label>
          <input v-model="form.password" type="password" class="input" placeholder="Mínimo 8 caracteres" required />
        </div>
        <div>
          <label class="label">Confirmar senha</label>
          <input v-model="form.password_confirmation" type="password" class="input" placeholder="••••••••" required />
        </div>
        <div v-if="error" class="text-sm text-red-600 bg-red-50 rounded-lg p-3">{{ error }}</div>
        <button type="submit" :disabled="loading" class="btn-primary w-full justify-center py-2.5">
          <span v-if="loading">Criando conta...</span>
          <span v-else>Criar conta</span>
        </button>
      </form>

      <p class="text-center text-sm text-gray-500 mt-6">
        Já tem conta?
        <RouterLink to="/login" class="text-blue-600 font-medium hover:underline">Entrar</RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { BanknotesIcon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'
import { currentMonth } from '@/utils/format'

const auth = useAuthStore()
const router = useRouter()
const form = ref({ name: '', email: '', password: '', password_confirmation: '', group_name: '' })
const loading = ref(false)
const error   = ref('')

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await auth.register(form.value)
    router.push(`/months/${currentMonth()}`)
  } catch (e: any) {
    const errs = e.response?.data?.errors
    error.value = errs ? Object.values(errs).flat().join(' ') : 'Erro ao criar conta.'
  } finally {
    loading.value = false
  }
}
</script>
