<script setup lang="ts">
  /**
   * Tela de login com validacao integrada a store de autenticacao.
   *
   * Utiliza credenciais pre-preenchidas para facilitar testes
   * em desenvolvimento. Renderiza erro inline em caso de falha.
   */
  import { ref } from 'vue';
  import { useAuthStore } from '@/stores/auth';
  import { useRouter } from 'vue-router';
  import AppInput from '@/components/ui/AppInput.vue';
  import AppButton from '@/components/ui/AppButton.vue';
  import AlertBox from '@/components/ui/AlertBox.vue';

  const auth = useAuthStore();
  const router = useRouter();

  const email = ref('admin@flowerp.com');
  const password = ref('senha123');
  const error = ref('');
  const loading = ref(false);

  async function handleLogin() {
    error.value = '';
    loading.value = true;

    try {
      await auth.login(email.value, password.value);
      router.push('/');
    } catch (e: unknown) {
      const err = e as {
        response?: { data?: { message?: string; errors?: { email?: string[] } } };
      };
      error.value =
        (err.response?.data?.errors && Object.values(err.response.data.errors).flat().join(' • ')) ||
        err.response?.data?.message ||
        'Erro ao fazer login.';
    } finally {
      loading.value = false;
    }
  }
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-surface">
    <form
      class="w-full max-w-sm bg-surface-secondary rounded-card border border p-8 space-y-6"
      novalidate
      @submit.prevent="handleLogin"
    >
      <div class="flex flex-col items-center gap-3">
        <img src="/favicon.png" alt="FlowERP Logo" class="w-16 h-16" />
        <h1 class="text-heading font-semibold text-center text-primary-text">FlowERP</h1>
      </div>

      <AlertBox v-if="error">{{ error }}</AlertBox>

      <div>
        <label class="block text-sm text-secondary mb-1">Email</label>
        <AppInput v-model="email" type="email" required />
      </div>

      <div>
        <label class="block text-sm text-secondary mb-1">Senha</label>
        <AppInput v-model="password" type="password" required />
      </div>

      <AppButton type="submit" class="w-full" :disabled="loading">
        {{ loading ? 'Entrando...' : 'Entrar' }}
      </AppButton>

      <p class="text-xs text-disabled text-center">admin@flowerp.com / senha123</p>
    </form>
  </div>
</template>
