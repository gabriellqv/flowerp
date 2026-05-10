<script setup lang="ts">
  /**
   * Tela de nova venda com grid de produtos, carrinho e dados complementares.
   *
   * Carrega produtos, categorias e clientes em paralelo.
   * Permite selecionar cliente via busca, aplicar desconto e escolher
   * forma de pagamento. Carrinho controla quantidades com validação
   * contra estoque disponível. Finaliza enviando os dados completos.
   */
  import { ref, computed, onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import api from '@/services/api';
  import type { Product, Category, Customer, CartItem } from '@/types';
  import { formatCurrency } from '@/utils/format';
  import { Plus, Minus, Trash2 } from 'lucide-vue-next';
  import PageContainer from '@/components/ui/PageContainer.vue';
  import PageHeader from '@/components/ui/PageHeader.vue';
  import AppSelect from '@/components/ui/AppSelect.vue';
  import AppButton from '@/components/ui/AppButton.vue';
  import AppInput from '@/components/ui/AppInput.vue';
  import AlertBox from '@/components/ui/AlertBox.vue';

  const router = useRouter();

  const products = ref<Product[]>([]);
  const categories = ref<Category[]>([]);
  const customers = ref<Customer[]>([]);
  const customerSearch = ref('');
  const selectedCustomer = ref<Customer | null>(null);
  const selectedCategory = ref('');
  const cart = ref<CartItem[]>([]);
  const discountValue = ref('0');
  const paymentMethod = ref('');
  const submitting = ref(false);
  const error = ref('');
  let abortController: AbortController | null = null;

  interface SelectOption {
    label: string;
    value: string;
  }

  const categoryOptions = computed<SelectOption[]>(() => [
    { label: 'Todas as categorias', value: '' },
    ...categories.value.map((c) => ({ label: c.name, value: c.id })),
  ]);

  const paymentOptions: SelectOption[] = [
    { label: 'Selecionar pagamento', value: '' },
    { label: 'Dinheiro', value: 'cash' },
    { label: 'Pix', value: 'pix' },
    { label: 'Cartão de crédito', value: 'credit_card' },
    { label: 'Cartão de débito', value: 'debit_card' },
    { label: 'Boleto', value: 'boleto' },
  ];

  const discount = computed(() => parseFloat(discountValue.value) || 0);

  const filteredProducts = computed(() => {
    if (!selectedCategory.value) return products.value;
    return products.value.filter((p) => p.category_id === selectedCategory.value);
  });

  const filteredCustomers = computed(() => {
    if (!customerSearch.value) return customers.value.slice(0, 10);
    const q = customerSearch.value.toLowerCase();
    return customers.value
      .filter((c) => c.name.toLowerCase().includes(q) || (c.document && c.document.includes(q)))
      .slice(0, 10);
  });

  const cartSubtotal = computed(() =>
    cart.value.reduce((sum, item) => sum + item.product.sale_price * item.quantity, 0),
  );

  const cartTotal = computed(() => Math.max(0, cartSubtotal.value - discount.value));

  function addToCart(product: Product) {
    const existing = cart.value.find((i) => i.product.id === product.id);
    if (existing) {
      if (existing.quantity < product.stock_quantity) {
        existing.quantity++;
      }
    } else {
      cart.value.push({ product, quantity: 1 });
    }
  }

  function removeFromCart(index: number) {
    cart.value.splice(index, 1);
  }

  function changeQuantity(index: number, delta: number) {
    const item = cart.value[index];
    const newQty = item.quantity + delta;
    if (newQty >= 1 && newQty <= item.product.stock_quantity) {
      item.quantity = newQty;
    }
  }

  function selectCustomer(customer: Customer) {
    selectedCustomer.value = customer;
    customerSearch.value = '';
  }

  function clearCustomer() {
    selectedCustomer.value = null;
  }

  async function fetchCustomers() {
    if (abortController) abortController.abort();
    const controller = new AbortController();
    abortController = controller;
    try {
      const { data } = await api.get<{ data: Customer[] }>('/customers', {
        params: { per_page: 200 },
        signal: controller.signal,
      });
      customers.value = data.data;
    } catch (e: unknown) {
      const err = e as { code?: string };
      if (err.code === 'ERR_CANCELED') return;
    }
  }

  async function submitSale() {
    if (cart.value.length === 0) return;
    if (!paymentMethod.value) {
      error.value = 'Selecione a forma de pagamento.';
      return;
    }
    submitting.value = true;
    error.value = '';

    try {
      await api.post('/sales', {
        customer_id: selectedCustomer.value?.id ?? null,
        discount: discount.value,
        payment_method: paymentMethod.value || null,
        items: cart.value.map((i) => ({
          product_id: i.product.id,
          quantity: i.quantity,
        })),
      });
      router.push('/sales');
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string } } };
      error.value = err.response?.data?.message || 'Erro ao finalizar venda.';
    } finally {
      submitting.value = false;
    }
  }

  onMounted(async () => {
    const [prodRes, catRes] = await Promise.all([
      api.get('/products', { params: { per_page: 200 } }),
      api.get<Category[]>('/categories'),
    ]);
    products.value = prodRes.data.data || prodRes.data;
    categories.value = catRes.data;
    fetchCustomers();
  });
</script>

