<script setup lang="ts">
  /**
   * Listagem de clientes com DataTable, busca e ações completas.
   *
   * Exibe nome, email, telefone e documento com formatação mono.
   * Inclui botão Novo Cliente, edição, exclusão individual,
   * toggle ativo/inativo e exclusão em lote com seleção.
   * Utiliza a interface Customer do types/index.ts para tipagem.
   */
  import { ref, onMounted, watch } from 'vue';
  import { useRouter } from 'vue-router';
  import api from '@/services/api';
  import type { Customer, PaginatedResponse } from '@/types';
  import { useToast } from '@/composables/useToast';
  import DataTable from '@/components/ui/DataTable.vue';
  import PageContainer from '@/components/ui/PageContainer.vue';
  import PageHeader from '@/components/ui/PageHeader.vue';
  import AppButton from '@/components/ui/AppButton.vue';
  import ConfirmModal from '@/components/ui/ConfirmModal.vue';
  import { Plus, Pencil, Trash2, Users, ToggleLeft, ToggleRight } from 'lucide-vue-next';

  const router = useRouter();
  const { showToast } = useToast();

  const customers = ref<Customer[]>([]);
  const total = ref(0);
  const page = ref(1);
  const perPage = ref(15);
  const loading = ref(true);
  const refreshing = ref(false);
  const search = ref('');
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
    { key: 'name', label: 'Nome' },
    { key: 'email', label: 'Email' },
    { key: 'phone', label: 'Telefone' },
    { key: 'document', label: 'CPF/CNPJ' },
    { key: 'is_active', label: 'Ativo' },
    { key: 'actions', label: 'Ações' },
  ];

  async function fetchCustomers() {
    if (abortController) {
      abortController.abort();
    }

    const controller = new AbortController();
    abortController = controller;

    if (customers.value.length > 0) {
      refreshing.value = true;
    } else {
      loading.value = true;
    }

    try {
      const { data } = await api.get<PaginatedResponse<Customer>>('/customers', {
        params: { page: page.value, search: search.value || undefined },
        signal: controller.signal,
      });
      customers.value = data.data;
      total.value = data.total;
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

  onMounted(fetchCustomers);
  watch(page, fetchCustomers);
  watch(perPage, () => {
    page.value = 1;
    fetchCustomers();
  });
  watch(search, () => {
    page.value = 1;
    fetchCustomers();
  });

  function navigateToNew() {
    router.push('/customers/new');
  }

  function editCustomer(customer: Customer) {
    router.push(`/customers/${customer.id}/edit`);
  }

  async function deleteCustomer(customer: Customer) {
    openConfirm(`Deseja excluir o cliente "${customer.name}"?`, async () => {
      await api.patch(`/customers/${customer.id}/toggle-active`);
      showToast('Cliente desativado com sucesso.');
      fetchCustomers();
    });
  }

  async function deleteSelected() {
    const count = selected.value.length;
    openConfirm(`Deseja excluir ${count} cliente(s)?`, async () => {
      await api.post('/customers/bulk-delete', { ids: selected.value });
      showToast(`${count} cliente(s) excluído(s).`);
      selected.value = [];
      fetchCustomers();
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
    if (selected.value.length === customers.value.length) {
      selected.value = [];
    } else {
      selected.value = customers.value.map((c) => c.id);
    }
  }

  async function toggleActive(customer: Customer) {
    await api.patch(`/customers/${customer.id}/toggle-active`);
    customer.is_active = !customer.is_active;
  }
</script>

<template>
  <PageContainer>
    <PageHeader title="Clientes">
      <template #actions>
        <AppButton size="sm" @click="navigateToNew">
          <Plus :size="16" class="mr-1.5" />
          Novo Cliente
        </AppButton>
      </template>
    </PageHeader>

    <DataTable
      :columns="columns"
      :data="customers"
      :total="total"
      :page="page"
      :per-page="perPage"
      :loading="loading"
      :refreshing="refreshing"
      :selected="selected"
      @update:page="page = $event"
      @update:per-page="perPage = $event"
      @search="search = $event"
      @toggle-select="toggleSelect"
      @toggle-select-all="toggleSelectAll"
    >
      <template #empty>
        <div class="flex flex-col items-center justify-center gap-3">
          <Users :size="40" class="text-tertiary opacity-30" />
          <p class="text-tertiary text-sm">Nenhum cliente encontrado.</p>
          <AppButton size="sm" @click="navigateToNew">
            <Plus :size="16" class="mr-1.5" />
            Cadastrar primeiro cliente
          </AppButton>
        </div>
      </template>

      <template #cell-email="{ value }">
        {{ value || '-' }}
      </template>
      <template #cell-phone="{ value }">
        <span class="font-mono">{{ value || '-' }}</span>
      </template>
      <template #cell-document="{ value }">
        <span class="font-mono">{{ value || '-' }}</span>
      </template>
      <template #cell-is_active="{ row }">
        <button
          class="cursor-pointer text-secondary hover:text-primary-text transition-colors"
          :title="row.is_active ? 'Desativar cliente' : 'Ativar cliente'"
          @click="toggleActive(row)"
        >
          <ToggleRight v-if="row.is_active" :size="20" class="text-primary-text" />
          <ToggleLeft v-else :size="20" class="text-tertiary" />
        </button>
      </template>
      <template #cell-actions="{ row }">
        <div class="flex items-center gap-1">
          <button
            class="p-1.5 rounded-input hover:bg-surface-elevated transition-colors cursor-pointer text-secondary hover:text-primary-text"
            title="Editar cliente"
            @click="editCustomer(row)"
          >
            <Pencil :size="16" />
          </button>
          <button
            class="p-1.5 rounded-input hover:bg-error-bg transition-colors cursor-pointer text-secondary hover:text-error"
            title="Excluir cliente"
            @click="deleteCustomer(row)"
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
