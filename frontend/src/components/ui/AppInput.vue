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
  <input
    :id="id"
    v-model="model"
    :type="type ?? 'text'"
    :placeholder="placeholder"
    :required="required"
    :disabled="disabled"
    class="w-full px-4 py-2 rounded-input bg-surface-elevated border border-input text-primary placeholder:text-tertiary focus:ring-2 focus:ring-primary-hover outline-none text-sm disabled:opacity-50"
  />
</template>
