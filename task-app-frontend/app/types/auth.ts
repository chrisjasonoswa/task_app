
export interface AuthUser {
  user: {
    id: number
    name: string
    email: string
  }
  token: string
}