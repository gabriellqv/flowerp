<script setup lang="ts" generic="T extends string | number">
  /**
   * Select customizado premium com suporte a v-model e tipagem generica.
   *
   * Substitui o select nativo feio do navegador por uma UI 100% customizavel,
   * com animacoes, hover states consistentes e suporte a click-outside.
   */
  import { ref, computed } from 'vue';
  import { onClickOutside } from '@vueuse/core';
  import { ChevronDown, Check } from 'lucide-vue-next';

  const model = defineModel<T>();

  const props = defineProps<{
    id?: string;
    options: { label: string; value: T }[];
    placeholder?: string;
    disabled?: boolean;
  }>();

  const isOpen = ref(false);
  // eslint-disable-next-line no-undef
  const containerRef = ref<HTMLElement | null>(null);

  onClickOutside(containerRef, () => {
    isOpen.value = false;
  });

  const selectedLabel = computed(() => {
    const opt = props.options.find((o) => String(o.value) === String(model.value));
    if (opt) return opt.label;
    if (props.placeholder) return props.placeholder;
    return 'Selecione...';
  });

  function selectOption(val: T | '') {
    if (props.disabled) return;
    model.value = val as T;
    isOpen.value = false;
  }
</script>

<template>
  <div ref="containerRef" class="relative w-full">
    <button
      :id="id"
      type="button"
      :disabled="disabled"
      class="w-full pl-4 pr-10 py-2 rounded-input bg-[var(--color-glass-bg)] backdrop-blur-md border border-[var(--color-glass-border)] text-left flex items-center justify-between outline-none text-sm disabled:opacity-50 transition-all cursor-pointer relative overflow-hidden"
      :class="[
        isOpen
          ? 'border-primary text-[var(--color-text-primary)]'
          : 'text-[var(--color-text-primary)] hover:border-[var(--color-glass-shine)] shadow-sm',
      ]"
      @click="isOpen = !isOpen"
    >
      <!-- Reflexo suave no topo igual ao card -->
      <div
        class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-[var(--color-glass-shine)] to-transparent opacity-50"
      ></div>
      <span
        class="truncate relative z-10"
        :class="{ 'text-tertiary': !model && model !== 0 && placeholder }"
      >
        {{ selectedLabel }}
      </span>
      <span
        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-tertiary"
      >
        <ChevronDown
          :size="16"
          class="transition-transform duration-200"
          :class="{ 'rotate-180': isOpen }"
        />
      </span>
    </button>

    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="transform scale-95 opacity-0 -translate-y-2"
      enter-to-class="transform scale-100 opacity-100 translate-y-0"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="transform scale-100 opacity-100 translate-y-0"
      leave-to-class="transform scale-95 opacity-0 -translate-y-2"
    >
      <div
        v-if="isOpen"
        class="absolute z-50 w-full mt-2 bg-surface border border-border rounded-md shadow-xl overflow-hidden backdrop-blur-xl"
      >
        <ul class="max-h-60 overflow-y-auto py-1 text-sm scrollbar-thin">
          <li
            v-if="placeholder"
            class="px-4 py-2.5 cursor-pointer hover:bg-surface-elevated hover:text-primary transition-colors text-tertiary flex items-center justify-between"
            @click="selectOption('')"
          >
            <span class="truncate">{{ placeholder }}</span>
            <Check v-if="model === '' || model === undefined" :size="14" class="text-primary" />
          </li>
          <li
            v-for="opt in options"
            :key="String(opt.value)"
            class="px-4 py-2.5 cursor-pointer transition-colors flex items-center justify-between"
            :class="
              model === opt.value
                ? 'text-primary bg-primary/10 font-medium'
                : 'text-[var(--color-text-primary)] hover:bg-surface-elevated hover:text-primary'
            "
            @click="selectOption(opt.value)"
          >
            <span class="truncate">{{ opt.label }}</span>
            <Check v-if="model === opt.value" :size="16" class="text-primary" />
          </li>
        </ul>
      </div>
    </Transition>
  </div>
</template>
