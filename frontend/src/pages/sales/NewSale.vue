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

  const router = useRouter();

  const products = ref<Product[]>([]);
  const categories = ref<Category[]>([]);
  const selectedCategory = ref('');
  const cart = ref<CartItem[]>([]);
  const submitting = ref(false);
  const error = ref('');

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
  <div class="p-8 space-y-6">
    <h1 class="text-2xl font-bold">Nova Venda</h1>

    <div
      v-if="error"
      class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-lg p-3 text-sm"
    >
      {{ error }}
    </div>

    <select
      v-model="selectedCategory"
      class="px-4 py-2 rounded-lg bg-zinc-900 border border-zinc-700 text-zinc-100 text-sm"
    >
      <option value="">Todas as categorias</option>
      <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
    </select>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
        <button
          v-for="product in filteredProducts"
          :key="product.id"
          class="text-left bg-zinc-900 border border-zinc-800 rounded-lg p-4 hover:border-indigo-500/50 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
          :disabled="product.stock_quantity === 0"
          @click="addToCart(product)"
        >
          <p class="font-medium text-sm line-clamp-1">{{ product.name }}</p>
          <p class="text-xs text-zinc-500">{{ product.sku }}</p>
          <div class="flex items-center justify-between mt-2">
            <span class="text-indigo-400 font-medium">
              R$ {{ Number(product.sale_price).toFixed(2) }}
            </span>
            <span class="text-xs text-zinc-500">{{ product.stock_quantity }} un.</span>
          </div>
        </button>
      </div>

      <div class="bg-zinc-900 border border-zinc-800 rounded-lg p-5 space-y-4">
        <h2 class="font-semibold">Carrinho</h2>

        <div v-if="cart.length === 0" class="text-sm text-zinc-500 text-center py-8">
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
              <p class="text-xs text-zinc-500">
                R$ {{ Number(item.product.sale_price).toFixed(2) }} x {{ item.quantity }}
              </p>
            </div>
            <div class="flex items-center gap-1">
              <button class="p-1 hover:text-indigo-400" @click="changeQuantity(i, -1)">
                <Minus class="w-4 h-4" />
              </button>
              <span class="w-8 text-center">{{ item.quantity }}</span>
              <button class="p-1 hover:text-indigo-400" @click="changeQuantity(i, 1)">
                <Plus class="w-4 h-4" />
              </button>
              <button class="p-1 hover:text-red-400 ml-1" @click="removeFromCart(i)">
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>

        <div v-if="cart.length > 0" class="border-t border-zinc-800 pt-4 space-y-3">
          <div class="flex justify-between font-bold">
            <span>Total</span>
            <span>R$ {{ cartTotal.toFixed(2) }}</span>
          </div>

          <button
            class="w-full py-2 bg-emerald-600 hover:bg-emerald-500 rounded-lg font-medium transition-colors disabled:opacity-50"
            :disabled="submitting"
            @click="submitSale"
          >
            {{ submitting ? 'Finalizando...' : 'Finalizar Venda' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
