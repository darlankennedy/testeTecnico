<!-- src/modules/auth/pages/LoginPage.vue -->
<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/app/stores/useAuthStore.ts'
import { useTheme } from 'vuetify'
import { useForm, useField } from 'vee-validate'
import { useRoute, useRouter } from 'vue-router'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const { handleSubmit, handleReset } = useForm({
  validationSchema: {
    email (value: string) {
      if (!value) return 'E-mail é obrigatório'
      if (!/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i.test(value)) {
        return 'Formato de e-mail inválido'
      }
      return true
    },
    password (value: string) {
      if (!value) return 'Senha é obrigatória'
      if (value.length < 6) return 'A senha deve ter pelo menos 6 caracteres'
      return true
    },
  },
})

const email = useField<string>('email')
const password = useField<string>('password')

const alertVisible = ref(false)
const alertMessage = ref('')
const alertType = ref<'success' | 'error' | 'info' | 'warning'>('info')

const theme = useTheme()
const isDark = ref(theme.global.current.value.dark)
const toggleTheme = () => {
  isDark.value = !isDark.value
  theme.change(isDark.value ? 'dark' : 'light')
}

const submit = handleSubmit(async (values) => {
  try {
    await authStore.login(values.email, values.password)
    const to = (route.query.redirect as string) || { name: 'home' }
    await router.replace(to)
    alertMessage.value = 'Usuário autenticado com sucesso!'
    alertType.value = 'success'
    alertVisible.value = true
  } catch (err: any) {
    // CORREÇÃO: usar crases
    alertMessage.value = `Erro no login: ${err?.message ?? 'Falha ao autenticar'}`
    alertType.value = 'error'
    alertVisible.value = true
  }
})

// 👉 ação para ir ao registro
const goToRegister = () => router.push({ name: 'register' })
</script>

<template>
  <v-container class="fill-height d-flex flex-column align-center justify-center text-center">
    <div class="d-flex justify-end w-100 pa-4 fixed-header">
      <v-btn icon variant="tonal" @click="toggleTheme">
        <v-icon>{{ isDark ? 'mdi-white-balance-sunny' : 'mdi-weather-night' }}</v-icon>
      </v-btn>
    </div>

    <v-card elevation="8" class="pa-6 rounded-xl" width="400">
      <h2 class="mb-6 font-weight-bold">Login</h2>

      <form @submit.prevent="submit">
        <v-text-field
          v-model="email.value.value"
          type="email"
          label="E-mail"
          prepend-inner-icon="mdi-email-outline"
          variant="outlined"
          density="comfortable"
          class="mb-4"
          :error-messages="email.errorMessage.value"
          @blur="email.handleBlur"
        />

        <v-text-field
          v-model="password.value.value"
          label="Senha"
          type="password"
          prepend-inner-icon="mdi-lock-outline"
          variant="outlined"
          density="comfortable"
          class="mb-6"
          :error-messages="password.errorMessage.value"
          @blur="password.handleBlur"
        />

        <v-btn
          type="submit"
          block
          color="primary"
          size="large"
          class="rounded-lg"
          :loading="authStore.loading"
        >
          Entrar
        </v-btn>

        <v-btn variant="text" class="mt-3" @click="handleReset()">Limpar</v-btn>
      </form>

      <!-- separador + CTA de registro -->
      <v-divider class="my-4" />
      <div class="text-body-2">
        Não tem uma conta?
        <v-btn variant="text" color="primary" class="text-none px-1" @click="goToRegister">
          Criar conta
        </v-btn>
      </div>
    </v-card>

    <BaseAlert v-model="alertVisible" :type="alertType" :message="alertMessage" :timeout="3000" />
  </v-container>
</template>

<style scoped>
.fill-height { min-height: 100vh; }
.fixed-header { position: absolute; top: 0; right: 0; }
h2 { font-size: 1.6rem; }
</style>
