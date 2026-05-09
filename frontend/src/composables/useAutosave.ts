import { ref } from 'vue'
import { useDebounceFn } from '@vueuse/core'
import { useToast } from 'vue-toastification'

export function useAutosave(saveFn: (field: string, value: unknown) => Promise<void>) {
  const isSaving  = ref(false)
  const lastSaved = ref<Date | null>(null)
  const queue     = ref<Record<string, unknown>>({})
  const toast     = useToast()

  const flush = useDebounceFn(async () => {
    if (!Object.keys(queue.value).length) return
    const snapshot = { ...queue.value }
    queue.value = {}
    isSaving.value = true
    try {
      for (const [field, value] of Object.entries(snapshot)) {
        await saveFn(field, value)
      }
      lastSaved.value = new Date()
    } catch {
      toast.error('Não foi possível salvar. Verifique sua conexão.')
    } finally {
      isSaving.value = false
    }
  }, 600)

  function queueChange(field: string, value: unknown) {
    queue.value[field] = value
    flush()
  }

  return { queueChange, isSaving, lastSaved }
}
