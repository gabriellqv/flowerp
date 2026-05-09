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
        err.response?.data?.message ||
        err.response?.data?.errors?.email?.[0] ||
        'Erro ao fazer login.';
    } finally {
      loading.value = false;
    }
  }
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-zinc-950">
    <form
      class="w-full max-w-sm bg-zinc-900 rounded-xl border border-zinc-800 p-8 space-y-6"
      @submit.prevent="handleLogin"
    >
      <h1 class="text-2xl font-bold text-center text-emerald-400">FlowERP</h1>

      <div
        v-if="error"
        class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-lg p-3"
      >
        {{ error }}
      </div>

      <div>
        <label class="block text-sm text-zinc-400 mb-1">Email</label>
        <input
          v-model="email"
          class="w-full px-4 py-2 rounded-lg bg-zinc-800 border border-zinc-700 text-zinc-100 focus:ring-2 focus:ring-emerald-500 outline-none"
          type="email"
          required
        />
      </div>

      <div>
        <label class="block text-sm text-zinc-400 mb-1">Senha</label>
        <input
          v-model="password"
          class="w-full px-4 py-2 rounded-lg bg-zinc-800 border border-zinc-700 text-zinc-100 focus:ring-2 focus:ring-emerald-500 outline-none"
          type="password"
          required
        />
      </div>

      <button
        class="w-full py-2 bg-emerald-600 hover:bg-emerald-500 rounded-lg font-medium transition-colors disabled:opacity-50"
        type="submit"
        :disabled="loading"
      >
        {{ loading ? 'Entrando...' : 'Entrar' }}
      </button>

      <p class="text-xs text-zinc-600 text-center">admin@flowerp.com / senha123</p>
    </form>
  </div>
</template>