<template>
  <PageContainer>
    <PageHeader title="Nova Venda" />

    <AlertBox v-if="error" class="mb-4">{{ error }}</AlertBox>

    <!-- Cliente -->
    <div class="mb-4">
      <label class="block text-sm text-secondary mb-1.5">Cliente</label>
      <div
        v-if="selectedCustomer"
        class="flex items-center justify-between bg-[var(--color-glass-bg)] backdrop-blur-md border border-[var(--color-glass-border)] rounded-input px-4 py-2 text-sm"
      >
        <span class="font-medium">{{ selectedCustomer.name }}</span>
        <span class="text-tertiary text-xs font-mono">{{ selectedCustomer.document || '-' }}</span>
        <button
          class="text-tertiary hover:text-error transition-colors cursor-pointer ml-2"
          title="Remover cliente"
          @click="clearCustomer"
        >
          &times;
        </button>
      </div>
      <div v-else class="relative">
        <AppInput
          v-model="customerSearch"
          placeholder="Buscar cliente por nome ou documento..."
          @focus="fetchCustomers"
        />
        <div
          v-if="customerSearch && filteredCustomers.length > 0"
          class="absolute z-50 w-full mt-1 bg-surface border border-border rounded-md shadow-xl overflow-hidden max-h-48 overflow-y-auto"
        >
          <button
            v-for="c in filteredCustomers"
            :key="c.id"
            class="w-full text-left px-4 py-2.5 text-sm hover:bg-surface-elevated hover:text-primary transition-colors flex items-center justify-between"
            @click="selectCustomer(c)"
          >
            <span>{{ c.name }}</span>
            <span class="text-tertiary text-xs font-mono">{{ c.document || '-' }}</span>
          </button>
        </div>
      </div>
    </div>

    <AppSelect v-model="selectedCategory" :options="categoryOptions" class="mb-4" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
        <button
          v-for="product in filteredProducts"
          :key="product.id"
          class="text-left bg-surface-secondary border border rounded-input p-4 hover:border-primary-hover/50 transition-colors cursor-pointer disabled:opacity-disabled-button disabled:cursor-not-allowed"
          :disabled="product.stock_quantity === 0"
          @click="addToCart(product)"
        >
          <p class="font-medium text-sm line-clamp-1">{{ product.name }}</p>
          <p class="text-xs text-tertiary font-mono">{{ product.sku }}</p>
          <div class="flex items-center justify-between mt-2">
            <span class="text-primary-text font-medium font-mono">
              {{ formatCurrency(product.sale_price) }}
            </span>
            <span class="text-xs text-tertiary font-mono">{{ product.stock_quantity }} un.</span>
          </div>
        </button>
      </div>

      <div class="bg-surface-secondary border border rounded-card p-card space-y-4 flex flex-col">
        <h2 class="font-semibold">Carrinho</h2>

        <div
          v-if="cart.length === 0"
          class="text-sm text-tertiary text-center py-8 flex-1 flex items-center justify-center"
        >
          Clique nos produtos para adicionar.
        </div>

        <div v-else class="space-y-3 flex-1 overflow-y-auto">
          <div
            v-for="(item, i) in cart"
            :key="item.product.id"
            class="flex items-center justify-between gap-2 text-sm"
          >
            <div class="flex-1 min-w-0">
              <p class="truncate">{{ item.product.name }}</p>
              <p class="text-xs text-tertiary font-mono">
                {{ formatCurrency(item.product.sale_price) }} &times; {{ item.quantity }}
              </p>
            </div>
            <div class="flex items-center gap-1">
              <button
                class="p-1 hover:text-primary-text cursor-pointer"
                @click="changeQuantity(i, -1)"
              >
                <Minus class="w-4 h-4" />
              </button>
              <span class="w-8 text-center font-mono">{{ item.quantity }}</span>
              <button
                class="p-1 hover:text-primary-text cursor-pointer"
                @click="changeQuantity(i, 1)"
              >
                <Plus class="w-4 h-4" />
              </button>
              <button
                class="p-1 hover:text-error-text ml-1 cursor-pointer"
                @click="removeFromCart(i)"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>

        <div v-if="cart.length > 0" class="border-t border pt-4 space-y-3">
          <div class="flex justify-between text-sm text-secondary">
            <span>Subtotal</span>
            <span class="font-mono">{{ formatCurrency(cartSubtotal) }}</span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <label class="text-secondary" for="discount">Desconto (R$)</label>
            <AppInput
              id="discount"
              v-model="discountValue"
              type="number"
              step="0.01"
              min="0"
              :max="cartSubtotal"
              class="!w-28"
            />
          </div>
          <div>
            <label class="block text-sm text-secondary mb-1">Pagamento</label>
            <AppSelect v-model="paymentMethod" :options="paymentOptions" />
          </div>
          <div class="flex justify-between font-semibold text-base">
            <span>Total</span>
            <span class="font-mono">{{ formatCurrency(cartTotal) }}</span>
          </div>
          <AppButton
            type="button"
            class="w-full"
            :disabled="submitting || !paymentMethod"
            @click="submitSale"
          >
            {{ submitting ? 'Finalizando...' : 'Finalizar Venda' }}
          </AppButton>
        </div>
      </div>
    </div>
  </PageContainer>
</template>
