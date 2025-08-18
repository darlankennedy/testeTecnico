<script setup lang="ts">
import { ref, defineProps, watch, defineEmits } from 'vue'

const props = defineProps<{
  modelValue: boolean
  type?: 'success' | 'error' | 'info' | 'warning'
  message: string
  timeout?: number
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'closed'): void
}>()

const visible = ref(props.modelValue)

watch(visible, (val) => {
  if (val && props.timeout) {
    setTimeout(() => {
      visible.value = false
      emit('update:modelValue', false)
      emit('closed')
    }, props.timeout)
  }
})

watch(() => props.modelValue, (val) => {
  visible.value = val
})
</script>

<template>
  <transition name="slide-down">
    <div v-if="visible" class="base-alert-wrapper">
      <v-alert
        :type="props.type || 'info'"
        border="start"
        elevation="2"
        prominent
        shaped
      >
        {{ props.message }}
      </v-alert>
    </div>
  </transition>
</template>

<style scoped>
.base-alert-wrapper {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  min-width: 300px;
}

/* Animação de slide para baixo */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
}
.slide-down-enter-from {
  transform: translateY(-50px);
  opacity: 0;
}
.slide-down-enter-to {
  transform: translateY(0);
  opacity: 1;
}
.slide-down-leave-from {
  transform: translateY(0);
  opacity: 1;
}
.slide-down-leave-to {
  transform: translateY(-50px);
  opacity: 0;
}
</style>
