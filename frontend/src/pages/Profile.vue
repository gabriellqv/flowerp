<script setup lang="ts">
  /**
   * Página de perfil do usuário logado.
   *
   * Permite visualizar e editar dados pessoais (nome, email)
   * e alterar a senha com confirmação da senha atual.
   * Atualiza o state global do auth store ao salvar o perfil.
   */
  import { ref, watch } from 'vue';
  import api from '@/services/api';
  import { useAuthStore } from '@/stores/auth';
  import { useToast } from '@/composables/useToast';
  import type { IUser } from '@/types';
  import PageContainer from '@/components/ui/PageContainer.vue';
  import PageHeader from '@/components/ui/PageHeader.vue';
  import AppInput from '@/components/ui/AppInput.vue';
  import AppButton from '@/components/ui/AppButton.vue';
  import AlertBox from '@/components/ui/AlertBox.vue';
  import { Save, Lock, User as UserIcon, Mail, Shield } from 'lucide-vue-next';

  const auth = useAuthStore();
  const { showToast } = useToast();

  /** Mapa de roles para labels legíveis. */
  const roleLabels: Record<string, string> = {
    admin: 'Administrador',
    manager: 'Gerente',
    seller: 'Vendedor',
    viewer: 'Visualizador',
  };

  // Formulário de dados pessoais
  const profile = ref({
    name: '',
    email: '',
  });
  const profileSubmitting = ref(false);
  const profileError = ref('');

  // Formulário de senha
  const passwordForm = ref({
    current_password: '',
    password: '',
    password_confirmation: '',
  });
  const passwordSubmitting = ref(false);
  const passwordError = ref('');

  /**
   * Observa o user do auth store para popular o formulário.
   * Usa immediate para cobrir quando o user já está disponível no mount.
   */
  watch(
    () => auth.user,
    (user) => {
      if (user) {
        profile.value.name = user.name;
        profile.value.email = user.email;
      }
    },
    { immediate: true },
  );

  /**
   * Salva alterações no perfil (nome e email).
   */
  async function handleProfileSubmit() {
    profileSubmitting.value = true;
    profileError.value = '';

    try {
      const { data } = await api.put<IUser>('/auth/profile', profile.value);
      auth.user = data;
      showToast('Perfil atualizado com sucesso.');
    } catch (e: unknown) {
      const err = e as {
        response?: { data?: { message?: string; errors?: Record<string, string[]> } };
      };
      profileError.value =
        (err.response?.data?.errors &&
          Object.values(err.response.data.errors).flat().join(' • ')) ||
        err.response?.data?.message ||
        'Erro ao atualizar perfil.';
    } finally {
      profileSubmitting.value = false;
    }
  }

  /**
   * Altera a senha do usuário.
   */
  async function handlePasswordSubmit() {
    passwordSubmitting.value = true;
    passwordError.value = '';

    try {
      await api.put('/auth/password', passwordForm.value);
      showToast('Senha alterada com sucesso.');
      passwordForm.value = {
        current_password: '',
        password: '',
        password_confirmation: '',
      };
    } catch (e: unknown) {
      const err = e as {
        response?: { data?: { message?: string; errors?: Record<string, string[]> } };
      };
      passwordError.value =
        (err.response?.data?.errors &&
          Object.values(err.response.data.errors).flat().join(' • ')) ||
        err.response?.data?.message ||
        'Erro ao alterar senha.';
    } finally {
      passwordSubmitting.value = false;
    }
  }
</script>

