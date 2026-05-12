<script setup lang="ts">
  /**
   * Listagem de categorias com DataTable, busca e acoes completas.
   *
   * Exibe categorias em ordem alfabetica com busca local.
   * Inclui botao Nova Categoria (redireciona para o form),
   * edicao via form dedicado, e exclusao com confirmacao.
   * Exibicao condicional dos botoes baseada no role do usuario.
   */
  import { ref, onMounted, computed } from 'vue';
  import { useRouter } from 'vue-router';
  import api from '@/services/api';
  import type { Category } from '@/types';
  import { useAuthStore } from '@/stores/auth';
  import { useToast } from '@/composables/useToast';
  import DataTable from '@/components/ui/DataTable.vue';
  import PageContainer from '@/components/ui/PageContainer.vue';
  import PageHeader from '@/components/ui/PageHeader.vue';
  import AppButton from '@/components/ui/AppButton.vue';
  import ConfirmModal from '@/components/ui/ConfirmModal.vue';
  import { Plus, Tag, Pencil, Trash2 } from 'lucide-vue-next';

  const router = useRouter();
  const auth = useAuthStore();
  const { showToast } = useToast();

  const allCategories = ref<Category[]>([]);
  const categories = ref<Category[]>([]);
  const loading = ref(true);
  const search = ref('');
  const confirmVisible = ref(false);
  const confirmMessage = ref('');
  let confirmCallback: (() => void) | null = null;

  const canManage = computed(() => auth.isAdmin || auth.isManager);

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
    { key: 'actions', label: 'Ações' },
  ];

  onMounted(async () => {
    const { data } = await api.get<Category[]>('/categories');
    allCategories.value = data;
    categories.value = data;
    loading.value = false;
  });

  /**
   * Filtra localmente as categorias ja carregadas.
   */
  function onSearch(query: string) {
    search.value = query;
    const term = query.toLowerCase().trim();
    if (!term) {
      categories.value = allCategories.value;
    } else {
      categories.value = allCategories.value.filter((c) => c.name.toLowerCase().includes(term));
    }
  }

  function navigateToNew() {
    router.push('/categories/new');
  }

  function editCategory(category: Category) {
    router.push(`/categories/${category.id}/edit`);
  }

  async function deleteCategory(category: Category) {
    openConfirm(`Deseja excluir a categoria "${category.name}"?`, async () => {
      try {
        await api.delete(`/categories/${category.id}`);
        allCategories.value = allCategories.value.filter((c) => c.id !== category.id);
        categories.value = categories.value.filter((c) => c.id !== category.id);
        showToast(`Categoria "${category.name}" excluida.`);
      } catch (e: unknown) {
        const err = e as { response?: { data?: { message?: string } } };
        showToast(err?.response?.data?.message ?? 'Erro ao excluir categoria.', 'error');
      }
    });
  }
</script>

<template>
  <PageContainer>
    <PageHeader title="Categorias">
      <template v-if="canManage" #actions>
        <AppButton size="sm" @click="navigateToNew">
          <Plus :size="16" class="mr-1.5" />
          Nova Categoria
        </AppButton>
      </template>
    </PageHeader>

    <DataTable
      :columns="columns"
      :data="categories"
      :total="categories.length"
      :page="1"
      :per-page="categories.length"
      :loading="loading"
      @search="onSearch"
    >
      <template #empty>
        <div class="flex flex-col items-center justify-center gap-3">
          <Tag :size="40" class="text-tertiary opacity-30" />
          <p class="text-tertiary text-sm">Nenhuma categoria encontrada.</p>
          <AppButton v-if="canManage" size="sm" @click="navigateToNew">
            <Plus :size="16" class="mr-1.5" />
            Criar primeira categoria
          </AppButton>
        </div>
      </template>

      <template #cell-name="{ value }">
        <span>{{ value }}</span>
      </template>

      <template v-if="canManage" #cell-actions="{ row }">
        <div class="flex items-center gap-1">
          <button
            class="p-1.5 rounded-input hover:bg-surface-elevated transition-colors cursor-pointer text-secondary hover:text-primary-text"
            title="Editar categoria"
            @click="editCategory(row)"
          >
            <Pencil :size="16" />
          </button>
          <button
            class="p-1.5 rounded-input hover:bg-error-bg transition-colors cursor-pointer text-secondary hover:text-error"
            title="Excluir categoria"
            @click="deleteCategory(row)"
          >
            <Trash2 :size="16" />
          </button>
        </div>
      </template>
    </DataTable>

    <ConfirmModal
      :visible="confirmVisible"
      title="Confirmar exclusao"
      :message="confirmMessage"
      confirm-text="Excluir"
      @confirm="onConfirm"
      @cancel="onCancel"
    />
  </PageContainer>
</template>
