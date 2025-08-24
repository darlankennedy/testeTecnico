<!-- src/modules/users/pages/UserListPage.vue -->
<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { listUsers } from '@/core/application/userService.ts'
import { deleteUser as deleteUserService } from '@/core/application/userService.ts'
import type { User } from '@/core/domain/user.ts'
import {normalizeAxiosError} from "@/app/plugins/axios.ts";

const router = useRouter()

const loading = ref(false)
const users = ref<User[]>([])
const total = ref(0)
const page = ref(1)
const perPage = ref(10)
const search = ref('')

const deleteDialog = ref(false)
const deleting = ref(false)
const selectedUser = ref<User | null>(null)
const snackbar = ref({ show: false, text: '', color: 'success' as 'success' | 'error' })

const headers = [
  { title: 'Nome', key: 'name' },
  { title: 'E-mail', key: 'email' },
  { title: 'Qtd. Produtos', key: 'products_count' },
  { title: 'Valor Total', key: 'products_total_value' },
  { title: 'Criado em', key: 'created_at' },
  { title: 'Ações', key: 'actions', sortable: false },
]

const money = (n?: number) =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(n || 0)

let fetchId = 0
async function fetchUsers() {
  loading.value = true
  const myId = ++fetchId
  try {
    const res = await listUsers({
      page: page.value,
      perPage: perPage.value,
      search: search.value,
    })

    if (myId === fetchId) {
      users.value = res.data
      users.value.forEach((user: User) => {
        const products = user.products ?? []
        user.products_count = products.length
        const total = products.reduce((acc, p: any) => {
          const priceNum =
            typeof p.price === 'number'
              ? p.price
              : Number((p.price ?? '0').toString().replace(',', '.')) || 0
          return acc + priceNum
        }, 0)
        user.products_total_value = Number(total.toFixed(2))
      })
      total.value = res.total
      page.value = res.page
      perPage.value = res.perPage
    }
  } catch (err) {
    const e = normalizeAxiosError(err)
    if (e.status !== 401) {
      snackbar.value = { show: true, text: e.message, color: 'error' }
      console.error('[fetchUsers] Error:', e.raw)
    }
  } finally {
    if (myId === fetchId) loading.value = false
  }
}

watch(search, () => { page.value = 1 })

let t: number | undefined
let first = true
watch([page, perPage, search], () => {
  if (first) {
    first = false
    fetchUsers()
    return
  }
  clearTimeout(t)
  t = window.setTimeout(fetchUsers, 250)
}, { immediate: true })

const totalPages = computed(() => Math.max(1, Math.ceil(total.value / perPage.value)))

function goBack() {
  if (window.history.length > 1) router.back()
  else router.push({ name: 'home' })
}
function addUser() {
  router.push({ name: 'users.create' })
}
function editUser(u: User) {
  router.push({ name: 'users.edit', params: { id: u.id } })
}
function viewUser(u: User) {
  router.push({ name: 'users.view', params: { id: u.id } })
}

function deleteUser(u: User) {
  selectedUser.value = u
  deleteDialog.value = true
}

