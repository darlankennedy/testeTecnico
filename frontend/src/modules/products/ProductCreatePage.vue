<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useForm, useField } from 'vee-validate'
import { createProduct } from '@/core/application/productService'
import { listAllUsersSimple } from '@/core/application/userService'
import type { User } from '@/core/domain/user'

const router = useRouter()

const { handleSubmit } = useForm({
  validateOnBlur: true,
  validationSchema: {
    name (v: string) {
      if (!v) return 'Nome é obrigatório'
      if (v.trim().length < 2) return 'Nome muito curto'
      return true
    },
    price (v: string) {
      if (!v) return 'Preço é obrigatório'
      const normalized = v.replace(',', '.')
      const n = Number(normalized)
      if (Number.isNaN(n) || n <= 0) return 'Informe um preço válido'
      return true
    },
    user_id (v: number | null) {
      if (!v) return 'Usuário responsável é obrigatório'
      return true
    },
    description (_v: string) {
      return true
    }
  }
})

const name = useField<string>('name')
const price = useField<string>('price') // string para aceitar vírgula; convertemos antes do POST
const description = useField<string>('description')
const user_id = useField<number | null>('user_id')

// ---------- Estado da página
const loading = ref(false)
const apiError = ref<string | null>(null)
const apiSuccess = ref<string | null>(null)
const users = ref<User[]>([])

// ---------- Carregar usuários para o select
async function fetchUsers () {
  try {
    users.value = await listAllUsersSimple() // [{ id, name }]
  } catch (e) {
    console.error('Erro ao buscar usuários:', e)
  }
}

onMounted(fetchUsers)

// ---------- Ações
function goBack () {
  if (window.history.length > 1) router.back()
  else router.push({ name: 'products.list' })
}

const submit = handleSubmit(async (values) => {
  loading.value = true
  apiError.value = null
  apiSuccess.value = null

  try {
    // normaliza preço: "1.234,56" → 1234.56
    const normalized = String(values.price).replace(/\./g, '').replace(',', '.')
    const priceNumber = Number(normalized)

    await createProduct({
      name: values.name,
      price: Number.isNaN(priceNumber) ? 0 : Number(priceNumber.toFixed(2)),
      description: values.description,
      user_id: values.user_id!,
    })

    apiSuccess.value = 'Produto criado com sucesso!'
    setTimeout(() => router.replace({ name: 'products.list' }), 400)
  } catch (err: any) {
    const res = err?.response?.data
    if (res?.errors) {
      const first = Object.keys(res.errors)[0]
      apiError.value = res.errors[first]?.[0] ?? 'Erro ao salvar'
    } else {
      apiError.value = res?.message ?? err?.message ?? 'Erro ao salvar'
    }
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <v-container class="py-6">
    <!-- Barra de voltar -->
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

    <!-- Cabeçalho -->
    <div class="page-header mb-6">
      <div class="page-title">
        <h1 class="text-h5 font-weight-bold mb-1">Novo Produto</h1>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Preencha os dados para cadastrar um novo produto
        </p>
      </div>
    </div>

    <!-- Formulário -->
    <v-card rounded="xl" elevation="3" class="form-card">
      <v-card-text>
        <v-alert
          v-if="apiError"
          type="error"
          variant="tonal"
          density="comfortable"
          class="mb-4"
        >
          {{ apiError }}
        </v-alert>

        <v-alert
          v-if="apiSuccess"
          type="success"
          variant="tonal"
          density="comfortable"
          class="mb-4"
        >
          {{ apiSuccess }}
        </v-alert>

        <form @submit.prevent="submit">
          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="name.value.value"
                :error-messages="name.errorMessage.value"
                label="Nome"
                prepend-inner-icon="mdi-cube"
                required
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="price.value.value"
                :error-messages="price.errorMessage.value"
                label="Preço"
                prefix="R$"
                hint="Use vírgula para centavos (ex.: 99,90)"
                persistent-hint
                prepend-inner-icon="mdi-cash"
                required
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-select
                v-model="user_id.value.value"
                :error-messages="user_id.errorMessage.value"
                :items="users"
                item-title="name"
                item-value="id"
                label="Usuário responsável"
                prepend-inner-icon="mdi-account"
                required
              />
            </v-col>

            <v-col cols="12">
              <v-textarea
                v-model="description.value.value"
                :error-messages="description.errorMessage.value"
                label="Descrição (opcional)"
                auto-grow
                prepend-inner-icon="mdi-text-long"
              />
            </v-col>
          </v-row>

          <div class="form-actions">
            <v-btn variant="text" class="me-2" @click="goBack">
              Cancelar
            </v-btn>
            <v-btn color="primary" :loading="loading" type="submit" prepend-icon="mdi-content-save">
              Salvar
            </v-btn>
          </div>
        </form>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<style scoped>
.btn-back { padding-inline: 8px; }

.page-header {
  display: grid;
  grid-template-columns: 1fr;
  gap: 12px;
}

.form-card { overflow: hidden; }

.form-actions {
  margin-top: 12px;
  display: flex;
  justify-content: flex-end;
}

/* Dark: clarear levemente a surface tal como no UserCreate */
:deep(.v-theme--dark) .form-card {
  background-color: color-mix(in srgb, var(--v-theme-surface) 88%, white);
}
</style>
