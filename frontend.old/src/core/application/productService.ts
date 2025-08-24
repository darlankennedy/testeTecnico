import {apiClient} from '@/core/infrastructure/http/apiClient'
import type {Product} from '@/core/domain/product'
import {API_PATHS} from '@/app/config/constantes.ts'
import {fetchPaginated, type ListParams, type Paginated} from "@/core/application/pagination.ts";


export interface UpdateProductPayload {
  name?: string
  price?: number
  description?: string
  user_id?: number
}

const productPath = (id: number | string) => `${API_PATHS.PRODUCTS}/${id}`

export async function getProduct(id: number | string): Promise<Product> {
  return await apiClient.get<Product>(productPath(id))
}
export async function fetchProducts(params?: { page?: number; limit?: number; q?: string }) {
  return await apiClient.get<{ items: Product[]; total: number }>(API_PATHS.PRODUCTS, {params})
}
export function listProducts(params?: ListParams): Promise<Paginated<Product>> {
  return fetchPaginated<Product>(API_PATHS.PRODUCTS, params)
}
export async function countProducts(): Promise<number> {
    return apiClient.get<number>(API_PATHS.PRODUCTS_COUNT)
}

export async function createProduct(payload: {
  name: string
  price: number
  description?: string
  user_id: number
}) {
  return apiClient.post(API_PATHS.PRODUCTS, payload)
}



export async function updateProduct(
  id: number | string,
  payload: UpdateProductPayload
): Promise<Product> {
  return await apiClient.put<Product>(productPath(id), payload)
}

export async function deleteProduct(id: number | string): Promise<void> {
  await apiClient.delete<void>(productPath(id))
}
