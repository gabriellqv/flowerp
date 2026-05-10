<script setup lang="ts" generic="T extends Record<string, any>">
  /**
   * DataTable reutilizável com busca, paginação e slots dinâmicos.
   *
   * @description
   * Componente genérico que aceita qualquer tipo de dados via `generic="T"`.
   * Utiliza debounce de 300ms na busca para evitar múltiplas chamadas HTTP.
   * As células são renderizadas via slots nomeados `cell-{key}` para
   * permitir formatação customizada por coluna.
   * Suporta ordenação via clique no cabeçalho das colunas com `sortable`.
   */
  import { ref, computed } from 'vue';
  import AppInput from '@/components/ui/AppInput.vue';
  import AppButton from '@/components/ui/AppButton.vue';
  import { ArrowUpDown, ArrowUp, ArrowDown } from 'lucide-vue-next';

  interface Column {
    key: string;
    label: string;
    sortable?: boolean;
  }

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
  }>();

  const emit = defineEmits<{
    'update:page': [page: number];
    search: [query: string];
    sort: [column: string];
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
</script>

<template>
  <div class="space-y-4">
    <AppInput
      v-model="searchQuery"
      class="max-w-sm !bg-surface-secondary"
      placeholder="Buscar..."
      @input="onSearch"
    />

    <div class="overflow-x-auto rounded-input border border relative">
      <div
        v-if="refreshing"
        class="absolute inset-0 bg-surface/50 backdrop-blur-[2px] z-10 flex items-center justify-center pointer-events-none"
      >
        <span class="text-sm text-tertiary animate-pulse">Atualizando...</span>
      </div>
      <table class="w-full text-sm text-left">
        <thead class="bg-surface-elevated/50 text-secondary uppercase text-xs">
          <tr>
            <th
              v-for="col in columns"
              :key="col.key"
              class="px-4 py-3"
              :class="{
                'cursor-pointer select-none hover:text-primary hover:bg-surface-elevated/70 transition-colors':
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
        <tbody class="divide-y divide">
          <tr v-if="loading">
            <td :colspan="columns.length" class="px-4 py-8 text-center text-tertiary">
              Carregando...
            </td>
          </tr>
          <tr v-else-if="data.length === 0">
            <td :colspan="columns.length" class="px-4 py-8 text-center text-tertiary">
              Nenhum registro encontrado.
            </td>
          </tr>
          <tr v-for="row in data" v-else :key="row.id" class="hover:bg-surface-elevated/30">
            <td v-for="col in columns" :key="col.key" class="px-4 py-3 text-primary">
              <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                <span class="font-mono">{{ row[col.key] }}</span>
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="flex items-center justify-between text-sm text-secondary">
      <span>Página {{ page }} de {{ totalPages }} ({{ total }} registros)</span>
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
</template>