async function confirmDelete() {
  if (!selectedUser.value) return
  try {
    deleting.value = true
    await deleteUserService(Number(selectedUser.value.id))
    snackbar.value = { show: true, text: 'Usuário excluído com sucesso.', color: 'success' }
    deleteDialog.value = false
    selectedUser.value = null
    await fetchUsers()
    if (users.value.length === 0 && page.value > 1) {
      page.value = page.value - 1
      await fetchUsers()
    }
  } catch (e) {
    snackbar.value = { show: true, text: 'Falha ao excluir usuário.', color: 'error' }
  } finally {
    deleting.value = false
  }
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
        <h1 class="text-h5 font-weight-bold mb-1">Usuários</h1>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Gerencie todos os usuários do sistema
        </p>
      </div>

      <div class="page-actions">
        <v-text-field
          v-model="search"
          density="compact"
          variant="outlined"
          prepend-inner-icon="mdi-magnify"
          placeholder="Buscar por nome ou e-mail"
          hide-details
          class="search-field"
        />
        <v-btn color="primary" prepend-icon="mdi-account-plus" @click="addUser">
          Novo Usuário
        </v-btn>
      </div>
    </div>

    <v-card rounded="xl" elevation="3" class="table-card">
      <v-data-table
        :headers="headers"
        :items="users"
        :loading="loading"
        :items-per-page="perPage"
        :page="page"
        class="elevation-0"
        hide-default-footer
        hover
      >
        <template #loading>
          <div class="pa-6 text-center">
            <v-progress-circular indeterminate size="24" class="me-2" />
            Carregando usuários...
          </div>
        </template>

        <template #item.products_count="{ item }">
          <div class="text-right">{{ item.products_count ?? 0 }}</div>
        </template>

        <template #item.products_total_value="{ item }">
          <div class="text-right">{{ money(item.products_total_value) }}</div>
        </template>

        <template #item.created_at="{ item }">
          <span>
            {{
              new Date(item.created_at ?? item.created_at ?? '')
                .toLocaleDateString('pt-BR', { timeZone: 'America/Manaus' })
            }}
          </span>
        </template>

        <template #item.actions="{ item }">
          <v-menu>
            <template #activator="{ props }">
              <v-btn
                size="small"
                variant="text"
                icon
                v-bind="props"
              >
                <v-icon size="20">mdi-dots-vertical</v-icon>
              </v-btn>
            </template>

            <v-list>
              <v-list-item
                prepend-icon="mdi-pencil"
                title="Editar"
                @click="editUser(item)"
              />
              <v-list-item
                prepend-icon="mdi-delete"
                title="Excluir"
                @click="deleteUser(item)"
              />
              <v-list-item
                prepend-icon="mdi-eye"
                title="Visualizar"
                @click="viewUser(item)"
              />
            </v-list>
          </v-menu>
        </template>

        <!-- vazio -->
        <template #no-data>
          <div class="pa-10 text-center text-medium-emphasis">
            <v-icon size="36" class="mb-2">mdi-account-search</v-icon>
            <div>Nenhum usuário encontrado</div>
          </div>
        </template>
      </v-data-table>

      <!-- rodapé / paginação -->
      <div class="table-footer">
        <div class="text-body-2 text-medium-emphasis">
          Total: <strong>{{ total }}</strong>
        </div>

        <div class="footer-controls">
          <v-select
            v-model="perPage"
            :items="[5,10,15,20,30,50]"
            density="compact"
            variant="outlined"
            hide-details
            class="perpage"
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


    <v-dialog v-model="deleteDialog" max-width="420">
      <v-card rounded="lg">
        <v-card-title class="text-subtitle-1">
          Confirmar exclusão
        </v-card-title>

        <v-card-text>
          Tem certeza que deseja excluir
          <strong>{{ selectedUser?.name }}</strong>?
          Essa ação não pode ser desfeita.
        </v-card-text>

        <v-card-actions class="justify-end">
          <v-btn variant="text" @click="deleteDialog = false" :disabled="deleting">
            Cancelar
          </v-btn>
          <v-btn color="error" :loading="deleting" @click="confirmDelete">
            Excluir
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Snackbar de feedback -->
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      location="bottom right"
      timeout="3000"
    >
      {{ snackbar.text }}
    </v-snackbar>


  </v-container>
</template>

<style scoped>
/* faixa de voltar separada do título */
.btn-back { padding-inline: 8px; }

/* cabeçalho limpo */
.page-header {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 16px;
  align-items: end;
}

.page-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.search-field { min-width: 280px; }

/* tabela */
.table-card { overflow: hidden; }

/* rodapé da tabela */
.table-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 12px 16px;
  border-top: 1px solid rgba(0,0,0,.06);
}

.footer-controls {
  display: flex;
  align-items: center;
  gap: 12px;
}

.perpage { width: 92px; }

/* Dark mode – clarear levemente a surface do card da tabela */
:deep(.v-theme--dark) .table-card {
  background-color: color-mix(in srgb, var(--v-theme-surface) 88%, white);
}
</style>
