<script setup lang="ts" generic="T extends string | number">
  /**
   * Select nativo padronizado com suporte a v-model e tipagem generica.
   *
   * Aceita array de opcoes com label e value genericos.
   * Estilizacao identica ao AppInput para consistencia visual.
   */
  const model = defineModel<T>();

  defineProps<{
    id?: string;
    options: { label: string; value: T }[];
    placeholder?: string;
    disabled?: boolean;
  }>();
</script>

<template>
  <select
    :id="id"
    v-model="model"
    :disabled="disabled"
    class="px-4 py-2 rounded-input bg-surface-secondary border border-input text-primary focus:ring-2 focus:ring-primary-hover outline-none text-sm disabled:opacity-50"
  >
    <option v-if="placeholder" value="">{{ placeholder }}</option>
    <option v-for="opt in options" :key="String(opt.value)" :value="opt.value">
      {{ opt.label }}
    </option>
  </select>
</template>
