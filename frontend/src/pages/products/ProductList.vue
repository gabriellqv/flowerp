<script setup lang="ts">
  /**
   * Listagem de produtos com DataTable, busca, ordenação e filtros.
   *
   * Carrega categorias no mount para exibição dos nomes
   * em vez de UUIDs e popula o filtro por categoria.
   * A busca reseta a página para 1.
   * Suporte a ordenação via cabeçalho da tabela com direção
   * alternada (asc/desc) e ícones indicativos.
   * Inclui seleção em massa, badges de status, toggle ativo/inativo
   * e seletor de itens por página.
   */
  import { ref, onMounted, watch, computed } from 'vue';
  import { useRouter } from 'vue-router';
  import api from '@/services/api';
  import type { Product, PaginatedResponse, Category } from '@/types';
  import { formatCurrency } from '@/utils/format';
  import { useToast } from '@/composables/useToast';
  import DataTable from '@/components/ui/DataTable.vue';
  import PageContainer from '@/components/ui/PageContainer.vue';
  import PageHeader from '@/components/ui/PageHeader.vue';
  import AppButton from '@/components/ui/AppButton.vue';
  import AppSelect from '@/components/ui/AppSelect.vue';
  import ConfirmModal from '@/components/ui/ConfirmModal.vue';
  import { Plus, Pencil, Trash2, Package, ToggleLeft, ToggleRight } from 'lucide-vue-next';

  const router = useRouter();
  const { showToast } = useToast();

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
  const selected = ref<string[]>([]);
  const confirmVisible = ref(false);
  const confirmMessage = ref('');
  let confirmCallback: (() => void) | null = null;
  let abortController: AbortController | null = null;

  function openConfirm(message: string, callback: () => void) {
    confirmMessage.value = message;
    confirmCallback = callback;
    confirmVisible.value = true;
  }

  function onConfirm() {
    confirmVisible.value = false;
    if (confirmCallback) confirmCallback();
    confirmCallback = null;
  }

  function onCancel() {
    confirmVisible.value = false;
    confirmCallback = null;
  }

  const columns = [
    { key: 'sku', label: 'SKU', sortable: true },
    { key: 'name', label: 'Nome', sortable: true },
    { key: 'category', label: 'Categoria' },
    { key: 'sale_price', label: 'Preço', sortable: true },
    { key: 'stock_quantity', label: 'Estoque', sortable: true },
    { key: 'status', label: 'Status' },
    { key: 'is_active', label: 'Ativo' },
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
      total.value = data.meta?.total || 0;
      selected.value = [];
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
  watch(perPage, () => {
    page.value = 1;
    fetchProducts();
  });
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
    openConfirm(`Deseja excluir o produto "${product.name}"?`, async () => {
      await api.delete(`/products/${product.id}`);
      showToast('Produto excluído com sucesso.');
      fetchProducts();
    });
  }

  async function deleteSelected() {
    const count = selected.value.length;
    openConfirm(`Deseja excluir ${count} produto(s)?`, async () => {
      await api.post('/products/bulk-delete', { ids: selected.value });
      showToast(`${count} produto(s) excluído(s).`);
      selected.value = [];
      fetchProducts();
    });
  }

  function toggleSelect(id: string) {
    const idx = selected.value.indexOf(id);
    if (idx === -1) {
      selected.value.push(id);
    } else {
      selected.value.splice(idx, 1);
    }
  }

  function toggleSelectAll() {
    if (selected.value.length === products.value.length) {
      selected.value = [];
    } else {
      selected.value = products.value.map((p) => p.id);
    }
  }

  async function toggleActive(product: Product) {
    await api.patch(`/products/${product.id}/toggle-active`);
    product.is_active = !product.is_active;
    showToast(product.is_active ? 'Produto ativado.' : 'Produto desativado.', 'info');
  }

  /**
   * Retorna as classes CSS da badge de status de estoque.
   */
  function badgeClass(product: Product): string {
    if (product.stock_quantity === 0) {
      return 'bg-error/10 text-error border-error/20';
    }
    if (product.stock_quantity <= product.min_stock) {
      return 'bg-warning/10 text-warning border-warning/20';
    }
    return 'bg-primary/10 text-primary-text border-primary/20';
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
          <Plus :size="16" class="mr-1.5" />
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
      :selected="selected"
      @update:page="page = $event"
      @update:per-page="perPage = $event"
      @search="search = $event"
      @sort="onSort"
      @toggle-select="toggleSelect"
      @toggle-select-all="toggleSelectAll"
    >
      <template #empty>
        <div class="flex flex-col items-center justify-center gap-3">
          <Package :size="40" class="text-tertiary opacity-30" />
          <p class="text-tertiary text-sm">Nenhum produto encontrado.</p>
          <AppButton size="sm" @click="navigateToNew">
            <Plus :size="16" class="mr-1.5" />
            Cadastrar primeiro produto
          </AppButton>
        </div>
      </template>

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
        <span
          class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium border"
          :class="badgeClass(row)"
        >
          {{ stockStatusLabel(row) }}
        </span>
      </template>
      <template #cell-is_active="{ row }">
        <button
          class="cursor-pointer transition-colors"
          :title="row.is_active ? 'Desativar produto' : 'Ativar produto'"
          @click="toggleActive(row)"
        >
          <ToggleRight
            v-if="row.is_active"
            :size="20"
            class="text-primary-text hover:text-primary transition-colors"
          />
          <ToggleLeft
            v-else
            :size="20"
            class="text-tertiary hover:text-secondary transition-colors"
          />
        </button>
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

      <template #bulk-actions>
        <AppButton size="sm" variant="danger" @click="deleteSelected">
          <Trash2 :size="14" class="mr-1 inline" />
          Excluir selecionados ({{ selected.length }})
        </AppButton>
      </template>
    </DataTable>

    <ConfirmModal
      :visible="confirmVisible"
      title="Confirmar exclusão"
      :message="confirmMessage"
      confirm-text="Excluir"
      @confirm="onConfirm"
      @cancel="onCancel"
    />
  </PageContainer>
</template>
