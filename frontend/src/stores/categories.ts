import { defineStore } from 'pinia'
import { ref } from 'vue'
import { categoryService } from '@/services/categoryService'
import type { Category } from '@/types'

export const useCategoryStore = defineStore('categories', () => {
  const items   = ref<Category[]>([])
  const loading = ref(false)

  async function load(groupId: number) {
    loading.value = true
    try { items.value = await categoryService.list(groupId) }
    finally { loading.value = false }
  }

  async function create(groupId: number, data: Partial<Category>) {
    const c = await categoryService.create(groupId, data)
    items.value.push(c)
    return c
  }

  async function update(groupId: number, id: number, data: Partial<Category>) {
    const c = await categoryService.update(groupId, id, data)
    const idx = items.value.findIndex(i => i.id === id)
    if (idx >= 0) items.value[idx] = c
    return c
  }

  async function remove(groupId: number, id: number) {
    await categoryService.remove(groupId, id)
    items.value = items.value.filter(i => i.id !== id)
  }

  const flat = () => {
    const result: Category[] = []
    const flatten = (cats: Category[]) => cats.forEach(c => { result.push(c); if (c.children) flatten(c.children) })
    flatten(items.value)
    return result
  }

  return { items, loading, load, create, update, remove, flat }
})
