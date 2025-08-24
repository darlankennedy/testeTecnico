import { Credentials } from '../domain/auth'
import { apiClient } from '@/core/infrastructure/http/apiClient'
import type { User } from '../domain/user'
import { API_PATHS } from '@/app/config/constantes'

export interface LoginResponse {
  access_token: string
  token_type: 'Bearer'
  user: User
}
export interface RegisterResponse {
  access_token?: string
  token_type?: 'Bearer'
  user: User
}

export async function loginService(email: string, password: string): Promise<LoginResponse> {
  console.log(email, password)
  const credentials = new Credentials(email, password)
  if (!credentials.isValid()) throw new Error('Invalid credentials')

  return apiClient.post<LoginResponse>(API_PATHS.LOGIN, {
    email: credentials.email,
    password: credentials.password,
  })
}
export async function logoutService(): Promise<void> {
  await apiClient.post(API_PATHS.LOGOUT)
}

export async function meService(): Promise<User> {
  return apiClient.get<User>(API_PATHS.ME)
}

export async function registerService(payload: {
  name: string
  email: string
  cpf: string
  password: string
  password_confirmation: string
}): Promise<RegisterResponse> {
  return apiClient.post<RegisterResponse>(API_PATHS.REGISTER, payload)
}
