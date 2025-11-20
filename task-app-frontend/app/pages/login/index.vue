<template>
  <div class="min-h-screen flex flex-col items-center justify-center">
    <img src="/logo.png" class="w-8 aspect-square mb-4" />
    <Card class="w-full max-w-[400px] px-8 pt-10 pb-24">
      <!-- Title -->
      <CardHeader class="flex flex-col items-center">
        <CardTitle class="text-2xl">Sign In</CardTitle>
        <CardDescription class="text-foreground">
          Login to continue using the app
        </CardDescription>
      </CardHeader>
      <!-- Form -->
      <CardContent>
        <form>
          <div class="grid w-full items-center gap-4">
            <div class="flex flex-col space-y-1.5">
              <Label for="email">Email</Label>
              <Input id="email" type="email" v-model="loginForm.email"/>
            </div>
            <div class="flex flex-col space-y-1.5">
              <div class="flex items-center">
                <Label for="password">Password</Label>
                <a
                  href="#"
                  class="ml-auto inline-block text-sm hover:underline"
                >
                  Forgot your password?
                </a>
              </div>
              <Input id="password" type="password" v-model="loginForm.password"/>
            </div>
          </div>
        </form>
      </CardContent>
      <!-- Login -->
      <CardFooter class="flex flex-col gap-2">
        <Button
          class="w-full"
          @click="handleLogin" 
          :loading="isLoggingIn" 
          :disabled="isLoggingIn"
        >
          Login
        </Button>
      </CardFooter>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { authRepository, type LoginForm } from "~/repositories/authRepository";
import { useAuthUserStore } from "~/stores/auth";

definePageMeta({
  layout: false
})

const router = useRouter()
const authUserStore = useAuthUserStore()

const { $api } = useNuxtApp()
const authRepo = authRepository($api)

const loginForm = reactive<LoginForm>({
  email: '',
  password: ''
})

const isLoggingIn = ref(false)
const handleLogin = async () => {
  try {
    isLoggingIn.value = true
    const { data } = await authRepo.login({
      email: loginForm.email,
      password: loginForm.password
    })

    isLoggingIn.value = false

    // Save user and token in Pinia store
    if(data){
      authUserStore.initializeStore(data)
    }
    
    // Redirect to /tasks
    router.push('/tasks')
  } catch (error) {
    console.error('Login failed', error)
  } finally {
    isLoggingIn.value = false
  }
}
</script>
