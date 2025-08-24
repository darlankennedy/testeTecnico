<template>
  <v-app>
    <Navbar />
    <v-main>
      <router-view />
    </v-main>
    <Footer />
  </v-app>
</template>

<script setup lang="ts">
import Navbar from '@/ui/components/Navbar.vue'
import Footer from '@/ui/components/Footer.vue'
</script>
