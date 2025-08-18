import type {Product} from "@/core/domain/product.ts";

export interface User {
  id: string | number
  name: string
  email: string
  created_at?: string
  updated_at?: string
  products_count?: number
  products_total_value?: number,
  products?: Product[]
}
