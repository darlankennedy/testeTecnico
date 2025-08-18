// core/infrastructure/http/apiClient.ts
import api from '@/app/plugins/axios'

export const apiClient = {
  get: async <T>(url: string, params?: any): Promise<T> => {
    const { data } = await api.get<T>(url, { params })
    return data
  },
  post: async <T>(url: string, body?: any): Promise<T> => {
    const { data } = await api.post<T>(url, body)
    return data
  },
  put: async <T>(url: string, body?: any): Promise<T> => {
    const { data } = await api.put<T>(url, body)
    return data
  },
  delete: async <T>(url: string): Promise<T> => {
    const { data } = await api.delete<T>(url)
    return data
  },
}
