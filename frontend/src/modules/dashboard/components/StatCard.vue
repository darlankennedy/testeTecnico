<!-- src/components/StatCard.vue -->
<script setup lang="ts">
import { defineProps } from 'vue'

const props = defineProps<{
  title: string
  value?: number | string
  icon?: string
  loading?: boolean
}>()
</script>

<template>
  <v-card rounded="xl" elevation="3" class="stat-card pa-4 d-flex align-center">
    <v-avatar color="primary" variant="tonal" size="40" class="me-4">
      <v-icon :icon="icon" size="22" />
    </v-avatar>

    <div class="flex-grow-1">
      <div class="stat-card__title text-body-2 text-medium-emphasis mb-1">
        {{ title }}
      </div>

      <!-- mostra loader ou valor -->
      <div class="stat-card__value text-h6 font-weight-bold">
        <template v-if="loading">
          <v-skeleton-loader type="text" class="stat-skeleton" />
        </template>
        <template v-else>
          {{ value }}
        </template>
      </div>
    </div>
  </v-card>
</template>

<style scoped>
.stat-card {
  padding: 16px 18px;
  transition: transform .18s ease, box-shadow .18s ease;
  /* usa a cor de fundo do tema */
  background-color: var(--v-theme-surface);
}

.stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(0,0,0,.08) }

.stat-card__top { display:flex; align-items:center; justify-content:space-between; gap:12px; }

/* título suave no light/dark */
.stat-card__title {
  font-size:.86rem;
  font-weight:600;
  letter-spacing:.2px;
  color: color-mix(in srgb, var(--v-theme-on-surface) 70%, transparent);
}

.stat-card__value {
  margin-top: 10px;
  font-size: 1.9rem;
  font-weight: 800;
  letter-spacing: .5px;
  color: var(--v-theme-on-surface);
}

.stat-card__avatar {
  background: color-mix(in srgb, var(--v-theme-primary) 16%, transparent);
  color: var(--v-theme-primary);
}

/* 🔦 Dark mode: clareia ainda mais o card */
:deep(.v-theme--dark) .stat-card {
  background-color: color-mix(in srgb, var(--v-theme-surface) 85%, white);
}
</style>
