<template>
  <div class="min-h-screen bg-surface flex items-center justify-center relative overflow-hidden">
    <!-- Mesh gradient de fundo (mesmo do layout principal) -->
    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
      <div
        class="absolute -top-20 -left-20 w-96 h-96 rounded-full bg-primary/15 blur-[120px]"
      ></div>
      <div
        class="absolute bottom-[-10%] right-[-5%] w-[35rem] h-[35rem] rounded-full bg-primary/10 blur-[150px]"
      ></div>
      <div
        class="absolute top-[40%] left-[30%] w-[25rem] h-[25rem] rounded-full bg-error/8 blur-[130px]"
      ></div>
    </div>

    <!-- Card glassmorphism central -->
    <div
      class="relative z-10 text-center px-8 py-12 max-w-md w-full mx-4 bg-[var(--color-glass-bg)] backdrop-blur-xl border border-[var(--color-glass-border)] rounded-card shadow-2xl"
    >
      <!-- Reflexo de vidro -->
      <div
        class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-[var(--color-glass-shine)] to-transparent opacity-60 pointer-events-none rounded-t-card"
      ></div>

      <!-- Código 404 animado -->
      <h1
        class="text-8xl font-bold font-mono bg-gradient-to-br from-primary via-primary-text to-primary/50 bg-clip-text text-transparent select-none mb-4 tracking-tighter"
      >
        404
      </h1>

      <!-- Mensagem -->
      <h2 class="text-xl font-semibold mb-2">Página não encontrada</h2>
      <p class="text-secondary text-sm leading-relaxed mb-8">
        A página que você está procurando não existe, foi removida ou está temporariamente
        indisponível.
      </p>

      <!-- Ações -->
      <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
        <AppButton @click="goHome">
          <LayoutDashboard :size="16" class="mr-1.5" />
          Ir para o Dashboard
        </AppButton>
        <AppButton variant="secondary" @click="goBack">
          <ArrowLeft :size="16" class="mr-1.5" />
          Voltar
        </AppButton>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  /**
   * Página 404 (Not Found) com design glassmorphism.
   *
   * Exibida quando o usuário navega para uma rota inexistente.
   * Inclui gradiente de fundo, código 404 com gradiente de texto,
   * e botões para voltar ao dashboard ou à página anterior.
   */
  import { useRouter } from 'vue-router';
  import { LayoutDashboard, ArrowLeft } from 'lucide-vue-next';
  import AppButton from '@/components/ui/AppButton.vue';

  const router = useRouter();

  function goHome() {
    router.push('/');
  }

  function goBack() {
    router.back();
  }
</script>
