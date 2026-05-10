<script setup lang="ts">
  /**
   * Listagem de produtos com DataTable, busca, ordenação e filtros.
   *
   * Carrega categorias no mount para exibição dos nomes
   * em vez de UUIDs e popula o filtro por categoria.
   * A busca reseta a página para 1.
   * Suporte a ordenação via cabeçalho da tabela com direção
   * alternada (asc/desc) e ícones indicativos.
   */
  import { ref, onMounted, watch, computed } from 'vue';
  import { useRouter } from 'vue-router';
  import api from '@/services/api';
  import type { Product, PaginatedResponse, Category } from '@/types';
  import { formatCurrency } from '@/utils/format';
  import DataTable from '@/components/ui/DataTable.vue';
  import PageContainer from '@/components/ui/PageContainer.vue';
  import PageHeader from '@/components/ui/PageHeader.vue';
  import AppButton from '@/components/ui/AppButton.vue';
  import AppSelect from '@/components/ui/AppSelect.vue';
  import { Plus, Pencil, Trash2 } from 'lucide-vue-next';

  const router = useRouter();

  const products = ref<Product[]>([]);
  const total = ref(0);
  const page = ref(1);
  const perPage = ref(15);
  const loading = ref(true);
  const refreshing = ref(false);
  const search = ref('');
  const categories = ref<Category[]>([]);
  const selectedCategory = ref('');
  const sortBy = ref('name');
  const sortDir = ref<'asc' | 'desc'>('asc');
  let abortController: AbortController | null = null;

  const columns = [
    { key: 'sku', label: 'SKU', sortable: true },
    { key: 'name', label: 'Nome', sortable: true },
    { key: 'category', label: 'Categoria' },
    { key: 'sale_price', label: 'Preço', sortable: true },
    { key: 'stock_quantity', label: 'Estoque', sortable: true },
    { key: 'status', label: 'Status' },
    { key: 'actions', label: 'Ações' },
  ];

  interface SelectOption {
    label: string;
    value: string;
  }

  const categoryOptions = computed<SelectOption[]>(() => [
    { label: 'Todas as categorias', value: '' },
    ...categories.value.map((c) => ({ label: c.name, value: c.id })),
  ]);

  async function fetchProducts() {
    if (abortController) {
      abortController.abort();
    }

    const controller = new AbortController();
    abortController = controller;

    if (products.value.length > 0) {
      refreshing.value = true;
    } else {
      loading.value = true;
    }

    try {
      const params: Record<string, unknown> = {
        page: page.value,
        per_page: perPage.value,
        search: search.value || undefined,
        sort_by: sortBy.value,
        order: sortDir.value,
        category_id: selectedCategory.value || undefined,
      };
      const { data } = await api.get<PaginatedResponse<Product>>('/products', {
        params,
        signal: controller.signal,
      });
      products.value = data.data;
      total.value = data.total;
    } catch (e: unknown) {
      const err = e as { code?: string };
      if (err.code === 'ERR_CANCELED') return;
      throw e;
    } finally {
      if (!controller.signal.aborted) {
        loading.value = false;
        refreshing.value = false;
      }
    }
  }

  onMounted(async () => {
    const catRes = await api.get<Category[]>('/categories');
    categories.value = catRes.data;
    fetchProducts();
  });

  watch(page, fetchProducts);
  watch(search, () => {
    page.value = 1;
    fetchProducts();
  });
  watch(selectedCategory, () => {
    page.value = 1;
    fetchProducts();
  });

  function onSort(column: string) {
    if (sortBy.value === column) {
      sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
      sortBy.value = column;
      sortDir.value = 'asc';
    }
    page.value = 1;
    fetchProducts();
  }

  function navigateToNew() {
    router.push('/products/new');
  }

  function editProduct(product: Product) {
    router.push(`/products/${product.id}/edit`);
  }

  async function deleteProduct(product: Product) {
    if (!confirm(`Deseja excluir o produto "${product.name}"?`)) return;
    await api.delete(`/products/${product.id}`);
    fetchProducts();
  }

  /**
   * Define a classe e o texto do status de estoque.
   */
  function stockStatusClass(product: Product): string {
    if (product.stock_quantity === 0) return 'text-error-text';
    if (product.stock_quantity <= product.min_stock) return 'text-warning';
    return 'text-primary-text';
  }

  function stockStatusLabel(product: Product): string {
    if (product.stock_quantity === 0) return 'Zerado';
    if (product.stock_quantity <= product.min_stock) return 'Baixo';
    return 'OK';
  }
</script>

<template>
  <PageContainer>
    <PageHeader title="Produtos">
      <template #actions>
        <AppButton size="sm" @click="navigateToNew">
          <Plus :size="16" class="mr-1 inline" />
          Novo Produto
        </AppButton>
      </template>
    </PageHeader>

    <div class="max-w-xs">
      <AppSelect v-model="selectedCategory" :options="categoryOptions" />
    </div>

    <DataTable
      :columns="columns"
      :data="products"
      :total="total"
      :page="page"
      :per-page="perPage"
      :loading="loading"
      :refreshing="refreshing"
      :sort-by="sortBy"
      :sort-dir="sortDir"
      @update:page="page = $event"
      @search="search = $event"
      @sort="onSort"
    >
      <template #cell-sku="{ row }">
        <span class="font-mono">{{ row.sku }}</span>
      </template>
      <template #cell-category="{ row }">
        {{ row.category?.name || '-' }}
      </template>
      <template #cell-sale_price="{ row }">
        <span class="font-mono">{{ formatCurrency(row.sale_price) }}</span>
      </template>
      <template #cell-stock_quantity="{ row }">
        <span class="font-mono">{{ row.stock_quantity }}</span>
      </template>
      <template #cell-status="{ row }">
        <span :class="stockStatusClass(row)">{{ stockStatusLabel(row) }}</span>
      </template>
      <template #cell-actions="{ row }">
        <div class="flex items-center gap-1">
          <button
            class="p-1.5 rounded-input hover:bg-surface-elevated transition-colors cursor-pointer text-secondary hover:text-primary-text"
            title="Editar produto"
            @click="editProduct(row)"
          >
            <Pencil :size="16" />
          </button>
          <button
            class="p-1.5 rounded-input hover:bg-error-bg transition-colors cursor-pointer text-secondary hover:text-error"
            title="Excluir produto"
            @click="deleteProduct(row)"
          >
            <Trash2 :size="16" />
          </button>
        </div>
      </template>
    </DataTable>
  </PageContainer>
</template>
