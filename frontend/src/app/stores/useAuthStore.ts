import { defineStore } from 'pinia'
import type { User } from '@/core/domain/user'
import { loginService, logoutService, registerService } from '@/core/application/authService'
import { STORAGE_KEYS } from '@/app/config/constantes'

interface AuthState {
  user: User | null
  token: string | null
  loading: boolean
  ready: boolean
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: safeParseUser(localStorage.getItem(STORAGE_KEYS.USER)),
    token: localStorage.getItem(STORAGE_KEYS.TOKEN),
    loading: false,
    ready: false,
  }),

  getters: {
    isAuthenticated: (s) => !!s.token,
    userName: (s) => s.user?.name || '',
  },

  actions: {
    async login(email: string, password: string) {
      this.loading = true
      try {
        const res = await loginService(email, password) // { access_token, user }
        this.token = res.access_token
        this.user = res.user

        localStorage.setItem(STORAGE_KEYS.TOKEN, res.access_token)
        localStorage.setItem(STORAGE_KEYS.USER, JSON.stringify(res.user))
      } finally {
        this.loading = false
      }
    },
    clearLocal() {
      this.token = ''
      this.user = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    },
    async register(payload: {
      name: string
      email: string
      cpf: string
      password: string
      password_confirmation: string
    }) {
      this.loading = true
      try {
        const cleanCpf = payload.cpf.replace(/\D/g, '')
        const res = await registerService({
          ...payload,
          cpf: cleanCpf,
        })

        if (res.access_token && res.user) {
          this.token = res.access_token
          this.user = res.user
          localStorage.setItem(STORAGE_KEYS.TOKEN, res.access_token)
        } else {
          this.user = res.user ?? null
        }
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try {
        await logoutService()
      } catch {
        // ignore 401/sem token
      } finally {
        this.user = null
        this.token = null
        localStorage.removeItem(STORAGE_KEYS.TOKEN)
        localStorage.removeItem(STORAGE_KEYS.USER)
      }
    },
  },
})

function safeParseUser(raw: string | null): User | null {
  if (!raw) return null
  try { return JSON.parse(raw) as User }
  catch { return null }
}
