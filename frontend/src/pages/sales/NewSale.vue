<script setup lang="ts">
  /**
   * Tela de nova venda com grid de produtos e carrinho lateral.
   *
   * Carrega produtos e categorias em paralelo (Promise.all).
   * Filtro por categoria atualiza o grid. Carrinho controla
   * quantidades com validacao contra estoque disponivel.
   */
  import { ref, computed, onMounted } from 'vue';
  import api from '@/services/api';
  import type { Product, Category, CartItem } from '@/types';
  import { useRouter } from 'vue-router';
  import { Plus, Minus, Trash2 } from 'lucide-vue-next';
  import PageContainer from '@/components/ui/PageContainer.vue';
  import PageHeader from '@/components/ui/PageHeader.vue';
  import AppSelect from '@/components/ui/AppSelect.vue';
  import AppButton from '@/components/ui/AppButton.vue';
  import AlertBox from '@/components/ui/AlertBox.vue';

  const router = useRouter();

  const products = ref<Product[]>([]);
  const categories = ref<Category[]>([]);
  const selectedCategory = ref('');
  const cart = ref<CartItem[]>([]);
  const submitting = ref(false);
  const error = ref('');

  interface SelectOption {
    label: string;
    value: string;
  }

  const categoryOptions = computed<SelectOption[]>(() => [
    { label: 'Todas as categorias', value: '' },
    ...categories.value.map((c) => ({ label: c.name, value: c.id })),
  ]);

  const filteredProducts = computed(() => {
    if (!selectedCategory.value) return products.value;
    return products.value.filter((p) => p.category_id === selectedCategory.value);
  });

  const cartTotal = computed(() =>
    cart.value.reduce((sum, item) => sum + item.product.sale_price * item.quantity, 0),
  );

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

  async function submitSale() {
    if (cart.value.length === 0) return;
    submitting.value = true;
    error.value = '';

    try {
      await api.post('/sales', {
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
  });
</script>

<template>
  <PageContainer>
    <PageHeader title="Nova Venda" />

    <AlertBox v-if="error">{{ error }}</AlertBox>

    <AppSelect v-model="selectedCategory" :options="categoryOptions" />

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
          <p class="text-xs text-tertiary">{{ product.sku }}</p>
          <div class="flex items-center justify-between mt-2">
            <span class="text-primary-text font-medium">
              R$ {{ Number(product.sale_price).toFixed(2) }}
            </span>
            <span class="text-xs text-tertiary">{{ product.stock_quantity }} un.</span>
          </div>
        </button>
      </div>

      <div class="bg-surface-secondary border border rounded-card p-card space-y-4">
        <h2 class="font-semibold">Carrinho</h2>

        <div v-if="cart.length === 0" class="text-sm text-tertiary text-center py-8">
          Clique nos produtos para adicionar.
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="(item, i) in cart"
            :key="item.product.id"
            class="flex items-center justify-between gap-2 text-sm"
          >
            <div class="flex-1 min-w-0">
              <p class="truncate">{{ item.product.name }}</p>
              <p class="text-xs text-tertiary">
                R$ {{ Number(item.product.sale_price).toFixed(2) }} x {{ item.quantity }}
              </p>
            </div>
            <div class="flex items-center gap-1">
              <button
                class="p-1 hover:text-primary-text cursor-pointer"
                @click="changeQuantity(i, -1)"
              >
                <Minus class="w-4 h-4" />
              </button>
              <span class="w-8 text-center">{{ item.quantity }}</span>
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
          <div class="flex justify-between font-bold">
            <span>Total</span>
            <span>R$ {{ cartTotal.toFixed(2) }}</span>
          </div>

          <AppButton type="button" class="w-full" :disabled="submitting" @click="submitSale">
            {{ submitting ? 'Finalizando...' : 'Finalizar Venda' }}
          </AppButton>
        </div>
      </div>
    </div>
  </PageContainer>
</template>
