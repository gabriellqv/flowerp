<script setup lang="ts">
  /**
   * Campo de input padronizado com suporte a v-model.
   *
   * Todas as classes de estilo usam tokens do @theme (Tailwind v4),
   * garantindo consistencia entre todos os inputs do sistema.
   */
  const model = defineModel<string>();

  defineProps<{
    id?: string;
    type?: string;
    placeholder?: string;
    required?: boolean;
    disabled?: boolean;
  }>();
</script>

<template>
  <div class="relative w-full rounded-input overflow-hidden">
    <input
      :id="id"
      v-model="model"
      :type="type ?? 'text'"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
      class="w-full px-4 py-2 rounded-input bg-[var(--color-glass-bg)] backdrop-blur-md border border-[var(--color-glass-border)] text-[var(--color-text-primary)] placeholder:text-tertiary focus:border-primary outline-none text-sm disabled:opacity-50 transition-all shadow-sm relative z-10 hover:border-[var(--color-glass-shine)]"
    />
    <!-- Reflexo suave no topo -->
    <div
      class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-[var(--color-glass-shine)] to-transparent opacity-50 z-20 pointer-events-none"
    ></div>
  </div>
</template>
