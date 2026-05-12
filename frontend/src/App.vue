<script setup lang="ts">
  /**
   * Componente raiz da aplicacao FlowERP.
   *
   * Renderiza o RouterView principal e o container global
   * de notificacoes toast (Teleportado via body).
   * Restaura a sessao do usuario no boot caso exista token valido.
   */
  import { onMounted } from 'vue';
  import { useAuthStore } from '@/stores/auth';
  import ToastContainer from '@/components/ui/ToastContainer.vue';
  import '@/composables/useTheme'; // Inicializa o tema globalmente no boot

  const auth = useAuthStore();

  onMounted(async () => {
    if (auth.isAuthenticated) {
      await auth.fetchMe();
    }
  });
</script>

<template>
  <RouterView />
  <ToastContainer />
</template>
