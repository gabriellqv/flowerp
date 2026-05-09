<script setup lang="ts">
  /**
   * Botao padronizado com variantes de estilo.
   *
   * Suporta tres variantes (primary, secondary, danger) e dois tamanhos.
   * Usa tokens do @theme para cores, radius e espacamento.
   */
  defineProps<{
    variant?: 'primary' | 'secondary' | 'danger';
    size?: 'sm' | 'default';
    type?: 'button' | 'submit';
    disabled?: boolean;
  }>();
</script>

<template>
  <button
    :type="type ?? 'button'"
    :disabled="disabled"
    class="font-medium transition-colors cursor-pointer disabled:opacity-disabled-button disabled:cursor-not-allowed"
    :class="[
      variant === 'danger'
        ? 'bg-error-bg border border-error-border text-error-text hover:bg-error-bg hover:border-error-hover'
        : variant === 'secondary'
          ? 'bg-surface-elevated hover:bg-border-input'
          : 'bg-primary hover:bg-primary-hover',
      size === 'sm'
        ? 'px-3 py-1 rounded-button-sm text-small'
        : 'px-4 py-2 rounded-input text-body',
    ]"
  >
    <slot />
  </button>
</template>
