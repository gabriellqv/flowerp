<script setup lang="ts">
  /**
   * Listagem de clientes com DataTable, busca e paginacao.
   *
   * Exibe nome, email, telefone e documento, tratando
   * valores nulos como traco para consistencia visual.
   */

  import { ref, onMounted, watch } from 'vue';
  import api from '@/services/api';
  import type { PaginatedResponse } from '@/types';
  import DataTable from '@/components/ui/DataTable.vue';

  interface Customer {
    id: string;
    name: string;
    email: string;
    phone: string;
    document: string;
  }

  const customers = ref<Customer[]>([]);
  const total = ref(0);
  const page = ref(1);
  const loading = ref(true);
  const search = ref('');

  const columns = [
    { key: 'name', label: 'Nome' },
    { key: 'email', label: 'Email' },
    { key: 'phone', label: 'Telefone' },
    { key: 'document', label: 'CPF/CNPJ' },
  ];

  async function fetchCustomers() {
    loading.value = true;
    const { data } = await api.get<PaginatedResponse<Customer>>('/customers', {
      params: { page: page.value, search: search.value || undefined },
    });
    customers.value = data.data;
    total.value = data.total;
    loading.value = false;
  }

  onMounted(fetchCustomers);
  watch(page, fetchCustomers);
  watch(search, () => {
    page.value = 1;
    fetchCustomers();
  });
</script>

<template>
  <div class="p-8 space-y-6">
    <h1 class="text-2xl font-bold">Clientes</h1>

    <DataTable
      :columns="columns"
      :data="customers"
      :total="total"
      :page="page"
      :per-page="15"
      :loading="loading"
      @update:page="page = $event"
      @search="search = $event"
    >
      <template #cell-email="{ value }">
        {{ value || '-' }}
      </template>
      <template #cell-phone="{ value }">
        {{ value || '-' }}
      </template>
      <template #cell-document="{ value }">
        {{ value || '-' }}
      </template>
    </DataTable>
  </div>
</template>
