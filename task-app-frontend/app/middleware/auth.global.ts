import { useAuthUserStore } from "~/stores/auth"

export default defineNuxtRouteMiddleware((to, from) => {
  const authUserStore = useAuthUserStore()

  // If user is authenticated and tries to access /login or /
  if (authUserStore.isAuthenticated && (to.path === '/login' || to.path === '/')) {
    return navigateTo('/tasks')
  }

  // If user is NOT authenticated and tries to access protected pages
  if (!authUserStore.isAuthenticated && to.path !== '/login') {
    return navigateTo('/login')
  }
})
