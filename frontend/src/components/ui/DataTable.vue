<script setup lang="ts" generic="T extends Record<string, any>">
  /**
   * DataTable reutilizavel com busca, paginacao e slots dinamicos.
   *
   * @description
   * Componente generico que aceita qualquer tipo de dados via `generic="T"`.
   * Utiliza debounce de 300ms na busca para evitar multiplas chamadas HTTP.
   * As celulas sao renderizadas via slots nomeados `cell-{key}` para
   * permitir formatacao customizada por coluna.
   */
  import { ref, computed } from 'vue';

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
  }>();

  const emit = defineEmits<{
    'update:page': [page: number];
    search: [query: string];
  }>();

  const searchQuery = ref('');
  let timer: ReturnType<typeof setTimeout>;

  function onSearch() {
    clearTimeout(timer);
    timer = setTimeout(() => emit('search', searchQuery.value), 300);
  }

  const totalPages = computed(() => Math.ceil(props.total / props.perPage) || 1);
</script>

<template>
  <div class="space-y-4">
    <input
      v-model="searchQuery"
      class="w-full max-w-sm px-4 py-2 rounded-lg bg-zinc-900 border border-zinc-700 text-zinc-100 placeholder-zinc-500 focus:ring-2 focus:ring-indigo-500 outline-none text-sm"
      placeholder="Buscar..."
      type="text"
      @input="onSearch"
    />

    <div class="overflow-x-auto rounded-lg border border-zinc-800">
      <table class="w-full text-sm text-left">
        <thead class="bg-zinc-800/50 text-zinc-400 uppercase text-xs">
          <tr>
            <th v-for="col in columns" :key="col.key" class="px-4 py-3">
              {{ col.label }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-zinc-800">
          <tr v-if="loading">
            <td :colspan="columns.length" class="px-4 py-8 text-center text-zinc-500">
              Carregando...
            </td>
          </tr>
          <tr v-else-if="data.length === 0">
            <td :colspan="columns.length" class="px-4 py-8 text-center text-zinc-500">
              Nenhum registro encontrado.
            </td>
          </tr>
          <tr v-for="row in data" v-else :key="row.id" class="hover:bg-zinc-800/30">
            <td v-for="col in columns" :key="col.key" class="px-4 py-3 text-zinc-300">
              <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                {{ row[col.key] }}
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="flex items-center justify-between text-sm text-zinc-400">
      <span>Pagina {{ page }} de {{ totalPages }} ({{ total }} registros)</span>
      <div class="flex gap-2">
        <button
          class="px-3 py-1 rounded bg-zinc-800 hover:bg-zinc-700 disabled:opacity-30"
          :disabled="page <= 1"
          @click="emit('update:page', page - 1)"
        >
          Anterior
        </button>
        <button
          class="px-3 py-1 rounded bg-zinc-800 hover:bg-zinc-700 disabled:opacity-30"
          :disabled="page >= totalPages"
          @click="emit('update:page', page + 1)"
        >
          Proximo
        </button>
      </div>
    </div>
  </div>
</template>
