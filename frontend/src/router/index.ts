import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { currentMonth } from '@/utils/format'

function currentMonthPath() {
  return `/months/${currentMonth()}`
}

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login',    name: 'login',    component: () => import('@/pages/LoginPage.vue'),    meta: { public: true } },
    { path: '/register', name: 'register', component: () => import('@/pages/RegisterPage.vue'), meta: { public: true } },
    {
      path: '/',
      component: () => import('@/components/layout/AppLayout.vue'),
      children: [
        { path: '',           redirect: () => currentMonthPath() },
        { path: 'dashboard',  name: 'dashboard',     component: () => import('@/pages/DashboardPage.vue') },
        { path: 'months/:month', name: 'month',      component: () => import('@/pages/MonthPage.vue') },
        { path: 'categories', name: 'categories',    component: () => import('@/pages/CategoriesPage.vue') },
        { path: 'tags',       name: 'tags',          component: () => import('@/pages/TagsPage.vue') },
        { path: 'notifications', name: 'notifications', component: () => import('@/pages/NotificationsPage.vue') },
        { path: 'settings',   name: 'settings',      component: () => import('@/pages/SettingsPage.vue') },
      ],
    },
    { path: '/:pathMatch(.*)*', redirect: () => currentMonthPath() },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  if (!to.meta.public && !auth.isAuthenticated) {
    if (auth.token) {
      await auth.fetchMe()
      if (!auth.isAuthenticated) return { name: 'login' }
    } else {
      return { name: 'login' }
    }
  }
  if (to.meta.public && auth.isAuthenticated) {
    return currentMonthPath()
  }
})

export default router
