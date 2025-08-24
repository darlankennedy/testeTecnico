<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { getUser } from '@/core/application/userService'
import type { User } from '@/core/domain/user'

const router = useRouter()
const route = useRoute()
const id = Number(route.params.id)

const loading = ref(true)
const apiError = ref<string | null>(null)
const user = ref<User | null>(null)

const productsSafe = computed(() => user.value?.products ?? [])
const productsCount = computed(() => productsSafe.value.length)
const totalValue = computed(() => {
  const sum = productsSafe.value.reduce((acc, p: any) => {
    const priceNum =
      typeof p.price === 'number'
        ? p.price
        : Number((p.price ?? '0').toString().replace(',', '.')) || 0
    return acc + priceNum
  }, 0)
  return Number(sum.toFixed(2))
})

const money = (n?: number) =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(n || 0)

onMounted(async () => {
  try {
    const u = await getUser(id)
    user.value = u
  } catch (err: any) {
    apiError.value = err?.response?.data?.message ?? err?.message ?? 'Falha ao carregar usuário'
  } finally {
    loading.value = false
  }
})

function goBack() {
  if (window.history.length > 1) router.back()
  else router.push({ name: 'users.list' })
}
function goEdit() {
  router.push({ name: 'users.edit', params: { id } })
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
        <h1 class="text-h5 font-weight-bold mb-1">Detalhes do Usuário</h1>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Visualize informações e produtos vinculados
        </p>
      </div>
      <div>
        <v-btn color="primary" prepend-icon="mdi-pencil" @click="goEdit">Editar</v-btn>
      </div>
    </div>

    <v-card rounded="xl" elevation="3" class="form-card">
      <v-card-text>
        <v-skeleton-loader v-if="loading" type="article, table" />

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

          <template v-if="user">
            <v-row class="mb-1">
              <v-col cols="12" md="4">
                <div class="label">Nome</div>
                <div class="value">{{ user.name }}</div>
              </v-col>
              <v-col cols="12" md="4">
                <div class="label">E-mail</div>
                <div class="value">{{ user.email }}</div>
              </v-col>
              <v-col cols="12" md="4">
                <div class="label">CPF</div>
                <div class="value">{{ user.cpf }}</div>
              </v-col>
            </v-row>

            <v-row class="mb-4">
              <v-col cols="12" md="4">
                <div class="label">Criado em</div>
                <div class="value">
                  {{ new Date(user.created_at ?? '').toLocaleDateString('pt-BR', { timeZone: 'America/Manaus' }) }}
                </div>
              </v-col>
              <v-col cols="12" md="4">
                <div class="label">Atualizado em</div>
                <div class="value">
                  {{ new Date(user.updated_at ?? '').toLocaleDateString('pt-BR', { timeZone: 'America/Manaus' }) }}
                </div>
              </v-col>
            </v-row>

            <!-- Métricas -->
            <v-row class="mb-2">
              <v-col cols="12" md="4">
                <v-alert variant="tonal" type="info" density="compact">
                  Produtos: <strong>{{ productsCount }}</strong>
                </v-alert>
              </v-col>
              <v-col cols="12" md="4">
                <v-alert variant="tonal" type="success" density="compact">
                  Valor total: <strong>{{ money(totalValue) }}</strong>
                </v-alert>
              </v-col>
            </v-row>

            <!-- Tabela de produtos -->
            <v-table class="mt-2">
              <thead>
              <tr>
                <th class="text-left">Produto</th>
                <th class="text-left">Descrição</th>
                <th class="text-right">Preço</th>
                <th class="text-left">Criado em</th>
              </tr>
              </thead>
              <tbody>
              <tr v-for="p in productsSafe" :key="p.id">
                <td>{{ p.name }}</td>
                <td class="truncate">{{ p.description }}</td>
                <td class="text-right">
                  {{ money(typeof p.price === 'number' ? p.price : Number((p.price ?? '0').toString().replace(',', '.')) || 0) }}
                </td>
                <td>
                  {{ new Date(p.created_at ?? '').toLocaleDateString('pt-BR', { timeZone: 'America/Manaus' }) }}
                </td>
              </tr>
              <tr v-if="productsSafe.length === 0">
                <td colspan="4" class="text-medium-emphasis">Nenhum produto vinculado.</td>
              </tr>
              </tbody>
            </v-table>
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
.truncate {
  max-width: 520px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
:deep(.v-theme--dark) .form-card {
  background-color: color-mix(in srgb, var(--v-theme-surface) 88%, white);
}
</style>
