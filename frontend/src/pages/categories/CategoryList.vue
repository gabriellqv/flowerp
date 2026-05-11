<script setup lang="ts">
  /**
   * Listagem de categorias com DataTable, busca e criacao inline.
   *
   * Carrega categorias no mount com busca local (todas ja estao
   * em memoria, pois o endpoint retorna lista completa).
   * Permite criar nova categoria via modal inline com validacao
   * basica de nome duplicado e campo obrigatorio.
   * Exibicao condicional do botao de criar baseado no role do usuario.
   */
  import { ref, onMounted, computed } from 'vue';
  import api from '@/services/api';
  import type { Category } from '@/types';
  import { useAuthStore } from '@/stores/auth';
  import DataTable from '@/components/ui/DataTable.vue';
  import PageContainer from '@/components/ui/PageContainer.vue';
  import PageHeader from '@/components/ui/PageHeader.vue';
  import AppButton from '@/components/ui/AppButton.vue';
  import AppInput from '@/components/ui/AppInput.vue';
  import { Plus, Tag, X } from 'lucide-vue-next';

  const auth = useAuthStore();

  const allCategories = ref<Category[]>([]);
  const categories = ref<Category[]>([]);
  const loading = ref(true);
  const search = ref('');
  const showingCreate = ref(false);
  const newName = ref('');
  const saving = ref(false);
  const error = ref('');

  const canCreate = computed(() => auth.isAdmin || auth.isManager);

  const columns = [{ key: 'name', label: 'Nome' }];

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

  async function handleCreate() {
    error.value = '';
    const name = newName.value.trim();

    if (!name) {
      error.value = 'Nome da categoria e obrigatorio.';
      return;
    }

    if (allCategories.value.some((c) => c.name.toLowerCase() === name.toLowerCase())) {
      error.value = 'Ja existe uma categoria com este nome.';
      return;
    }

    saving.value = true;
    try {
      const { data } = await api.post<Category>('/categories', { name });
      allCategories.value.push(data);
      allCategories.value.sort((a, b) => a.name.localeCompare(b.name));
      categories.value = allCategories.value.filter(
        (c) => !search.value || c.name.toLowerCase().includes(search.value.toLowerCase()),
      );
      newName.value = '';
      showingCreate.value = false;
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } };
      error.value = err?.response?.data?.message ?? 'Erro ao criar categoria.';
    } finally {
      saving.value = false;
    }
  }

  function cancelCreate() {
    newName.value = '';
    error.value = '';
    showingCreate.value = false;
  }
</script>

<template>
  <PageContainer>
    <PageHeader title="Categorias">
      <template v-if="canCreate" #actions>
        <AppButton size="sm" @click="showingCreate = true">
          <Plus :size="16" class="mr-1.5" />
          Nova Categoria
        </AppButton>
      </template>
    </PageHeader>

    <!-- Modal de criacao inline -->
    <div
      v-if="showingCreate"
      class="p-5 rounded-card border border-[var(--color-glass-border)] bg-[var(--color-glass-bg)] backdrop-blur-xl shadow-[0_4px_24px_-8px_rgba(0,0,0,0.1)]"
    >
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-semibold">Nova Categoria</h2>
        <button
          class="p-1 rounded-input hover:bg-surface-elevated transition-colors cursor-pointer text-secondary hover:text-primary-text"
          @click="cancelCreate"
        >
          <X :size="16" />
        </button>
      </div>

      <div v-if="error" class="mb-3 text-xs text-error">{{ error }}</div>

      <div class="flex items-end gap-3">
        <div class="flex-1">
          <label class="block text-xs text-secondary mb-1">Nome</label>
          <AppInput v-model="newName" placeholder="Nome da categoria" @keyup.enter="handleCreate" />
        </div>
        <AppButton :disabled="saving" @click="handleCreate">
          {{ saving ? 'Salvando...' : 'Salvar' }}
        </AppButton>
        <AppButton variant="secondary" @click="cancelCreate">Cancelar</AppButton>
      </div>
    </div>

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
          <AppButton v-if="canCreate" size="sm" @click="showingCreate = true">
            <Plus :size="16" class="mr-1.5" />
            Criar primeira categoria
          </AppButton>
        </div>
      </template>

      <template #cell-name="{ value }">
        <span>{{ value }}</span>
      </template>
    </DataTable>
  </PageContainer>
</template>
