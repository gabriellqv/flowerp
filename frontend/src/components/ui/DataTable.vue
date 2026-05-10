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
  import AppInput from '@/components/ui/AppInput.vue';
  import AppButton from '@/components/ui/AppButton.vue';

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
    <AppInput
      v-model="searchQuery"
      class="max-w-sm !bg-surface-secondary"
      placeholder="Buscar..."
      @input="onSearch"
    />

    <div class="overflow-x-auto rounded-input border border">
      <table class="w-full text-sm text-left">
        <thead class="bg-surface-elevated/50 text-secondary uppercase text-xs">
          <tr>
            <th v-for="col in columns" :key="col.key" class="px-4 py-3">
              {{ col.label }}
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
      <span>Pagina {{ page }} de {{ totalPages }} ({{ total }} registros)</span>
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
          Proximo
        </AppButton>
      </div>
    </div>
  </div>
</template>
