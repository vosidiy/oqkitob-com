import { createRouter, createWebHistory } from 'vue-router'
import AppLayout from '@/layouts/AppLayout.vue'
import GuestLayout from '@/layouts/GuestLayout.vue'
import HomeView from '@/views/HomeView.vue'
import BookView from '@/views/BookView.vue'
import LandingPage from '@/views/LandingPage.vue'
import LoginView from '@/views/auth/LoginView.vue'
import RegisterView from '@/views/auth/RegisterView.vue'
import ForgotPasswordView from '@/views/auth/ForgotPasswordView.vue'
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
      path: '/',
      component: GuestLayout,
      meta: {
        requiresGuest: true,
      },
      children: [
        {
          path: 'login',
          name: 'login',
          component: LoginView,
        },
        {
          path: 'register',
          name: 'register',
          component: RegisterView,
        },
        {
          path: 'forgot-password',
          name: 'forgot-password',
          component: ForgotPasswordView,
        },
      ],
    },
    {
      path: '/home',
      component: AppLayout,
      meta: {
        requiresAuth: true,
      },
      children: [
        {
          path: '',
          name: 'dashboard-home',
          component: HomeView,
        },
        {
          path: 'books',
          redirect: {
            name: 'dashboard-home',
          },
        },
        {
          path: 'books/:bookId',
          name: 'book-detail',
          component: BookView,
        },
      ],
    },
  ],
})

router.beforeEach(async (to) => {
  const user = await authStore.ensureChecked()

  if (to.meta.requiresAuth && !user) {
    return {
      name: 'login',
    }
  }

  if (to.meta.requiresGuest && user) {
    return {
      name: 'dashboard-home',
    }
  }

  return true
})

export default router
