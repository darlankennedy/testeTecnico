<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useTheme } from 'vuetify'
import { useAuthStore } from '@/app/stores/useAuthStore.ts'
import { env } from '@/app/config/env'

const router = useRouter()
const theme = useTheme()
const auth = useAuthStore()

const THEME_KEY = 'APP_THEME'

const saved = (localStorage.getItem(THEME_KEY) as 'light' | 'dark' | null)
const prefersDark = window.matchMedia?.('(prefers-color-scheme: dark)').matches
const initialTheme: 'light' | 'dark' = saved ?? (prefersDark ? 'dark' : 'light')

theme.global.name.value = initialTheme
const isDark = ref(initialTheme === 'dark')

const toggleTheme = () => {
  const next: 'light' | 'dark' = isDark.value ? 'light' : 'dark'
  isDark.value = !isDark.value
  theme.global.name.value = next
  localStorage.setItem(THEME_KEY, next)
}

const userName = computed(() => auth.user?.name ?? 'Usuário')

const initials = computed(() => {
  const n = (auth.user?.name ?? '').trim()
  if (!n) return 'U'
  const parts = n.split(/\s+/)
  return (parts[0]?.[0] ?? '') + (parts[parts.length - 1]?.[0] ?? '')
})

const notifications = ref([
  { id: 1, text: 'Bem-vindo(a) de volta!' },
  { id: 2, text: 'Você tem 2 tarefas pendentes.' },
])
const notifCount = computed(() => notifications.value.length)

const goProfile = () => router.push({ name: 'home' })
const doLogout = async () => {
  await auth.logout()
  router.replace({ name: 'login' })
}
</script>

<template>
  <v-app-bar elevation="2" density="comfortable">
    <v-btn icon variant="text" class="me-2" :ripple="false">
      <v-icon>mdi-rocket-launch</v-icon>
    </v-btn>

    <v-app-bar-title class="text-subtitle-1 font-weight-medium">
      {{ env.appName ?? 'Meu Sistema' }}
    </v-app-bar-title>

    <v-spacer />

    <v-btn icon variant="text" class="me-1" @click="toggleTheme" :title="isDark ? 'Tema claro' : 'Tema escuro'">
      <v-icon>{{ isDark ? 'mdi-white-balance-sunny' : 'mdi-weather-night' }}</v-icon>
    </v-btn>
    <v-menu offset-y>
      <template #activator="{ props }">
        <v-badge :content="notifCount" :model-value="notifCount > 0" color="error" overlap>
          <v-btn icon variant="text" v-bind="props" :title="'Notificações'">
            <v-icon>mdi-bell-outline</v-icon>
          </v-btn>
        </v-badge>
      </template>

      <v-card min-width="300">
        <v-list density="comfortable">
          <v-list-subheader>Notificações</v-list-subheader>
          <template v-if="notifications.length">
            <v-list-item v-for="n in notifications" :key="n.id" :title="n.text" />
          </template>
          <template v-else>
            <v-list-item title="Sem notificações" />
          </template>
        </v-list>
      </v-card>
    </v-menu>

    <v-menu offset-y>
      <template #activator="{ props }">
        <v-btn v-bind="props" variant="text" class="ms-2" rounded="xl">
          <v-avatar size="32" class="me-2" color="primary">
            <span class="text-white text-body-2">{{ initials }}</span>
          </v-avatar>
          <span class="text-body-2 d-none d-sm-inline">{{ userName }}</span>
          <v-icon class="ms-1 d-none d-sm-inline">mdi-menu-down</v-icon>
        </v-btn>
      </template>

      <v-card min-width="220">
        <v-list density="comfortable">
          <v-list-item
            :title="userName"
            subtitle="Logado"
            prepend-icon="mdi-account-circle"
          />
          <v-divider />
          <v-list-item title="Perfil" prepend-icon="mdi-account" @click="goProfile" />
          <v-list-item title="Sair" prepend-icon="mdi-logout" @click="doLogout" />
        </v-list>
      </v-card>
    </v-menu>
  </v-app-bar>
</template>
