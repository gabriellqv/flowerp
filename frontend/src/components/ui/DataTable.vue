<script setup lang="ts" generic="T extends Record<string, any>">
  /**
   * DataTable reutilizável com busca, paginação e slots dinâmicos.
   *
   * @description
   * Componente genérico que aceita qualquer tipo de dados via `generic="T"`.
   * Utiliza debounce de 300ms na busca para evitar múltiplas chamadas HTTP.
   * As células são renderizadas via slots nomeados `cell-{key}` para
   * permitir formatação customizada por coluna.
   * Suporta ordenação via clique no cabeçalho, seleção em massa e
   * seletor de itens por página.
   */
  import { ref, computed } from 'vue';
  import AppInput from '@/components/ui/AppInput.vue';
  import AppButton from '@/components/ui/AppButton.vue';
  import { ArrowUpDown, ArrowUp, ArrowDown, Square, CheckSquare } from 'lucide-vue-next';

  interface Column {
    key: string;
    label: string;
    sortable?: boolean;
  }

  interface PaginationRange {
    label: string;
    value: number;
  }

  const PER_PAGE_OPTIONS: PaginationRange[] = [
    { label: '10 por página', value: 10 },
    { label: '15 por página', value: 15 },
    { label: '20 por página', value: 20 },
    { label: '50 por página', value: 50 },
  ];

  const props = defineProps<{
    columns: Column[];
    data: T[];
    total: number;
    page: number;
    perPage: number;
    loading: boolean;
    refreshing?: boolean;
    sortBy?: string;
    sortDir?: 'asc' | 'desc';
    selected?: string[];
  }>();

  const emit = defineEmits<{
    'update:page': [page: number];
    'update:perPage': [perPage: number];
    search: [query: string];
    sort: [column: string];
    'toggle-select': [id: string];
    'toggle-select-all': [];
  }>();

  const searchQuery = ref('');
  let timer: ReturnType<typeof setTimeout>;

  function onSearch() {
    clearTimeout(timer);
    timer = setTimeout(() => emit('search', searchQuery.value), 300);
  }

  const totalPages = computed(() => Math.ceil(props.total / props.perPage) || 1);

  function onSort(col: Column) {
    if (!col.sortable) return;
    emit('sort', col.key);
  }

  function sortIcon(col: Column) {
    if (!col.sortable) return null;
    if (props.sortBy !== col.key) return ArrowUpDown;
    return props.sortDir === 'asc' ? ArrowUp : ArrowDown;
  }

  const hasSelection = computed(() => (props.selected?.length ?? 0) > 0);

  const allSelected = computed(() => {
    if (!props.data.length) return false;
    return props.data.every((row) => props.selected?.includes(row.id));
  });

  const SKELETON_ROWS = 8;
  const skeletonArray = Array.from({ length: SKELETON_ROWS }, (_, i) => i);
</script>

<template>
  <div class="space-y-4">
    <AppInput
      v-model="searchQuery"
      class="max-w-sm !bg-surface-secondary"
      placeholder="Buscar..."
      @input="onSearch"
    />

    <div
      class="overflow-x-auto rounded-card border border-[var(--color-glass-border)] bg-[var(--color-glass-bg)] backdrop-blur-xl shadow-[0_4px_24px_-8px_rgba(0,0,0,0.1)] relative"
    >
      <div
        class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-[var(--color-glass-shine)] to-transparent"
      ></div>

      <div
        v-if="refreshing"
        class="absolute inset-0 bg-surface/40 backdrop-blur-[2px] z-10 flex items-center justify-center pointer-events-none"
      >
        <span class="text-sm text-tertiary animate-pulse">Atualizando...</span>
      </div>

      <table class="w-full text-sm text-left">
        <thead class="bg-surface-elevated/30 text-secondary uppercase text-[11px] tracking-wider">
          <tr>
            <th v-if="selected !== undefined" class="w-10 px-2 py-3">
              <button
                class="flex items-center justify-center cursor-pointer text-tertiary hover:text-primary transition-colors"
                @click="emit('toggle-select-all')"
              >
                <CheckSquare v-if="allSelected" :size="16" class="text-primary-text" />
                <Square v-else :size="16" />
              </button>
            </th>
            <th
              v-for="col in columns"
              :key="col.key"
              class="px-4 py-3"
              :class="{
                'cursor-pointer select-none hover:text-primary hover:bg-surface-elevated/30 transition-colors':
                  col.sortable,
              }"
              @click="onSort(col)"
            >
              <span class="inline-flex items-center gap-1">
                {{ col.label }}
                <component
                  :is="sortIcon(col)"
                  v-if="col.sortable"
                  :size="12"
                  :class="sortBy === col.key ? 'text-primary-text' : 'text-tertiary'"
                />
              </span>
            </th>
          </tr>
        </thead>

        <tbody class="divide-y divide-border/50">
          <template v-if="loading">
            <tr v-for="i in skeletonArray" :key="i">
              <td v-if="selected !== undefined" class="px-2 py-3">
                <div
                  class="h-4 w-4 bg-zinc-200/50 dark:bg-zinc-700/30 rounded animate-pulse mx-auto"
                ></div>
              </td>
              <td v-for="col in columns" :key="col.key" class="px-4 py-3">
                <div
                  class="h-4 bg-zinc-200/50 dark:bg-zinc-700/30 rounded animate-pulse w-3/4"
                ></div>
              </td>
            </tr>
          </template>
          <tr v-else-if="data.length === 0">
            <td :colspan="columns.length + (selected !== undefined ? 1 : 0)" class="px-4 py-12">
              <slot name="empty" />
            </td>
          </tr>
          <tr
            v-for="row in data"
            v-else
            :key="row.id"
            class="hover:bg-surface-elevated/20 transition-colors"
            :class="{ 'bg-primary/5': selected?.includes(row.id) }"
          >
            <td v-if="selected !== undefined" class="px-2 py-3">
              <button
                class="flex items-center justify-center cursor-pointer text-tertiary hover:text-primary-text transition-colors"
                @click="emit('toggle-select', row.id)"
              >
                <CheckSquare
                  v-if="selected?.includes(row.id)"
                  :size="16"
                  class="text-primary-text"
                />
                <Square v-else :size="16" />
              </button>
            </td>
            <td v-for="col in columns" :key="col.key" class="px-4 py-3 text-primary">
              <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                <span class="font-mono">{{ row[col.key] }}</span>
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-sm text-secondary"
    >
      <div class="flex items-center gap-3">
        <span>Página {{ page }} de {{ totalPages }} ({{ total }} registros)</span>
        <select
          :value="perPage"
          class="text-xs rounded-input bg-surface-elevated border-border border pl-2 pr-6 py-1 outline-none"
          @change="emit('update:perPage', Number(($event.target as HTMLSelectElement).value))"
        >
          <option v-for="opt in PER_PAGE_OPTIONS" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </select>
      </div>

      <div class="flex flex-wrap gap-2">
        <div v-if="hasSelection" class="mr-2">
          <slot name="bulk-actions" />
        </div>

        <div class="flex gap-2">
          <AppButton
            size="sm"
            variant="secondary"
            :disabled="page <= 1"
            @click="emit('update:page', page - 1)"
          >
            Anterior
          </AppButton>
          <AppButton
            size="sm"
            variant="secondary"
            :disabled="page >= totalPages"
            @click="emit('update:page', page + 1)"
          >
            Próximo
          </AppButton>
        </div>
      </div>
    </div>
  </div>
</template>
