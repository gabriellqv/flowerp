<script setup lang="ts">
  /**
   * Detalhes de uma venda com itens, cliente, vendedor e valores.
   *
   * Exibe o resumo da venda (total, desconto, forma de pagamento, status),
   * dados do cliente e vendedor, e a tabela de itens com quantidade,
   * preco unitario e subtotal. Carrega a venda via API no mount
   * usando o ID da rota.
   */
  import { ref, onMounted } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import api from '@/services/api';
  import type { Sale } from '@/types';
  import { formatCurrency } from '@/utils/format';
  import PageContainer from '@/components/ui/PageContainer.vue';
  import PageHeader from '@/components/ui/PageHeader.vue';
  import AppButton from '@/components/ui/AppButton.vue';
  import { ArrowLeft, ShoppingCart } from 'lucide-vue-next';

  const route = useRoute();
  const router = useRouter();

  const sale = ref<Sale | null>(null);
  const loading = ref(true);

  onMounted(async () => {
    try {
      const { data } = await api.get<Sale>(`/sales/${route.params.id}`);
      sale.value = data;
    } finally {
      loading.value = false;
    }
  });

  function statusBadgeClass(status: string): string {
    if (status === 'CANCELLED') return 'bg-error/10 text-error border-error/20';
    return 'bg-success-bg text-primary-text border-success-border';
  }

  function statusLabel(status: string): string {
    if (status === 'CANCELLED') return 'Cancelada';
    return 'Concluída';
  }

  function paymentMethodLabel(method: string | null): string {
    if (!method) return '-';
    const labels: Record<string, string> = {
      pix: 'PIX',
      cash: 'Dinheiro',
      card: 'Cartão',
      transfer: 'Transferência',
    };
    return labels[method] ?? method;
  }

  function goBack() {
    router.push('/sales');
  }
</script>

<template>
  <PageContainer>
    <PageHeader title="Detalhes da Venda">
      <template #actions>
        <AppButton size="sm" variant="secondary" @click="goBack">
          <ArrowLeft :size="16" class="mr-1.5" />
          Voltar
        </AppButton>
      </template>
    </PageHeader>

    <div v-if="loading" class="flex items-center justify-center py-20">
      <span class="text-sm text-tertiary animate-pulse">Carregando...</span>
    </div>

    <template v-else-if="sale">
      <!-- Resumo da venda -->
      <div
        class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-5 rounded-card border border-[var(--color-glass-border)] bg-[var(--color-glass-bg)] backdrop-blur-xl shadow-[0_4px_24px_-8px_rgba(0,0,0,0.1)]"
      >
        <div>
          <p class="text-xs text-tertiary uppercase tracking-wider">Total</p>
          <p class="text-lg font-semibold font-mono">{{ formatCurrency(sale.total_amount) }}</p>
        </div>
        <div>
          <p class="text-xs text-tertiary uppercase tracking-wider">Desconto</p>
          <p class="text-lg font-semibold font-mono">{{ formatCurrency(sale.discount) }}</p>
        </div>
        <div>
          <p class="text-xs text-tertiary uppercase tracking-wider">Pagamento</p>
          <p class="text-lg font-semibold">{{ paymentMethodLabel(sale.payment_method) }}</p>
        </div>
        <div>
          <p class="text-xs text-tertiary uppercase tracking-wider">Status</p>
          <span
            class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium border mt-1"
            :class="statusBadgeClass(sale.status)"
          >
            {{ statusLabel(sale.status) }}
          </span>
        </div>
      </div>

      <!-- Cliente e vendedor -->
      <div
        class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-5 rounded-card border border-[var(--color-glass-border)] bg-[var(--color-glass-bg)] backdrop-blur-xl shadow-[0_4px_24px_-8px_rgba(0,0,0,0.1)]"
      >
        <div>
          <p class="text-xs text-tertiary uppercase tracking-wider mb-2">Cliente</p>
          <p class="text-sm font-medium">{{ sale.customer?.name ?? 'Venda anônima' }}</p>
        </div>
        <div>
          <p class="text-xs text-tertiary uppercase tracking-wider mb-2">Vendedor</p>
          <p class="text-sm font-medium">{{ sale.seller?.name ?? '-' }}</p>
        </div>
      </div>

      <!-- Itens da venda -->
      <div
        class="overflow-x-auto rounded-card border border-[var(--color-glass-border)] bg-[var(--color-glass-bg)] backdrop-blur-xl shadow-[0_4px_24px_-8px_rgba(0,0,0,0.1)]"
      >
        <table class="w-full text-sm text-left">
          <thead class="bg-surface-elevated/30 text-secondary uppercase text-[11px] tracking-wider">
            <tr>
              <th class="px-4 py-3">Produto</th>
              <th class="px-4 py-3 text-right">Qtd</th>
              <th class="px-4 py-3 text-right">Unitário</th>
              <th class="px-4 py-3 text-right">Subtotal</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-border/50">
            <tr v-for="item in sale.items" :key="item.product_id">
              <td class="px-4 py-3 text-primary">
                {{ item.product?.name ?? '-' }}
              </td>
              <td class="px-4 py-3 text-right font-mono">{{ item.quantity }}</td>
              <td class="px-4 py-3 text-right font-mono">
                {{ formatCurrency(item.unit_price) }}
              </td>
              <td class="px-4 py-3 text-right font-mono font-medium">
                {{ formatCurrency(item.quantity * item.unit_price) }}
              </td>
            </tr>
          </tbody>
          <tfoot
            class="bg-surface-elevated/20 border-t border-[var(--color-glass-border)] text-sm font-medium"
          >
            <tr>
              <td colspan="3" class="px-4 py-3 text-right text-secondary">Subtotal</td>
              <td class="px-4 py-3 text-right font-mono">
                {{
                  formatCurrency(sale.items.reduce((sum, i) => sum + i.quantity * i.unit_price, 0))
                }}
              </td>
            </tr>
            <tr v-if="sale.discount > 0">
              <td colspan="3" class="px-4 py-3 text-right text-secondary">Desconto</td>
              <td class="px-4 py-3 text-right font-mono text-error">
                -{{ formatCurrency(sale.discount) }}
              </td>
            </tr>
            <tr class="text-base">
              <td colspan="3" class="px-4 py-3 text-right text-secondary">Total</td>
              <td class="px-4 py-3 text-right font-mono font-semibold">
                {{ formatCurrency(sale.total_amount) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Data -->
      <p class="text-xs text-tertiary">
        Venda realizada em
        {{ new Date(sale.created_at).toLocaleDateString('pt-BR') }}
        às
        {{
          new Date(sale.created_at).toLocaleTimeString('pt-BR', {
            hour: '2-digit',
            minute: '2-digit',
          })
        }}
      </p>
    </template>

    <div v-else class="flex flex-col items-center justify-center py-20 gap-3">
      <ShoppingCart :size="40" class="text-tertiary opacity-30" />
      <p class="text-tertiary text-sm">Venda não encontrada.</p>
      <AppButton size="sm" variant="secondary" @click="goBack">
        <ArrowLeft :size="16" class="mr-1.5" />
        Voltar para vendas
      </AppButton>
    </div>
  </PageContainer>
</template>
