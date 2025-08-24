<script setup lang="ts">
import { onMounted, ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { getReportSummary, type ReportFilters, type ReportSummary } from '@/core/application/reportService'

// **Se não quiser serviço dedicado agora, dá pra montar tudo com listUsers/listProducts e calcular no front.**
// Abaixo assumo um endpoint /reports/summary (ver seção 3).

const router = useRouter()

const loading = ref(true)
const exporting = ref(false)
const data = ref<ReportSummary | null>(null)

// filtros
const dateFrom = ref<string | null>(null)  // '2025-08-01'
const dateTo   = ref<string | null>(null)
const q        = ref<string>('')           // busca por nome/email/produto (se backend suportar)
const userId   = ref<number | null>(null)  // filtro por usuário (opcional)

// paginação da tabela
const page = ref(1)
const perPage = ref(10)

const money = (n?: number) =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(n || 0)

async function fetchReport() {
  loading.value = true
  try {
    const params: ReportFilters = {
      dateFrom: dateFrom.value || undefined,
      dateTo:   dateTo.value   || undefined,
      q:        q.value        || undefined,
      userId:   userId.value   || undefined,
      page:     page.value,
      perPage:  perPage.value,
    }
    data.value = await getReportSummary(params)
  } finally {
    loading.value = false
  }
}

onMounted(fetchReport)
watch([dateFrom, dateTo, q, userId, page, perPage], () => {
  // debounce simples
  clearTimeout((fetchReport as any)._t)
  ;(fetchReport as any)._t = setTimeout(fetchReport, 250)
})

// KPIs (fallback safe)
const usersTotal     = computed(() => data.value?.metrics.usersTotal ?? 0)
const productsTotal  = computed(() => data.value?.metrics.productsTotal ?? 0)
const usersNoProduct = computed(() => data.value?.metrics.usersNoProduct ?? 0)

// tabela principal (ex.: usuários com totais)
const rows = computed(() => data.value?.topUsers ?? [])
const totalRows = computed(() => data.value?.pagination?.total ?? rows.value.length)
const totalPages = computed(() => Math.max(1, Math.ceil(totalRows.value / perPage.value)))

// navegação
function goBack() {
  if (window.history.length > 1) router.back()
  else router.push({ name: 'home' })
}

// ======== Exportar PDF (CLIENTE) com html2canvas + jsPDF ========
import jsPDF from 'jspdf'
import html2canvas from 'html2canvas'

async function exportPdfClient() {
  exporting.value = true
  try {
    const el = document.getElementById('report-pdf')
    if (!el) return
    // força fundo branco p/ dark mode
    el.style.backgroundColor = '#fff'
    const canvas = await html2canvas(el, { scale: 2, useCORS: true, backgroundColor: '#fff' })
    const imgData = canvas.toDataURL('image/png')

    const pdf = new jsPDF('p', 'mm', 'a4')
    const pageWidth = pdf.internal.pageSize.getWidth()
    const pageHeight = pdf.internal.pageSize.getHeight()
    const imgWidth = pageWidth
    const imgHeight = (canvas.height * imgWidth) / canvas.width

    let position = 0
    let heightLeft = imgHeight

    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight)
    heightLeft -= pageHeight
    while (heightLeft > 0) {
      pdf.addPage()
      position = -heightLeft
      pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight)
      heightLeft -= pageHeight
    }

    pdf.save(`relatorio-${new Date().toISOString().slice(0,10)}.pdf`)
  } finally {
    exporting.value = false
  }
}
</script>

