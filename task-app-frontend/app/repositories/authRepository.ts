import type {$Fetch, NitroFetchRequest } from 'nitropack'
import type { ApiResponse } from "~/types/apiResponse"
import type { AuthUser } from "~/types/auth"

export interface LoginForm {
  email: string,
  password: string
}

const BASE_RESOURCE = '/auth'
export const authRepository = <T>(fetch: $Fetch<T, NitroFetchRequest>) => ({
  async login(formData: LoginForm){
    return fetch<ApiResponse<AuthUser>>(BASE_RESOURCE +'/login', {
      method: 'POST',
      body: formData
    })
  }
})