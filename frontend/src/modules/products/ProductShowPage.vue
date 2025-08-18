<!-- src/modules/products/pages/ProductShowPage.vue -->
<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { getProduct } from '@/core/application/productService'
import type { Product } from '@/core/domain/product'

const router = useRouter()
const route = useRoute()
const id = route.params.id as string | number

const loading = ref(true)
const apiError = ref<string | null>(null)
const product = ref<Product | null>(null)

const money = (n?: number) =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(n || 0)

onMounted(async () => {
  try {
    const p = await getProduct(id)
    product.value = p
  } catch (err: any) {
    apiError.value = err?.response?.data?.message ?? err?.message ?? 'Falha ao carregar produto'
  } finally {
    loading.value = false
  }
})

function goBack () {
  if (window.history.length > 1) router.back()
  else router.push({ name: 'products.list' })
}
function goEdit () {
  router.push({ name: 'products.edit', params: { id } })
}
</script>

<template>
  <v-container class="py-6">
    <div class="mb-3">
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
    </div>

    <div class="page-header mb-6">
      <div class="page-title">
        <h1 class="text-h5 font-weight-bold mb-1">Detalhes do Produto</h1>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Visualize as informações do produto
        </p>
      </div>
      <div>
        <v-btn color="primary" prepend-icon="mdi-pencil" @click="goEdit">Editar</v-btn>
      </div>
    </div>

    <v-card rounded="xl" elevation="3" class="form-card">
      <v-card-text>
        <v-skeleton-loader v-if="loading" type="article" />

        <template v-else>
          <v-alert
            v-if="apiError"
            type="error"
            variant="tonal"
            density="comfortable"
            class="mb-4"
          >
            {{ apiError }}
          </v-alert>

          <template v-if="product">
            <v-row class="mb-1">
              <v-col cols="12" md="4">
                <div class="label">Produto</div>
                <div class="value">{{ product.name }}</div>
              </v-col>
              <v-col cols="12" md="4">
                <div class="label">Preço</div>
                <div class="value">
                  {{
                    money(typeof product.price === 'number'
                      ? product.price
                      : Number(String(product.price ?? '0').replace(',', '.')) || 0)
                  }}
                </div>
              </v-col>
              <v-col cols="12" md="4">
                <div class="label">Dono</div>
                <div class="value">{{ product.owner?.name ?? '-' }}</div>
              </v-col>
            </v-row>

            <v-row class="mb-3">
              <v-col cols="12">
                <div class="label">Descrição</div>
                <div class="value">{{ product.description || '–' }}</div>
              </v-col>
            </v-row>

            <v-row>
              <v-col cols="12" md="4">
                <div class="label">Criado em</div>
                <div class="value">
                  {{ new Date(product.created_at ?? '').toLocaleDateString('pt-BR', { timeZone: 'America/Manaus' }) }}
                </div>
              </v-col>
              <v-col cols="12" md="4">
                <div class="label">Atualizado em</div>
                <div class="value">
                  {{ new Date(product.updated_at ?? '').toLocaleDateString('pt-BR', { timeZone: 'America/Manaus' }) }}
                </div>
              </v-col>
            </v-row>
          </template>
        </template>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<style scoped>
.btn-back { padding-inline: 8px; }
.page-header {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 12px;
  align-items: end;
}
.form-card { overflow: hidden; }
.label { font-size: .8rem; color: var(--v-theme-on-surface-variant); }
.value { font-weight: 600; }
:deep(.v-theme--dark) .form-card {
  background-color: color-mix(in srgb, var(--v-theme-surface) 88%, white);
}
</style>
