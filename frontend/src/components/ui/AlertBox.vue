<script setup lang="ts">
  /**
   * Caixa de alerta reutilizavel com variantes por tipo.
   *
   * Suporta quatro variantes de feedback: error, warning, info e success.
   * Exibe mensagem de texto com estilo condicional por tipo.
   */
  defineProps<{
    type?: 'error' | 'warning' | 'info' | 'success';
  }>();
</script>

<template>
  <div
    v-if="$slots.default"
    class="rounded-input p-3 text-sm"
    :class="
      type === 'warning'
        ? 'bg-warning-bg border border-warning-border text-warning'
        : type === 'info'
          ? 'bg-info-bg border border-info-border text-info'
          : type === 'success'
            ? 'bg-success-bg border border-success-border text-primary-text'
            : 'bg-error-bg border border-error-border text-error-text'
    "
  >
    <slot />
  </div>
</template>