<template>
  <PageContainer>
    <PageHeader title="Meu Perfil" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Card informativo (lateral) -->
      <div
        class="bg-[var(--color-glass-bg)] backdrop-blur-xl border border-[var(--color-glass-border)] rounded-card p-card shadow-[0_4px_24px_-8px_rgba(0,0,0,0.1)] relative overflow-hidden flex flex-col items-center gap-4 h-fit"
      >
        <!-- Reflexo de vidro -->
        <div
          class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-[var(--color-glass-shine)] to-transparent"
        ></div>

        <!-- Avatar -->
        <div
          class="w-20 h-20 rounded-full bg-primary/15 flex items-center justify-center text-2xl font-bold text-primary-text"
        >
          {{ auth.user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
        </div>

        <div class="text-center">
          <h3 class="font-semibold text-lg">{{ auth.user?.name }}</h3>
          <p class="text-sm text-secondary flex items-center justify-center gap-1.5 mt-1">
            <Mail :size="14" />
            {{ auth.user?.email }}
          </p>
        </div>

        <div
          class="w-full flex items-center gap-2 px-3 py-2 rounded-input bg-surface-secondary text-sm"
        >
          <Shield :size="16" class="text-primary-text shrink-0" />
          <span class="text-secondary">Perfil:</span>
          <span class="font-medium text-primary-text">
            {{ roleLabels[auth.user?.role ?? ''] ?? auth.user?.role }}
          </span>
        </div>
      </div>

      <!-- Formulários -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Dados pessoais -->
        <div
          class="bg-[var(--color-glass-bg)] backdrop-blur-xl border border-[var(--color-glass-border)] rounded-card p-card shadow-[0_4px_24px_-8px_rgba(0,0,0,0.1)] relative overflow-hidden"
        >
          <div
            class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-[var(--color-glass-shine)] to-transparent"
          ></div>

          <h3 class="font-semibold text-base mb-4 flex items-center gap-2">
            <UserIcon :size="18" class="text-primary-text" />
            Dados Pessoais
          </h3>

          <AlertBox v-if="profileError" class="mb-4">{{ profileError }}</AlertBox>

          <form class="space-y-4" novalidate @submit.prevent="handleProfileSubmit">
            <div>
              <label class="block text-sm text-secondary mb-1.5" for="profile-name">Nome</label>
              <AppInput
                id="profile-name"
                v-model="profile.name"
                placeholder="Seu nome completo"
                required
              />
            </div>

            <div>
              <label class="block text-sm text-secondary mb-1.5" for="profile-email">Email</label>
              <AppInput
                id="profile-email"
                v-model="profile.email"
                type="email"
                placeholder="seu@email.com"
                required
              />
            </div>

            <div class="flex justify-end pt-2">
              <AppButton type="submit" :disabled="profileSubmitting">
                <Save :size="16" class="mr-1.5" />
                {{ profileSubmitting ? 'Salvando...' : 'Salvar alterações' }}
              </AppButton>
            </div>
          </form>
        </div>

        <!-- Alterar senha -->
        <div
          class="bg-[var(--color-glass-bg)] backdrop-blur-xl border border-[var(--color-glass-border)] rounded-card p-card shadow-[0_4px_24px_-8px_rgba(0,0,0,0.1)] relative overflow-hidden"
        >
          <div
            class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-[var(--color-glass-shine)] to-transparent"
          ></div>

          <h3 class="font-semibold text-base mb-4 flex items-center gap-2">
            <Lock :size="18" class="text-primary-text" />
            Alterar Senha
          </h3>

          <AlertBox v-if="passwordError" class="mb-4">{{ passwordError }}</AlertBox>

          <form class="space-y-4" novalidate @submit.prevent="handlePasswordSubmit">
            <div>
              <label class="block text-sm text-secondary mb-1.5" for="current-password">
                Senha atual
              </label>
              <AppInput
                id="current-password"
                v-model="passwordForm.current_password"
                type="password"
                placeholder="Digite sua senha atual"
                required
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm text-secondary mb-1.5" for="new-password">
                  Nova senha
                </label>
                <AppInput
                  id="new-password"
                  v-model="passwordForm.password"
                  type="password"
                  placeholder="Mínimo 8 caracteres"
                  required
                />
              </div>
              <div>
                <label class="block text-sm text-secondary mb-1.5" for="confirm-password">
                  Confirmar nova senha
                </label>
                <AppInput
                  id="confirm-password"
                  v-model="passwordForm.password_confirmation"
                  type="password"
                  placeholder="Repita a nova senha"
                  required
                />
              </div>
            </div>

            <div class="flex justify-end pt-2">
              <AppButton type="submit" :disabled="passwordSubmitting">
                <Lock :size="16" class="mr-1.5" />
                {{ passwordSubmitting ? 'Alterando...' : 'Alterar senha' }}
              </AppButton>
            </div>
          </form>
        </div>
      </div>
    </div>
  </PageContainer>
</template>
