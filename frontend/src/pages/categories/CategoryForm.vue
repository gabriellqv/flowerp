<script setup lang="ts">
  /**
   * Formulario compartilhado para criacao e edicao de categorias.
   *
   * Recebe uma categoria opcional via rota. Se a rota contiver `id`,
   * opera em modo edicao (PUT); caso contrario, modo criacao (POST).
   * Utiliza validacao nativa HTML5. Exibe alerta de erro em caso de
   * falha na API e redireciona para a listagem ao concluir.
   */
  import { ref, computed, onMounted } from 'vue';
  import { useRouter, useRoute } from 'vue-router';
  import api from '@/services/api';
  import type { Category } from '@/types';
  import { useToast } from '@/composables/useToast';
  import PageContainer from '@/components/ui/PageContainer.vue';
  import PageHeader from '@/components/ui/PageHeader.vue';
  import AppInput from '@/components/ui/AppInput.vue';
  import AppButton from '@/components/ui/AppButton.vue';
  import AlertBox from '@/components/ui/AlertBox.vue';
  import { Save, ArrowLeft } from 'lucide-vue-next';

  const router = useRouter();
  const route = useRoute();
  const { showToast } = useToast();

  const isEdit = computed(() => !!route.params.id);
  const title = computed(() => (isEdit.value ? 'Editar Categoria' : 'Nova Categoria'));

  const form = ref({
    name: '',
  });

  const submitting = ref(false);
  const error = ref('');

  async function loadCategory() {
    const { data } = await api.get<Category>(`/categories/${route.params.id}`);
    form.value = { name: data.name };
  }

  onMounted(() => {
    if (isEdit.value) {
      loadCategory();
    }
  });

  async function handleSubmit() {
    submitting.value = true;
    error.value = '';

    try {
      if (isEdit.value) {
        await api.put(`/categories/${route.params.id}`, { name: form.value.name });
        showToast('Categoria atualizada com sucesso.');
      } else {
        await api.post('/categories', { name: form.value.name });
        showToast('Categoria criada com sucesso.');
      }
      router.push('/categories');
    } catch (e: unknown) {
      const err = e as {
        response?: { data?: { message?: string; errors?: Record<string, string[]> } };
      };
      error.value =
        (err.response?.data?.errors && Object.values(err.response.data.errors).flat().join(' • ')) ||
        err.response?.data?.message ||
        'Erro ao salvar categoria.';
    } finally {
      submitting.value = false;
    }
  }

  function goBack() {
    router.push('/categories');
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
      <div>
        <label class="block text-sm text-secondary mb-1.5" for="name">Nome da categoria</label>
        <AppInput id="name" v-model="form.name" placeholder="Ex: Bebidas" required />
      </div>

      <div class="flex justify-end pt-2">
        <AppButton type="submit" :disabled="submitting">
          <Save :size="16" class="mr-1.5" />
          {{ submitting ? 'Salvando...' : 'Salvar categoria' }}
        </AppButton>
      </div>
    </form>
  </PageContainer>
</template>
