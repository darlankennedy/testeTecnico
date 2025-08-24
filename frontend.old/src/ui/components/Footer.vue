<script setup lang="ts">
import { computed } from 'vue'

const hoje = computed(() =>
  new Date().toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  })
)
</script>

<template>
  <v-footer app elevation="2" class="py-3">
    <v-container class="text-center">
      <div class="text-body-2">
        © <strong>Darlan Kennedy</strong> — Desenvolvedor • Manaus-AM • {{ hoje }}
      </div>
    </v-container>
  </v-footer>
</template>
