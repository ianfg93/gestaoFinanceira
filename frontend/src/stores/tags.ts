import { defineStore } from 'pinia'
import { ref } from 'vue'
import { tagService } from '@/services/tagService'
import type { Tag } from '@/types'

export const useTagStore = defineStore('tags', () => {
  const items = ref<Tag[]>([])

  async function load(groupId: number) {
    items.value = await tagService.list(groupId)
  }
  async function create(groupId: number, name: string, color?: string) {
    const t = await tagService.create(groupId, name, color)
    items.value.push(t)
    return t
  }
  async function remove(groupId: number, id: number) {
    await tagService.remove(groupId, id)
    items.value = items.value.filter(i => i.id !== id)
  }

  return { items, load, create, remove }
})
