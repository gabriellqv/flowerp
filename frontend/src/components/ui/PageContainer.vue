<script setup lang="ts">
  /**
   * Container de pagina com padding e espacamento padrao.
   *
   * Wrapper que garante consistencia de layout (p-page + space-y-6)
   * em todas as paginas do sistema.
   */
</script>

<template>
  <div class="p-page space-y-section">
    <slot />
  </div>
</template>
