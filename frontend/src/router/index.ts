/**
 * Configuracao do Vue Router com guards de autenticacao.
 *
 * Utiliza lazy loading em todas as paginas para code splitting
 * automatico. O guard `beforeEach` redireciona usuarios nao
 * autenticados para /login e usuarios logados para longe do login.
 */

import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('@/pages/Login.vue'),
      meta: { guest: true },
    },
    {
      path: '/',
      component: () => import('@/components/layout/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'dashboard',
          component: () => import('@/pages/Dashboard.vue'),
        },
        {
          path: 'products',
          name: 'products',
          component: () => import('@/pages/products/ProductList.vue'),
        },
        {
          path: 'products/new',
          name: 'new-product',
          component: () => import('@/pages/products/ProductForm.vue'),
        },
        {
          path: 'products/:id/edit',
          name: 'edit-product',
          component: () => import('@/pages/products/ProductForm.vue'),
        },
        {
          path: 'sales',
          name: 'sales',
          component: () => import('@/pages/sales/SaleList.vue'),
        },
        {
          path: 'sales/new',
          name: 'new-sale',
          component: () => import('@/pages/sales/NewSale.vue'),
        },
        {
          path: 'sales/:id',
          name: 'sale-detail',
          component: () => import('@/pages/sales/SaleDetail.vue'),
        },
        {
          path: 'customers',
          name: 'customers',
          component: () => import('@/pages/customers/CustomerList.vue'),
        },
        {
          path: 'customers/new',
          name: 'new-customer',
          component: () => import('@/pages/customers/CustomerForm.vue'),
        },
        {
          path: 'customers/:id/edit',
          name: 'edit-customer',
          component: () => import('@/pages/customers/CustomerForm.vue'),
        },
        {
          path: 'categories',
          name: 'categories',
          component: () => import('@/pages/categories/CategoryList.vue'),
        },
        {
          path: 'categories/new',
          name: 'new-category',
          component: () => import('@/pages/categories/CategoryForm.vue'),
        },
        {
          path: 'categories/:id/edit',
          name: 'edit-category',
          component: () => import('@/pages/categories/CategoryForm.vue'),
        },
        {
          path: 'activities',
          name: 'activities',
          component: () => import('@/pages/ActivityLog.vue'),
        },
        {
          path: 'profile',
          name: 'profile',
          component: () => import('@/pages/Profile.vue'),
        },
      ],
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/pages/NotFound.vue'),
    },
  ],
});

router.beforeEach((to) => {
  const auth = useAuthStore();

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return '/login';
  }

  if (to.meta.guest && auth.isAuthenticated) {
    return '/';
  }
});

export default router;
