  import {apiClient} from '@/core/infrastructure/http/apiClient'
  import type {User} from '@/core/domain/user'
  import {API_PATHS} from '@/app/config/constantes.ts'
  import {fetchPaginated, type ListParams, type Paginated} from "@/core/application/pagination.ts";
  import {numeric} from "@vuelidate/validators";

  export interface CreateUserPayload {
    name: string
    email: string
    cpf: string
    password: string
    password_confirmation: string
  }

  export interface UpdateUserPayload {
    name?: string
    email?: string
    cpf?: string
    password?: string
    password_confirmation?: string
  }

  const userPath = (id: number | string) => `${API_PATHS.USERS}/${id}`



  export async function fetchUsers(params?: { page?: number; limit?: number; q?: string }) {
    return await apiClient.get<{ items: User[]; total: number }>(API_PATHS.USERS, {params})
  }

  export async function countUsers(): Promise<number> {
    return await apiClient.get<number>(API_PATHS.USERS_COUNT)

  }
  // lista simples para selects (id, name)
  export async function listAllUsersSimple(): Promise<User[]> {
    const res = await apiClient.get<any>(API_PATHS.USER_LISTENING, { per_page: 1000 })
    const data: User[] = res?.data ?? res ?? []
    return data.map((u: any) => ({ id:  u.id, name: u.name, email: u.email }))
  }
  export async function countUsersWithoutProducts(): Promise<number> {
    try {
      const data = await apiClient.get<User[]>(API_PATHS.USERS_WITHOUT_PRODUCTS_COUNT)
      return data.length
    } catch {
      const list = await fetchUsers({ page: 1, limit: 1_000 })
      return list.items.filter(u => !u.products_count).length
    }
  }

  export async function createUser(payload: CreateUserPayload): Promise<User> {
    const body = { ...payload, cpf: payload.cpf.replace(/\D/g, '') }
    return await apiClient.post<User>(API_PATHS.USERS, body)
  }

  export function listUsers(params?: ListParams): Promise<Paginated<User>> {
    return fetchPaginated<User>(API_PATHS.USERS, params)
  }

  export async function getUser(id: number): Promise<User> {
    return await apiClient.get<User>(userPath(id))
  }

  export async function updateUser(id: number, payload: UpdateUserPayload): Promise<User> {
    const body = payload.cpf ? { ...payload, cpf: payload.cpf.replace(/\D/g, '') } : payload
    // Use PATCH se sua API suportar campos parciais; troque para PUT se for full update
    return await apiClient.put<User>(userPath(id), body)
  }

  export async function deleteUser(id: number): Promise<void> {
    await apiClient.delete<void>(userPath(id))
  }
