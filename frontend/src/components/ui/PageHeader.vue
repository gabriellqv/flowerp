<script setup lang="ts">
  /**
   * Cabecalho de pagina com titulo e slot para acoes.
   *
   * Padroniza o H1 e a area de acoes (botoes, filtros) no topo
   * de todas as paginas do sistema.
   */
  defineProps<{
    title: string;
  }>();
</script>

<template>
  <div class="flex items-center justify-between">
    <h1 class="text-heading font-semibold">{{ title }}</h1>
    <div v-if="$slots.actions" class="flex items-center gap-2">
      <slot name="actions" />
    </div>
  </div>
</template>
