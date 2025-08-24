<!-- src/modules/auth/pages/RegisterPage.vue -->
<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useForm, useField } from 'vee-validate'
import { useAuthStore } from '@/app/stores/useAuthStore'
import {validarCPF} from "@/shared/utils/validators.ts";

const router = useRouter()
const auth = useAuthStore()

const { handleSubmit } = useForm({
  validationSchema: {
    name (v: string) {
      if (!v) return 'Nome é obrigatório'
      if (v.length < 2) return 'Nome muito curto'
      return true
    },
    email (v: string) {
      if (!v) return 'E-mail é obrigatório'
      if (!/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i.test(v)) return 'E-mail inválido'
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
    cpf(value: string) {
      if (!value) return 'CPF é obrigatório'
      if (!validarCPF(value)) return 'CPF inválido'
      return true
    },
  },
})

const name = useField<string>('name')
const email = useField<string>('email')
const password = useField<string>('password')
const confirm = useField<string>('confirm')
const cpf = useField<string>('cpf')

const loading = ref(false)
const apiError = ref<string | null>(null)

const submit = handleSubmit(async (values) => {
  console.log('[register submit] values:', values) // debug: confirme se está disparando
  loading.value = true
  apiError.value = null
  try {
    await auth.register({
      name: values.name,
      email: values.email,
      cpf: values.cpf, // com máscara; o store remove os não-dígitos
      password: values.password,
      password_confirmation: values.confirm,
    })

    if (auth.isAuthenticated) {
      await router.replace({ name: 'home' })
    } else {
      await router.replace({ name: 'login' })
    }
  } catch (err: any) {
    // Mostra mensagens úteis da API (Laravel costuma mandar em err.response.data.errors)
    const res = err?.response?.data
    if (res?.message) {
      apiError.value = res.message
    } else if (res?.errors) {
      // pega a primeira mensagem de erro do bag de validação
      const firstField = Object.keys(res.errors)[0]
      apiError.value = res.errors[firstField]?.[0] ?? 'Erro ao registrar'
    } else {
      apiError.value = err?.message ?? 'Erro ao registrar'
    }
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <v-container class="fill-height d-flex align-center justify-center">
    <v-card width="420" class="pa-6" rounded="xl" elevation="6">
      <h2 class="text-h6 font-weight-bold mb-4">Criar conta</h2>

      <!-- Erro da API -->
      <v-alert
        v-if="apiError"
        type="error"
        variant="tonal"
        density="compact"
        class="mb-4"
      >
        {{ apiError }}
      </v-alert>

      <form @submit.prevent="submit">
        <v-text-field
          v-model="name.value.value"
          :error-messages="name.errorMessage.value"
          label="Nome"
          prepend-inner-icon="mdi-account"
          class="mb-3"
        />
        <v-text-field
          v-model="email.value.value"
          :error-messages="email.errorMessage.value"
          type="email"
          label="E-mail"
          prepend-inner-icon="mdi-email-outline"
          class="mb-3"
        />
        <v-mask-input
          v-model="cpf.value.value"
          label="CPF"
          placeholder="000.000.000-00"
          mask="###.###.###-##"
          class="mb-3"
          :error-messages="cpf.errorMessage.value"
        />
        <v-text-field
          v-model="password.value.value"
          :error-messages="password.errorMessage.value"
          type="password"
          label="Senha"
          prepend-inner-icon="mdi-lock-outline"
          class="mb-3"
        />
        <v-text-field
          v-model="confirm.value.value"
          :error-messages="confirm.errorMessage.value"
          type="password"
          label="Confirmar senha"
          prepend-inner-icon="mdi-lock-check"
          class="mb-5"
        />

        <v-btn
          type="submit"
          block
          color="primary"
          :loading="loading"
          @click.prevent="submit"
        >
          Cadastrar
        </v-btn>
        <v-btn block variant="text" class="mt-2" :to="{ name: 'login' }">
          Já tenho conta
        </v-btn>
      </form>
    </v-card>
  </v-container>
</template>
