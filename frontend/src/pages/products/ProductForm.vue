<script setup lang="ts">
  /**
   * Formulário compartilhado para criação e edição de produtos.
   *
   * Recebe um produto opcional via prop. Se fornecido, opera em modo
   * edição (PUT); caso contrário, modo criação (POST).
   * Utiliza validação nativa HTML5. Exibe alerta de erro em caso de
   * falha na API e redireciona para a listagem ao concluir.
   */
  import { ref, onMounted, computed } from 'vue';
  import { useRouter, useRoute } from 'vue-router';
  import api from '@/services/api';
  import type { Product, Category } from '@/types';
  import { useToast } from '@/composables/useToast';
  import PageContainer from '@/components/ui/PageContainer.vue';
  import PageHeader from '@/components/ui/PageHeader.vue';
  import AppInput from '@/components/ui/AppInput.vue';
  import AppButton from '@/components/ui/AppButton.vue';
  import AppSelect from '@/components/ui/AppSelect.vue';
  import AlertBox from '@/components/ui/AlertBox.vue';
  import { Save, ArrowLeft } from 'lucide-vue-next';

  const router = useRouter();
  const route = useRoute();
  const { showToast } = useToast();

  const isEdit = computed(() => !!route.params.id);
  const title = computed(() => (isEdit.value ? 'Editar Produto' : 'Novo Produto'));

  const categories = ref<Category[]>([]);

  interface SelectOption {
    label: string;
    value: string;
  }

  const categoryOptions = computed<SelectOption[]>(() =>
    categories.value.map((c) => ({ label: c.name, value: c.id })),
  );

  const form = ref({
    name: '',
    sku: '',
    description: '',
    category_id: '',
    cost_price: '0',
    sale_price: '0',
    stock_quantity: '0',
    min_stock: '5',
  });

  const submitting = ref(false);
  const error = ref('');

  async function loadProduct() {
    const { data } = await api.get<Product>(`/products/${route.params.id}`);
    form.value = {
      name: data.name,
      sku: data.sku,
      description: (data as unknown as Record<string, string>).description ?? '',
      category_id: data.category_id,
      cost_price: String(data.cost_price),
      sale_price: String(data.sale_price),
      stock_quantity: String(data.stock_quantity),
      min_stock: String(data.min_stock),
    };
  }

  onMounted(async () => {
    const catRes = await api.get<Category[]>('/categories');
    categories.value = catRes.data;

    if (isEdit.value) {
      await loadProduct();
    }
  });

  async function handleSubmit() {
    submitting.value = true;
    error.value = '';

    const payload = {
      name: form.value.name,
      sku: form.value.sku,
      description: form.value.description || null,
      category_id: form.value.category_id,
      cost_price: Number(form.value.cost_price),
      sale_price: Number(form.value.sale_price),
      stock_quantity: Number(form.value.stock_quantity),
      min_stock: Number(form.value.min_stock),
    };

    try {
      if (isEdit.value) {
        await api.put(`/products/${route.params.id}`, payload);
        showToast('Produto atualizado com sucesso.');
      } else {
        await api.post('/products', payload);
        showToast('Produto criado com sucesso.');
      }
      router.push('/products');
    } catch (e: unknown) {
      const err = e as {
        response?: { data?: { message?: string; errors?: Record<string, string[]> } };
      };
      error.value =
        (err.response?.data?.errors && Object.values(err.response.data.errors).flat().join(' • ')) ||
        err.response?.data?.message ||
        'Erro ao salvar produto.';
    } finally {
      submitting.value = false;
    }
  }

  function goBack() {
    router.push('/products');
  }
</script>

<template>
  <PageContainer>
    <PageHeader :title="title">
      <template #actions>
        <AppButton size="sm" variant="secondary" @click="goBack">
          <ArrowLeft :size="16" class="mr-1.5" />
          Voltar
        </AppButton>
      </template>
    </PageHeader>

    <AlertBox v-if="error" class="mb-4">{{ error }}</AlertBox>

    <form
      class="max-w-2xl bg-[var(--color-glass-bg)] backdrop-blur-xl border border-[var(--color-glass-border)] rounded-card p-card shadow-[0_4px_24px_-8px_rgba(0,0,0,0.1)] space-y-5"
      novalidate
      @submit.prevent="handleSubmit"
    >
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-secondary mb-1.5" for="name">Nome do produto</label>
          <AppInput id="name" v-model="form.name" placeholder="Ex: Arroz Integral 5kg" required />
        </div>
        <div>
          <label class="block text-sm text-secondary mb-1.5" for="sku">SKU</label>
          <AppInput
            id="sku"
            v-model="form.sku"
            placeholder="Ex: ARZ-001"
            :disabled="isEdit"
            required
          />
        </div>
      </div>

      <div>
        <label class="block text-sm text-secondary mb-1.5" for="category">Categoria</label>
        <AppSelect
          id="category"
          v-model="form.category_id"
          :options="categoryOptions"
          placeholder="Selecionar categoria"
          required
        />
      </div>

      <div>
        <label class="block text-sm text-secondary mb-1.5" for="description">Descrição</label>
        <div class="relative w-full rounded-input group">
          <textarea
            id="description"
            v-model="form.description"
            rows="10"
            class="w-full px-4 py-2 rounded-input bg-[var(--color-glass-bg)] backdrop-blur-md border border-[var(--color-glass-border)] text-[var(--color-text-primary)] placeholder:text-tertiary focus:border-primary focus:ring-0 outline-none text-sm transition-all resize-none shadow-sm relative z-10 hover:border-[var(--color-glass-shine)]"
            placeholder="Descrição opcional do produto"
          ></textarea>
          <div
            class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-[var(--color-glass-shine)] to-transparent opacity-50 z-20 pointer-events-none rounded-t-input"
          ></div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-secondary mb-1.5" for="cost_price">Preço de custo</label>
          <AppInput
            id="cost_price"
            v-model="form.cost_price"
            type="number"
            step="0.01"
            min="0"
            placeholder="0,00"
            required
          />
        </div>
        <div>
          <label class="block text-sm text-secondary mb-1.5" for="sale_price">Preço de venda</label>
          <AppInput
            id="sale_price"
            v-model="form.sale_price"
            type="number"
            step="0.01"
            min="0"
            placeholder="0,00"
            required
          />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-secondary mb-1.5" for="stock_quantity">
            Quantidade em estoque
          </label>
          <AppInput
            id="stock_quantity"
            v-model="form.stock_quantity"
            type="number"
            min="0"
            placeholder="0"
            required
          />
        </div>
        <div>
          <label class="block text-sm text-secondary mb-1.5" for="min_stock">Estoque mínimo</label>
          <AppInput
            id="min_stock"
            v-model="form.min_stock"
            type="number"
            min="1"
            placeholder="5"
            required
          />
        </div>
      </div>

      <div class="flex justify-end pt-2">
        <AppButton type="submit" :disabled="submitting">
          <Save :size="16" class="mr-1.5" />
          {{ submitting ? 'Salvando...' : 'Salvar produto' }}
        </AppButton>
      </div>
    </form>
  </PageContainer>
</template>
