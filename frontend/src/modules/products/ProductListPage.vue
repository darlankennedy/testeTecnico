<!-- src/modules/products/pages/ProductListPage.vue -->
<script setup lang="ts">
import { onMounted, ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { deleteProduct as deleteProductService } from '@/core/application/productService'
import { listProducts } from '@/core/application/productService.ts'
import type { Product } from '@/core/domain/product.ts'

const deleteDialog = ref(false)
const deleting = ref(false)
const selectedProduct = ref<Product | null>(null)
const snackbar = ref({ show: false, text: '', color: 'success' as 'success' | 'error' })

const router = useRouter()

const loading = ref(false)
const items = ref<Product[]>([])
const total = ref(0)
const page = ref(1)
const perPage = ref(10)
const search = ref('')

const headers = [
  { title: 'Produto', key: 'name' },
  { title: 'Preço', key: 'price' },
  { title: 'Dono', key: 'owner' },
  { title: 'Criado em', key: 'created_at' },
  { title: 'Ações', key: 'actions', sortable: false },
]

const money = (n: number) =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(n || 0)

async function fetchProducts() {
  loading.value = true
  try {
    const res = await listProducts({
      page: page.value,
      perPage: perPage.value,
      q: search.value,
    })
    items.value = res.data
    total.value = res.total
    page.value = res.page
    perPage.value = res.perPage
  } finally {
    loading.value = false
  }
}

onMounted(fetchProducts)

let t: number | undefined
watch([page, perPage, search], () => {
  clearTimeout(t)
  t = window.setTimeout(fetchProducts, 250)
})

const totalPages = computed(() => Math.max(1, Math.ceil(total.value / perPage.value)))

function goBack() {
  if (window.history.length > 1) router.back()
  else router.push({ name: 'home' })
}
function addProduct() {
  router.push({ name: 'products.create' })
}
function editProduct(p: Product) {
  router.push({ name: 'products.edit', params: { id: p.id } })
}

function viewProduct(p: Product) {
  router.push({ name: 'products.show', params: { id: p.id } })
}

function deleteProduct(p: Product) {
  selectedProduct.value = p
  deleteDialog.value = true
}

async function confirmDelete() {
  if (!selectedProduct.value) return
  try {
    deleting.value = true
    await deleteProductService(selectedProduct.value.id)
    snackbar.value = { show: true, text: 'Produto excluído com sucesso.', color: 'success' }
    deleteDialog.value = false
    selectedProduct.value = null
    await fetchProducts()
    // se a página ficou vazia, volta uma página
    if (items.value.length === 0 && page.value > 1) {
      page.value = page.value - 1
      await fetchProducts()
    }
  } catch (e) {
    snackbar.value = { show: true, text: 'Falha ao excluir produto.', color: 'error' }
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

    <div class="d-flex align-center justify-space-between flex-wrap gap-3 mb-4">
      <div class="d-flex align-center gap-2">
        <div>
          <h2 class="text-h6 font-weight-bold mb-1">Listagem de Produtos</h2>
          <div class="text-body-2 text-medium-emphasis">Gerencie seu catálogo</div>
        </div>
      </div>

      <div class="d-flex align-center gap-2">
        <v-text-field
          v-model="search"
          density="compact"
          variant="outlined"
          prepend-inner-icon="mdi-magnify"
          placeholder="Buscar por nome ou SKU"
          hide-details
          class="mr-3"
          style="min-width: 260px"
        />
        <v-btn class="mr-3" color="primary" prepend-icon="mdi-plus-box" @click="addProduct">
          Adicionar produto
        </v-btn>
      </div>
    </div>

    <v-card rounded="xl" elevation="3">
      <v-data-table
        :headers="headers"
        :items="items"
        :loading="loading"
        :items-per-page="perPage"
        :page="page"
        class="elevation-0"
        hide-default-footer
      >
        <template #loading>
          <div class="pa-6 text-center">
            <v-progress-circular indeterminate size="24" class="me-2" />
            Carregando produtos...
          </div>
        </template>

        <template #item.price="{ item }">
          <span>{{ money(item.price ?? 0) }}</span>
        </template>

        <template #item.owner="{ item }">
          <span>{{item.owner ? item.owner.name : "-" }}</span>
        </template>

        <template #item.created_at="{ item }">
          <span>{{ new Date(item.created_at ?? item.created_at ?? '').toLocaleDateString() }}</span>
        </template>

        <template #item.actions="{ item }">
          <v-menu>
            <template #activator="{ props }">
              <v-btn size="small" variant="text" icon v-bind="props">
                <v-icon size="20">mdi-dots-vertical</v-icon>
              </v-btn>
            </template>

            <v-list>
              <v-list-item
                prepend-icon="mdi-pencil"
                title="Editar"
                @click="editProduct(item)"
              />
              <v-list-item
                prepend-icon="mdi-delete"
                title="Excluir"
                @click="deleteProduct(item)"
              />
              <v-list-item
                prepend-icon="mdi-eye"
                title="Visualizar"
                @click="viewProduct(item)"
              />
            </v-list>
          </v-menu>
        </template>

        <template #no-data>
          <div class="pa-8 text-center text-medium-emphasis">
            <v-icon size="32" class="mb-2">mdi-package-variant</v-icon>
            <div>Nenhum produto encontrado</div>
          </div>
        </template>
      </v-data-table>

      <div class="d-flex align-center justify-space-between flex-wrap gap-3 px-4 py-3">
        <div class="text-body-2 text-medium-emphasis">
          Total: <strong>{{ total }}</strong>
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
  </v-container>

  <!-- Dialog de confirmação -->
  <v-dialog v-model="deleteDialog" max-width="420">
    <v-card rounded="lg">
      <v-card-title class="text-subtitle-1">
        Confirmar exclusão
      </v-card-title>
      <v-card-text>
        Tem certeza que deseja excluir
        <strong>{{ selectedProduct?.name }}</strong>?
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

  <v-snackbar
    v-model="snackbar.show"
    :color="snackbar.color"
    location="bottom right"
    timeout="3000"
  >
    {{ snackbar.text }}
  </v-snackbar>

</template>

<style scoped>
:deep(.v-theme--dark) .v-card {
  background-color: color-mix(in srgb, var(--v-theme-surface) 85%, white);
}
</style>
