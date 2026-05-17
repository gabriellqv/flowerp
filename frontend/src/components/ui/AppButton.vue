<script setup lang="ts">
  /**
   * Botão padronizado com variantes de estilo.
   *
   * Suporta três variantes (primary, secondary, danger) e dois tamanhos.
   * Usa tokens do @theme para cores, radius e espaçamento.
   * Inclui reflexo de vidro no topo para manter a estética glassmorphism
   * consistente com os demais componentes do sistema.
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
    class="font-medium transition-all cursor-pointer disabled:opacity-disabled-button disabled:cursor-not-allowed inline-flex items-center justify-center relative overflow-hidden group"
    :class="[
      variant === 'danger'
        ? 'bg-error/35 backdrop-blur-md border border-error/20 text-error hover:bg-error/45 hover:border-error/40 shadow-sm'
        : variant === 'secondary'
          ? 'bg-[var(--color-glass-bg)] backdrop-blur-md border border-[var(--color-glass-border)] text-[var(--color-text-primary)] hover:border-[var(--color-glass-shine)] shadow-sm'
          : 'bg-primary/50 backdrop-blur-md border border-primary/20 text-primary hover:bg-primary/60 hover:border-primary/40 shadow-sm',
      size === 'sm'
        ? 'px-3 py-1.5 rounded-button-sm text-small'
        : 'px-4 py-2.5 rounded-input text-body',
    ]"
  >
    <!-- Reflexos suaves no topo (vidro) -->
    <div
      v-if="variant === 'danger'"
      class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-error/50 to-transparent opacity-50 z-0 pointer-events-none"
    ></div>
    <div
      v-else-if="variant === 'secondary'"
      class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-[var(--color-glass-shine)] to-transparent opacity-50 z-0 pointer-events-none"
    ></div>
    <div
      v-else
      class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-primary/60 to-transparent opacity-50 z-0 pointer-events-none"
    ></div>

    <span class="relative z-10 flex items-center justify-center gap-2">
      <slot />
    </span>
  </button>
</template>
