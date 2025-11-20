// plugins/api.ts
import { defineNuxtPlugin } from '#app';


export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig(); // Access runtime config if needed
  const authStore = useAuthUserStore();
  const api = $fetch.create({
    baseURL: config.public.API_BASE_URL, // Example: base URL from runtime config
    onRequest ({ request, options, error }) {
      if (authStore.token) {
        // note that this relies on ofetch >= 1.4.0 - you may need to refresh your lockfile
        options.headers.set('Authorization', `Bearer ${authStore.token}`)
      }
    },
  });

  return {
    provide: {
      api
    }
  }
});