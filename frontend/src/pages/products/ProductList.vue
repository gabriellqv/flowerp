<script setup lang="ts">
  /**
   * Listagem de produtos com DataTable, busca e filtros.
   *
   * Carrega categorias no mount para exibicao dos nomes
   * em vez de UUIDs. A busca reseta a pagina para 1.
   */
  import { ref, onMounted, watch } from 'vue';
  import api from '@/services/api';
  import type { Product, PaginatedResponse, Category } from '@/types';
  import DataTable from '@/components/ui/DataTable.vue';
  import PageContainer from '@/components/ui/PageContainer.vue';
  import PageHeader from '@/components/ui/PageHeader.vue';

  const products = ref<Product[]>([]);
  const total = ref(0);
  const page = ref(1);
  const perPage = ref(15);
  const loading = ref(true);
  const search = ref('');
  const categories = ref<Category[]>([]);

  const columns = [
    { key: 'sku', label: 'SKU', sortable: true },
    { key: 'name', label: 'Nome' },
    { key: 'category', label: 'Categoria' },
    { key: 'sale_price', label: 'Preco' },
    { key: 'stock_quantity', label: 'Estoque' },
    { key: 'status', label: 'Status' },
  ];

  async function fetchProducts() {
    loading.value = true;
    const { data } = await api.get<PaginatedResponse<Product>>('/products', {
      params: { page: page.value, per_page: perPage.value, search: search.value || undefined },
    });
    products.value = data.data;
    total.value = data.total;
    loading.value = false;
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
</script>

<template>
  <PageContainer>
    <PageHeader title="Produtos" />

    <DataTable
      :columns="columns"
      :data="products"
      :total="total"
      :page="page"
      :per-page="perPage"
      :loading="loading"
      @update:page="page = $event"
      @search="search = $event"
    >
      <template #cell-category="{ row }">
        {{ row.category?.name || '-' }}
      </template>
      <template #cell-sale_price="{ row }">R$ {{ Number(row.sale_price).toFixed(2) }}</template>
      <template #cell-status="{ row }">
        <span
          :class="
            row.stock_quantity <= row.min_stock
              ? 'text-warning'
              : row.stock_quantity === 0
                ? 'text-error-text'
                : 'text-primary-text'
          "
        >
          {{
            row.stock_quantity <= row.min_stock
              ? 'Baixo'
              : row.stock_quantity === 0
                ? 'Zerado'
                : 'OK'
          }}
        </span>
      </template>
    </DataTable>
  </PageContainer>
</template>
