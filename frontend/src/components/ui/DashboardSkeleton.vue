<script setup lang="ts">
  /**
   * Skeleton de carregamento completo do Dashboard.
   *
   * Replica a estrutura visual do Dashboard (7 KPI cards,
   * 2 gráficos à esquerda e 2 blocos à direita) usando
   * apenas elementos pulsantes (animate-pulse) para dar
   * uma percepção instantânea de layout enquanto a API responde.
   */
</script>

<template>
  <div>
    <!-- Skeleton: Título -->
    <div class="h-7 w-48 bg-zinc-200/50 dark:bg-zinc-700/30 rounded-md animate-pulse mb-6"></div>

    <!-- Skeleton: 7 KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 2xl:grid-cols-7 xl:grid-cols-7 gap-4 mb-6">
      <div
        v-for="i in 7"
        :key="i"
        class="bg-[var(--color-glass-bg)] backdrop-blur-xl border border-[var(--color-glass-border)] rounded-card p-4 flex flex-col gap-3"
      >
        <div class="flex items-center gap-2">
          <div
            class="w-8 h-8 rounded-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse shrink-0"
          ></div>
          <div class="h-4 w-20 bg-zinc-200/50 dark:bg-zinc-700/30 rounded animate-pulse"></div>
        </div>
        <div class="h-6 w-24 bg-zinc-200/50 dark:bg-zinc-700/30 rounded animate-pulse"></div>
      </div>
    </div>

    <!-- Skeleton: Layout Principal (2/3 + 1/3) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
      <!-- Esquerda (2/3) -->
      <div class="lg:col-span-2 flex flex-col gap-4">
        <!-- Skeleton: Gráfico de Receita -->
        <div
          class="bg-[var(--color-glass-bg)] backdrop-blur-xl border border-[var(--color-glass-border)] rounded-card p-card"
        >
          <div class="flex items-center justify-between mb-4">
            <div class="h-5 w-20 bg-zinc-200/50 dark:bg-zinc-700/30 rounded animate-pulse"></div>
            <div class="flex gap-1">
              <div class="h-7 w-10 bg-zinc-200/50 dark:bg-zinc-700/30 rounded animate-pulse"></div>
              <div class="h-7 w-10 bg-zinc-200/50 dark:bg-zinc-700/30 rounded animate-pulse"></div>
              <div class="h-7 w-10 bg-zinc-200/50 dark:bg-zinc-700/30 rounded animate-pulse"></div>
            </div>
          </div>
          <div class="h-48 flex items-end justify-between gap-4 px-4 pb-6 pt-4">
            <div
              class="w-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-t-md h-[40%]"
            ></div>
            <div
              class="w-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-t-md h-[70%]"
            ></div>
            <div
              class="w-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-t-md h-[30%]"
            ></div>
            <div
              class="w-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-t-md h-[90%]"
            ></div>
            <div
              class="w-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-t-md h-[50%]"
            ></div>
            <div
              class="w-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-t-md h-[80%]"
            ></div>
          </div>
        </div>

        <!-- Skeleton: Top Produtos (1/2) + Categorias (1/2) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div
            class="bg-[var(--color-glass-bg)] backdrop-blur-xl border border-[var(--color-glass-border)] rounded-card p-card"
          >
            <div
              class="h-5 w-52 bg-zinc-200/50 dark:bg-zinc-700/30 rounded animate-pulse mb-4"
            ></div>
            <div class="h-48 flex flex-col justify-between gap-3 px-2 py-2">
              <div
                class="h-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-r-md w-[80%]"
              ></div>
              <div
                class="h-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-r-md w-[60%]"
              ></div>
              <div
                class="h-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-r-md w-[90%]"
              ></div>
              <div
                class="h-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-r-md w-[50%]"
              ></div>
              <div
                class="h-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse rounded-r-md w-[70%]"
              ></div>
            </div>
          </div>
          <div
            class="bg-[var(--color-glass-bg)] backdrop-blur-xl border border-[var(--color-glass-border)] rounded-card p-card"
          >
            <div
              class="h-5 w-44 bg-zinc-200/50 dark:bg-zinc-700/30 rounded animate-pulse mb-4"
            ></div>
            <div class="h-52 flex items-center justify-center">
              <div
                class="w-40 h-40 border-[16px] border-zinc-200/50 dark:border-zinc-700/30 rounded-full animate-pulse"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Direita (1/3) -->
      <div class="lg:col-span-1">
        <!-- Skeleton: Activity Feed -->
        <div
          class="bg-[var(--color-glass-bg)] backdrop-blur-xl border border-[var(--color-glass-border)] rounded-card p-card h-full"
        >
          <div class="h-5 w-36 bg-zinc-200/50 dark:bg-zinc-700/30 rounded animate-pulse mb-4"></div>
          <div class="flex flex-col gap-4">
            <div v-for="i in 5" :key="i" class="flex items-start gap-3">
              <div
                class="w-8 h-8 rounded-full bg-zinc-200/50 dark:bg-zinc-700/30 animate-pulse shrink-0"
              ></div>
              <div class="flex-1 space-y-2 py-1">
                <div
                  class="h-4 bg-zinc-200/50 dark:bg-zinc-700/30 rounded w-3/4 animate-pulse"
                ></div>
                <div
                  class="h-3 bg-zinc-200/50 dark:bg-zinc-700/30 rounded w-1/2 animate-pulse"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
