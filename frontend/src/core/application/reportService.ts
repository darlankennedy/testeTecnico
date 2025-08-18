import { apiClient } from '@/core/infrastructure/http/apiClient'
import { API_PATHS } from '@/app/config/constantes'
export interface ReportFilters {
  dateFrom?: string
  dateTo?: string
  q?: string
  userId?: number
  page?: number
  perPage?: number
}

export interface ReportUserRow {
  id: number
  name: string
  products_count: number
  products_total_value: number
}

export interface ReportSummary {
  metrics: {
    usersTotal: number
    productsTotal: number
    usersNoProduct: number
  }
  topUsers: ReportUserRow[]
  pagination?: { total: number }
  usersSimple?: Array<{ id: number; name: string }>
}

export async function getReportSummary(params?: ReportFilters): Promise<ReportSummary> {
  return await apiClient.get<ReportSummary>(API_PATHS.REPORTS_SUMMARY, { params })
}
