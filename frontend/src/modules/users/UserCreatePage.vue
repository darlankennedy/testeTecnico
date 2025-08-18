<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useForm, useField } from 'vee-validate'
import { createUser } from '@/core/application/userService'
import {validarCPF} from "@/shared/utils/validators.ts";


const router = useRouter()

const { handleSubmit } = useForm({
  validateOnBlur: true,
  validationSchema: {
    name (v: string) {
      if (!v) return 'Nome é obrigatório'
      if (v.trim().length < 2) return 'Nome muito curto'
      return true
    },
    email (v: string) {
      if (!v) return 'E-mail é obrigatório'
      if (!/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i.test(v)) return 'E-mail inválido'
      return true
    },
    cpf (v: string) {
      if (!v) return 'CPF é obrigatório'
      if (!validarCPF(v)) return 'CPF inválido'
      return true
    },
    password (v: string) {
      if (!v) return 'Senha é obrigatória'
      if (v.length < 6) return 'Mínimo 6 caracteres'
      return true
    },
    confirm (v: string, ctx: any) {
      const pwd = (ctx?.form?.password ?? '').trim()
      const conf = (v ?? '').trim()
      if (!conf) return 'Confirme a senha'
      if (conf !== pwd) return 'Senhas não conferem'
      return true
    },
  },
})

const name = useField<string>('name')
const email = useField<string>('email')
const cpf = useField<string>('cpf')
const password = useField<string>('password')
const confirm = useField<string>('confirm')

const loading = ref(false)
const apiError = ref<string | null>(null)
const apiSuccess = ref<string | null>(null)

const submit = handleSubmit(async (values) => {
  loading.value = true
  apiError.value = null
  apiSuccess.value = null
  try {
    await createUser({
      name: values.name,
      email: values.email,
      cpf: values.cpf,
      password: values.password,
      password_confirmation: values.confirm,
    })
    apiSuccess.value = 'Usuário criado com sucesso!'
    setTimeout(() => router.replace({ name: 'users.list' }), 400)
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

function goBack() {
  if (window.history.length > 1) router.back()
  else router.push({ name: 'users.list' })
}
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
        <h1 class="text-h5 font-weight-bold mb-1">Novo Usuário</h1>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Preencha os dados para cadastrar um novo usuário
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
                prepend-inner-icon="mdi-account"
                required
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="email.value.value"
                :error-messages="email.errorMessage.value"
                type="email"
                label="E-mail"
                prepend-inner-icon="mdi-email-outline"
                required
              />
            </v-col>

            <v-col cols="12" md="6">
              <!-- Vuetify Labs Mask Input (registrado no main.ts) -->
              <v-mask-input
                v-model="cpf.value.value"
                :error-messages="cpf.errorMessage.value"
                mask="###.###.###-##"
                label="CPF"
                placeholder="000.000.000-00"
                prepend-inner-icon="mdi-card-account-details-outline"
                required
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="password.value.value"
                :error-messages="password.errorMessage.value"
                type="password"
                label="Senha"
                prepend-inner-icon="mdi-lock-outline"
                required
              />
            </v-col>

            <v-col cols="12" md="3">
              <v-text-field
                v-model="confirm.value.value"
                :error-messages="confirm.errorMessage.value"
                type="password"
                label="Confirmar senha"
                prepend-inner-icon="mdi-lock-check"
                required
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

/* Dark: clarear um pouco a surface */
:deep(.v-theme--dark) .form-card {
  background-color: color-mix(in srgb, var(--v-theme-surface) 88%, white);
}
</style>
