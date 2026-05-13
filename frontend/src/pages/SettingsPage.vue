<template>
  <div class="p-6 max-w-2xl mx-auto space-y-6">
    <h2 class="text-xl font-bold text-gray-900">Configurações</h2>

    <div class="card p-6">
      <h3 class="font-semibold text-gray-900 mb-4">Notificações por e-mail</h3>
      <div class="space-y-3">
        <Toggle v-model="prefs.email_enabled"         label="Ativar e-mails" />
        <Toggle v-model="prefs.email_due_tomorrow"    label="Vencimento amanhã" :disabled="!prefs.email_enabled" />
        <Toggle v-model="prefs.email_due_today"       label="Vencimento hoje" :disabled="!prefs.email_enabled" />
        <Toggle v-model="prefs.email_overdue_daily"   label="Atrasadas (diário)" :disabled="!prefs.email_enabled" />
        <Toggle v-model="prefs.email_monthly_summary" label="Resumo mensal" :disabled="!prefs.email_enabled" />
      </div>
    </div>

    <div class="card p-6">
      <h3 class="font-semibold text-gray-900 mb-4">Notificações no sistema</h3>
      <Toggle v-model="prefs.in_app_enabled" label="Ativar notificações no app" />
    </div>

    <button @click="save" :disabled="saving" class="btn-primary">{{ saving ? 'Salvando...' : 'Salvar preferências' }}</button>

    <div class="card p-6">
      <h3 class="font-semibold text-gray-900 mb-4">Membros do grupo</h3>
      <p class="text-sm text-gray-500 mb-4">Grupo: <strong>{{ auth.currentGroup?.name }}</strong></p>
      <div class="space-y-3">
        <div v-for="m in members" :key="m.id" class="border border-gray-200 rounded-xl p-3">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold">{{ m.name.charAt(0) }}</div>
            <div class="flex-1">
              <p class="text-sm font-medium">{{ m.name }}</p>
              <p class="text-xs text-gray-400">{{ m.email }}</p>
            </div>
            <span class="badge badge-blue">{{ m.role }}</span>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <input v-model="m.editName" type="text" class="input" placeholder="Nome" />
            <input v-model="m.editEmail" type="email" class="input" placeholder="Email" />
            <input v-model="m.editPassword" type="password" class="input md:col-span-2" placeholder="Nova senha (opcional, min 8)" />
          </div>
          <div class="mt-2 flex justify-end">
            <button @click="saveMember(m)" :disabled="m.saving" class="btn-primary btn-sm">{{ m.saving ? 'Salvando...' : 'Salvar membro' }}</button>
          </div>
        </div>
      </div>
      <div class="mt-4 pt-4 border-t border-gray-100">
        <h4 class="text-sm font-medium text-gray-700 mb-2">Convidar membro</h4>
        <div class="flex gap-2">
          <input v-model="inviteEmail" type="email" class="input flex-1" placeholder="email@exemplo.com" />
          <button @click="sendInvite" :disabled="inviting" class="btn-primary btn-sm">{{ inviting ? '...' : 'Convidar' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { notificationService } from '@/services/notificationService'
import { groupService } from '@/services/groupService'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'vue-toastification'
import Toggle from '@/components/ui/Toggle.vue'

const auth    = useAuthStore()
const toast   = useToast()
const prefs   = ref({ email_enabled: true, email_due_tomorrow: true, email_due_today: true, email_overdue_daily: true, email_monthly_summary: true, in_app_enabled: true })
const saving  = ref(false)
const members = ref<any[]>([])
const inviteEmail = ref('')
const inviting    = ref(false)

async function save() {
  saving.value = true
  try { await notificationService.updatePreferences(prefs.value); toast.success('Preferências salvas') }
  finally { saving.value = false }
}

async function sendInvite() {
  if (!inviteEmail.value) return
  inviting.value = true
  try { await groupService.invite(auth.currentGroup!.id, inviteEmail.value); toast.success('Convite enviado!'); inviteEmail.value = '' }
  catch { toast.error('Erro ao enviar convite') }
  finally { inviting.value = false }
}

function withMemberForm(list: any[]) {
  return list.map((m: any) => ({
    ...m,
    editName: m.name,
    editEmail: m.email,
    editPassword: '',
    saving: false,
  }))
}

async function saveMember(member: any) {
  const payload: any = {}
  if (member.editName !== member.name) payload.name = member.editName
  if (member.editEmail !== member.email) payload.email = member.editEmail
  if (member.editPassword) payload.password = member.editPassword

  if (Object.keys(payload).length === 0) {
    toast.info('Nenhuma alteração para salvar')
    return
  }

  member.saving = true
  try {
    await groupService.updateMember(auth.currentGroup!.id, member.id, payload)
    member.name = member.editName
    member.email = member.editEmail
    member.editPassword = ''
    toast.success('Membro atualizado!')
  } catch {
    toast.error('Erro ao atualizar membro')
  } finally {
    member.saving = false
  }
}

onMounted(async () => {
  prefs.value   = await notificationService.getPreferences()
  members.value = withMemberForm(await groupService.members(auth.currentGroup!.id))
})
</script>
