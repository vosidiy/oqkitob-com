import { createRouter, createWebHistory } from 'vue-router'
import BaseShell from '@/layouts/BaseShell.vue'
import DashboardHomeView from '@/views/DashboardHomeView.vue'
import BookContentView from '@/views/BookContentView.vue'
import LandingPage from '@/views/LandingPage.vue'
import { authStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'landing',
      component: LandingPage,
    },
    {
      path: '/home',
      component: BaseShell,
      meta: {
        requiresAuth: true,
      },
      children: [
        {
          path: '',
          name: 'dashboard-home',
          component: DashboardHomeView,
        },
        {
          path: 'books/:bookId',
          name: 'book-detail',
          component: BookContentView,
        },
      ],
    },
  ],
})

router.beforeEach(async (to) => {
  // The guard relies on authStore.ensureChecked() so the SPA only performs
  // one initial auth probe against /auth/me when possible.
  if (!to.meta.requiresAuth) {
    return true
  }

  const user = await authStore.ensureChecked()

  if (!user) {
    return {
      name: 'landing',
    }
  }

  return true
})

export default router
