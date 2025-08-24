import axios from 'axios'
import type { AxiosError, AxiosInstance, AxiosRequestConfig, AxiosResponse, InternalAxiosRequestConfig } from 'axios'
import { env } from '@/app/config/env'
import { useAuthStore } from '@/app/stores/useAuthStore'
import router from '@/app/router'


declare module 'axios' {
  interface InternalAxiosRequestConfig<D = any> {
    skipAuthHandling?: boolean
    _retried?: boolean
  }
}

export const api: AxiosInstance = axios.create({
  baseURL: env.apiUrl,
  timeout: 15_000,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

export const authlessApi: AxiosInstance = axios.create({
  baseURL: env.apiUrl,
  timeout: 10_000,
})

let handlingUnauthorized = false

api.interceptors.request.use((config: InternalAxiosRequestConfig) => {
  const auth = useAuthStore()
  if (auth.token && !config.headers?.Authorization) {
    config.headers = config.headers || {}
    config.headers.Authorization = `Bearer ${auth.token}`
  }
  return config
})

export function normalizeAxiosError(error: unknown) {
  const err = error as AxiosError<any>
  const status = err.response?.status
  const data = err.response?.data
  const isTimeout = err.code === 'ECONNABORTED'
  const isNetwork = !err.response && !isTimeout

  const backendMsg =
    (typeof data === 'string' && data) ||
    data?.message ||
    data?.error ||
    data?.errors?.[0]?.message ||
    data?.errors && typeof data.errors === 'object' ? JSON.stringify(data.errors) : undefined

  let message =
    backendMsg ||
    (isTimeout ? 'Tempo de requisição excedido.' : isNetwork ? 'Falha de rede. Verifique sua conexão.' : 'Ocorreu um erro na requisição.')

  if (status === 422 && data?.errors) {
    try {
      const firstKey = Object.keys(data.errors)[0]
      const firstMsg = Array.isArray(data.errors[firstKey]) ? data.errors[firstKey][0] : String(data.errors[firstKey])
      message = firstMsg || message
    } catch {}
  }

  return {
    status,
    code: err.code,
    message,
    raw: err,
  }
}

api.interceptors.response.use(
  (response: AxiosResponse) => response,
  async (error: AxiosError) => {
    const config = (error.config || {}) as InternalAxiosRequestConfig
    const status = error.response?.status

    if (config.skipAuthHandling) {
      return Promise.reject(error)
    }

    if (status === 401) {
      if (handlingUnauthorized) {
        return Promise.reject(error)
      }
      handlingUnauthorized = true
      try {
        const auth = useAuthStore()
        await auth.clearLocal?.()
      } catch {}
      finally {
        const current = router.currentRoute.value
        if (current.name !== 'login') {
          router.replace({ name: 'login', query: { redirect: current.fullPath } })
        }
        handlingUnauthorized = false
      }
      return Promise.reject(error)
    }

    // Exemplos de outras categorias comuns:
    // - 403: sem permissão -> você pode mostrar toast específico
    // - 422: validação -> UI pode exibir mensagens de campo
    // - 500+: erro do servidor -> exibir mensagem genérica/logar

    return Promise.reject(error)
  }
)

export default api
