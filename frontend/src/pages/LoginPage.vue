<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8">
      <div class="text-center mb-8">
        <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center mx-auto mb-3">
          <BanknotesIcon class="w-6 h-6 text-white" />
        </div>
        <h1 class="text-2xl font-bold text-gray-900">FinanceFlow</h1>
        <p class="text-sm text-gray-500 mt-1">Gestão financeira inteligente</p>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="label">E-mail</label>
          <input v-model="form.email" type="email" class="input" placeholder="seu@email.com" required />
        </div>
        <div>
          <label class="label">Senha</label>
          <input v-model="form.password" type="password" class="input" placeholder="••••••••" required />
        </div>
        <div v-if="error" class="text-sm text-red-600 bg-red-50 rounded-lg p-3">{{ error }}</div>
        <button type="submit" :disabled="loading" class="btn-primary w-full justify-center py-2.5">
          <span v-if="loading">Entrando...</span>
          <span v-else>Entrar</span>
        </button>
      </form>

      <p class="text-center text-sm text-gray-500 mt-6">
        Não tem conta?
        <RouterLink to="/register" class="text-blue-600 font-medium hover:underline">Criar conta</RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { BanknotesIcon } from '@heroicons/vue/24/outline'
import { useAuthStore } from '@/stores/auth'

const auth   = useAuthStore()
const router = useRouter()
const form   = ref({ email: '', password: '' })
const loading = ref(false)
const error   = ref('')

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await auth.login(form.value.email, form.value.password)
    router.push('/dashboard')
  } catch (e: any) {
    error.value = e.response?.data?.message ?? 'Erro ao entrar. Verifique suas credenciais.'
  } finally {
    loading.value = false
  }
}
</script>
