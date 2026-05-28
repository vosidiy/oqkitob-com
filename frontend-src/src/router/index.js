import { createRouter, createWebHistory } from 'vue-router'
import AppLayout from '@/layouts/AppLayout.vue'
import AppLayoutMobile from '@/layouts/AppLayoutMobile.vue'
import GuestLayout from '@/layouts/GuestLayout.vue'
import HomeView from '@/views/HomeView.vue'
import BookView from '@/views/BookView.vue'
import LoginView from '@/views/auth/LoginView.vue'
import RegisterView from '@/views/auth/RegisterView.vue'
import ForgotPasswordView from '@/views/auth/ForgotPasswordView.vue'
import { authStore } from '@/stores/auth'
import NotFoundView from '@/views/NotFoundView.vue'
import { isInitialMobileViewport } from '@/utils/device'

const AuthenticatedLayout = isInitialMobileViewport() ? AppLayoutMobile : AppLayout

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/app.html',
      redirect: {
        name: 'login',
      },
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
      component: AuthenticatedLayout,
      meta: {  requiresAuth: true,  },
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
          path: 'books/:bookId/main',
          redirect: (to) => ({
            name: 'book-detail',
            params: {
              bookId: to.params.bookId,
            },
          }),
        },
        {
          // Keep :page optional so /home/books/:bookId remains the canonical
          // default page URL for every future multi-tab book.
          path: 'books/:bookId/:page?',
          name: 'book-detail',
          component: BookView,
        },
        {
          path: ':pathMatch(.*)*',
          name: 'dashboard-not-found',
          component: NotFoundView,
        },
      ],
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: NotFoundView,
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
