import type {User} from "@/core/domain/user.ts";

export interface Product {
  id: string | number
  name: string
  price?: number
  created_at?: string
  owner?: User
}
