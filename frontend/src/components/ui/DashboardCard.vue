<script setup lang="ts">
  /**
   * Card de KPI para o Dashboard.
   *
   * Exibe icone grande em circulo com sombra a esquerda,
   * e rotulo + valor numerico a direita.
   * Usa slot para o icone (componente lucide-vue-next).
   */
  defineProps<{
    label: string;
    value: string | number;
  }>();
</script>

<template>
  <div class="bg-surface rounded-card border border-border p-card flex items-center gap-4">
    <div
      class="w-12 h-12 rounded-full bg-surface-secondary flex items-center justify-center shrink-0 shadow-md"
    >
      <slot name="icon" />
    </div>
    <div>
      <span class="text-body text-secondary block">{{ label }}</span>
      <p class="text-heading font-bold">{{ value }}</p>
    </div>
  </div>
</template>
