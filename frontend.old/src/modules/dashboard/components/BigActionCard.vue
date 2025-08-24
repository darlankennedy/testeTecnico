<script setup lang="ts">
defineProps<{
  title: string
  subtitle?: string
  icon?: string
}>()
defineEmits<{ (e:'click'): void }>()
</script>

<template>
  <v-card class="action-card" rounded="xl" elevation="4" @click="$emit('click')">
    <div class="action-card__icon">
      <v-avatar size="72" class="action-card__avatar">
        <v-icon size="40">{{ icon || 'mdi-rocket-launch' }}</v-icon>
      </v-avatar>
    </div>
    <div class="action-card__text">
      <div class="action-card__title">{{ title }}</div>
      <div v-if="subtitle" class="action-card__subtitle">{{ subtitle }}</div>
    </div>
  </v-card>
</template>

<style scoped>
.action-card {
  cursor: pointer;
  padding: 24px 28px;
  display:flex; align-items:center; gap:20px;
  transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
  background: linear-gradient(180deg, rgba(25,118,210,.03), transparent);
}
.action-card:hover { transform: translateY(-3px); box-shadow: 0 14px 28px rgba(0,0,0,.10) }
.action-card__icon { display:flex; align-items:center; justify-content:center }
.action-card__avatar {
  background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
  color: white;
  box-shadow: 0 10px 30px rgba(25,118,210,.35);
}
.dark .action-card__avatar {
  background: linear-gradient(135deg, #7c4dff 0%, #bb86fc 100%);
  box-shadow: 0 10px 30px rgba(187,134,252,.35);
}
.action-card__text { display:flex; flex-direction:column }
.action-card__title { font-weight:800; font-size:1.1rem; letter-spacing:.3px }
.action-card__subtitle { opacity:.7; margin-top:4px; font-size:.92rem }
</style>
