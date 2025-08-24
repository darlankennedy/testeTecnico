<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { countUsers, countUsersWithoutProducts } from '@/core/application/userService'
import { countProducts } from '@/core/application/productService'
import StatCard from '@/modules/dashboard/components/StatCard.vue'
import BigActionCard from '@/modules/dashboard/components/BigActionCard.vue'

const router = useRouter()
const usersTotal = ref(0)
const productsTotal = ref(0)
const usersNoProduct = ref(0)
const loading = ref(true)

onMounted(async () => {
  try {
    const [u, p, up] = await Promise.all([
      countUsers().catch(() => 0),
      countProducts().catch(() => 0),
      countUsersWithoutProducts().catch(() => 0),
    ])
    usersTotal.value = u ?? 0
    productsTotal.value = p ?? 0
    usersNoProduct.value = up ?? 0
  } finally {
    loading.value = false
  }
})

const goUsers = () => router.push({ name: 'users.list' })
const goProducts = () => router.push({ name: 'products.list' })

const goReports = () => router.push({ name: 'reports' })


</script>

<template>
  <v-container class="hero-container">
    <v-row class="mx-auto metrics-row" justify="center" align="stretch">
      <v-col cols="12" sm="6" md="4">
        <StatCard
          title="Usuários (total)"
          :value="usersTotal"
          :loading="loading"
          icon="mdi-account-multiple-outline"
        />
      </v-col>
      <v-col cols="12" sm="6" md="4">
        <StatCard
          title="Produtos (total)"
          :value="productsTotal"
          :loading="loading"
          icon="mdi-cube-outline"
        />
      </v-col>
      <v-col cols="12" sm="6" md="4">
        <StatCard
          title="Usuários sem Produtos"
          :value="usersNoProduct"
          :loading="loading"
          icon="mdi-account-alert-outline"
        />
      </v-col>
    </v-row>

    <div class="cta-wrap">
      <v-row class="cta-row" justify="center" align="center">
        <v-col cols="12" md="5" class="mb-6">
          <BigActionCard
            title="Gerenciar Usuários"
            subtitle="Criar, editar e atribuir produtos"
            icon="mdi-account-cog"
            @click="goUsers"
          />
        </v-col>

        <v-col cols="12" md="5" class="mb-6">
          <BigActionCard
            title="Gerenciar Produtos"
            subtitle="Catálogo, preços e estoque"
            icon="mdi-cube-scan"
            @click="goProducts"
          />
        </v-col>

        <!-- Novo botão de Relatórios -->
        <v-col cols="12" md="5" class="mb-6">
          <BigActionCard
            title="Relatórios"
            subtitle="Visualizar estatísticas e gerar relatórios"
            icon="mdi-file-chart"
            @click="goReports"
          />
        </v-col>
      </v-row>
    </div>

  </v-container>
</template>

<style scoped>
.hero-container {
  min-height: calc(100vh - 140px);
  display: flex;
  flex-direction: column;
  gap: 22px;
  padding-top: 18px;
  padding-bottom: 22px;
  max-width: 1200px;
}

.metrics-row { width: 100%; }

.cta-wrap {
  flex: 1;
  display: flex;
  align-items: center;
}
.cta-row { width: 100%; }

@media (min-width: 1440px) {
  .hero-container { max-width: 1280px; }
}
</style>