<template>
  <v-container class="py-6">
    <!-- Barra -->
    <div class="mb-3 d-flex align-center justify-space-between flex-wrap gap-2">
      <v-btn
        variant="text"
        color="primary"
        size="small"
        prepend-icon="mdi-arrow-left"
        class="btn-back"
        @click="goBack"
      >
        Voltar
      </v-btn>

      <div class="d-flex align-center gap-2">
        <v-btn
          color="primary"
          prepend-icon="mdi-file-pdf-box"
          :loading="exporting"
          @click="exportPdfClient"
        >Exportar PDF</v-btn>
      </div>
    </div>

    <!-- Filtros -->
    <v-card class="mb-4" rounded="xl" elevation="2">
      <v-card-text class="d-flex flex-wrap gap-3">
        <v-text-field
          v-model="dateFrom"
          type="date"
          label="Data inicial"
          density="comfortable"
          variant="outlined"
          hide-details
          style="max-width: 220px"
        />
        <v-text-field
          v-model="dateTo"
          type="date"
          label="Data final"
          density="comfortable"
          variant="outlined"
          hide-details
          style="max-width: 220px"
        />
        <v-text-field
          v-model="q"
          label="Buscar"
          prepend-inner-icon="mdi-magnify"
          density="comfortable"
          variant="outlined"
          placeholder="Nome, e-mail, produto…"
          hide-details
          style="min-width: 260px"
        />
        <v-select
          v-model="userId"
          :items="data?.usersSimple ?? []"
          item-title="name"
          item-value="id"
          label="Usuário"
          density="comfortable"
          variant="outlined"
          hide-details
          style="min-width: 220px"
        />
      </v-card-text>
    </v-card>

    <!-- CONTEÚDO CAPTURADO NO PDF -->
    <div id="report-pdf">
      <!-- KPIs -->
      <v-row class="mb-4" align="stretch">
        <v-col cols="12" md="4">
          <v-card rounded="xl" elevation="3">
            <v-card-text class="py-4">
              <div class="text-caption text-medium-emphasis">Usuários (total)</div>
              <div class="text-h5 font-weight-bold">{{ usersTotal }}</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="4">
          <v-card rounded="xl" elevation="3">
            <v-card-text class="py-4">
              <div class="text-caption text-medium-emphasis">Produtos (total)</div>
              <div class="text-h5 font-weight-bold">{{ productsTotal }}</div>
            </v-card-text>
          </v-card>
        </v-col>
        <v-col cols="12" md="4">
          <v-card rounded="xl" elevation="3">
            <v-card-text class="py-4">
              <div class="text-caption text-medium-emphasis">Usuários sem Produtos</div>
              <div class="text-h5 font-weight-bold">{{ usersNoProduct }}</div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Tabela principal (ex.: usuários com totais) -->
      <v-card rounded="xl" elevation="3">
        <v-card-title class="text-subtitle-1">Resumo por usuário</v-card-title>
        <v-data-table
          :headers="[
            { title: 'Usuário', key: 'name' },
            { title: 'Qtd. Produtos', key: 'products_count' },
            { title: 'Valor Total', key: 'products_total_value' },
          ]"
          :items="rows"
          :loading="loading"
          class="elevation-0"
          hide-default-footer
        >
          <template #item.products_total_value="{ item }">
            <div class="text-right">{{ money(item.products_total_value) }}</div>
          </template>

          <template #no-data>
            <div class="pa-8 text-center text-medium-emphasis">
              <v-icon size="32" class="mb-2">mdi-table-off</v-icon>
              <div>Nenhum dado para o período/filtros.</div>
            </div>
          </template>
        </v-data-table>

        <!-- Footer da tabela com paginação -->
        <div class="d-flex align-center justify-space-between flex-wrap gap-3 px-4 py-3">
          <div class="text-body-2 text-medium-emphasis">
            Total: <strong>{{ totalRows }}</strong>
          </div>
          <div class="d-flex align-center gap-3">
            <v-select
              v-model="perPage"
              :items="[5,10,15,20,30,50]"
              density="compact"
              variant="outlined"
              hide-details
              style="width: 92px"
            />
            <v-pagination
              v-model="page"
              :length="totalPages"
              density="comfortable"
              size="small"
            />
          </div>
        </div>
      </v-card>
    </div>
  </v-container>
</template>

<style scoped>
.btn-back { padding-inline: 8px; }
#report-pdf { background: white; padding: 6px; border-radius: 16px; } /* útil pro PDF no dark mode */
</style>
