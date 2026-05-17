<script setup lang="ts">
  /**
   * Formulário compartilhado para criação e edição de clientes.
   *
   * Recebe um cliente opcional via rota. Se a rota contiver `id`,
   * opera em modo edição (PUT); caso contrário, modo criação (POST).
   * Utiliza validação nativa HTML5. Exibe alerta de erro em caso de
   * falha na API e redireciona para a listagem ao concluir.
   */
  import { ref, computed, onMounted } from 'vue';
  import { useRouter, useRoute } from 'vue-router';
  import api from '@/services/api';
  import type { ICustomer } from '@/types';
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
  const title = computed(() => (isEdit.value ? 'Editar Cliente' : 'Novo Cliente'));

  const form = ref({
    name: '',
    email: '',
    phone: '',
    document: '',
  });

  const submitting = ref(false);
  const error = ref('');

  async function loadCustomer() {
    const { data } = await api.get<ICustomer>(`/customers/${route.params.id}`);
    form.value = {
      name: data.name,
      email: data.email ?? '',
      phone: data.phone ?? '',
      document: data.document ?? '',
    };
  }

  onMounted(() => {
    if (isEdit.value) {
      loadCustomer();
    }
  });

  async function handleSubmit() {
    submitting.value = true;
    error.value = '';

    const payload: Record<string, unknown> = {
      name: form.value.name,
      email: form.value.email || null,
      phone: form.value.phone || null,
      document: form.value.document || null,
    };

    try {
      if (isEdit.value) {
        await api.put(`/customers/${route.params.id}`, payload);
        showToast('Cliente atualizado com sucesso.');
      } else {
        await api.post('/customers', payload);
        showToast('Cliente cadastrado com sucesso.');
      }
      router.push('/customers');
    } catch (e: unknown) {
      const err = e as {
        response?: { data?: { message?: string; errors?: Record<string, string[]> } };
      };
      error.value =
        (err.response?.data?.errors &&
          Object.values(err.response.data.errors).flat().join(' • ')) ||
        err.response?.data?.message ||
        'Erro ao salvar cliente.';
    } finally {
      submitting.value = false;
    }
  }

  function goBack() {
    router.push('/customers');
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
        <label class="block text-sm text-secondary mb-1.5" for="name">Nome completo</label>
        <AppInput id="name" v-model="form.name" placeholder="Ex: João da Silva" required />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-secondary mb-1.5" for="email">Email</label>
          <AppInput id="email" v-model="form.email" type="email" placeholder="Ex: joao@email.com" />
        </div>
        <div>
          <label class="block text-sm text-secondary mb-1.5" for="phone">Telefone</label>
          <AppInput id="phone" v-model="form.phone" placeholder="Ex: (11) 99999-9999" />
        </div>
      </div>

      <div>
        <label class="block text-sm text-secondary mb-1.5" for="document">CPF/CNPJ</label>
        <AppInput id="document" v-model="form.document" placeholder="Ex: 123.456.789-00" />
      </div>

      <div class="flex justify-end pt-2">
        <AppButton type="submit" :disabled="submitting">
          <Save :size="16" class="mr-1.5" />
          {{ submitting ? 'Salvando...' : 'Salvar cliente' }}
        </AppButton>
      </div>
    </form>
  </PageContainer>
</template>
