<template>
  <RouterView />
  <ToastContainer />
</template>

<script setup lang="ts">
  /**
   * Componente raiz da aplicação FlowERP.
   *
   * Renderiza o RouterView principal e o container global
   * de notificações toast (Teleportado via body).
   */
  import ToastContainer from '@/components/ui/ToastContainer.vue';
  import '@/composables/useTheme'; // Inicializa o tema globalmente no boot
</script>
