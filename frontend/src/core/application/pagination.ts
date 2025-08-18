import { apiClient } from '@/core/infrastructure/http/apiClient'

export interface ListParams {
  page?: number
  perPage?: number
  search?: string
  [key: string]: any
}

export interface Paginated<T> {
  data: T[]
  total: number
  page: number
  perPage: number
}

export async function fetchPaginated<T>(
  url: string,
  params: ListParams = {}
): Promise<Paginated<T>> {
  const page = params.page ?? 1
  const perPage = params.perPage ?? 10
  const search = params.search ??  ''

  const res = await apiClient.get<{ data: T[]; meta: any }>(url,
    {
     page,
     per_page: perPage,
     search
    })

  const data: T[] = res?.data ?? []
  const meta = res?.meta ?? {}

  return {
    data,
    total: meta.total ?? data.length,
    page: meta.current_page ?? page,
    perPage: meta.per_page ?? perPage,
  }
}
