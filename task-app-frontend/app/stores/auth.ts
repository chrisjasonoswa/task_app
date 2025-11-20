import { defineStore } from 'pinia'
import type { AuthUser } from "~/types/auth"

export const useAuthUserStore = defineStore(
  'user',
  () => {
    // ===== State =====
    const name = ref('')
    const email = ref('')
    const token = ref('')

    // ===== Getters =====
    const isAuthenticated = computed(() => !!token.value)

    // ===== Actions =====
    const initializeStore = (authUser: AuthUser) => {
      name.value = authUser.user.name
      email.value = authUser.user.email
      token.value = authUser.token
    }

    // ===== Return exposed state, getters, and actions =====
    return {
      // state
      name, email, token,
      // getters
      // actions
      initializeStore, isAuthenticated
    }
  },
  {
    persist: {
      storage: piniaPluginPersistedstate.localStorage(),
    },
  },
)
