/**
 * Tipos e interfaces compartilhados do frontend FlowERP.
 *
 * Define os contratos de dados entre a API (Laravel) e os componentes Vue,
 * garantindo type safety em toda a camada de apresentacao.
 */

export interface User {
  id: string;
  name: string;
  email: string;
  role: 'admin' | 'manager' | 'seller' | 'viewer';
}

export interface LoginResponse {
  access_token: string;
  user: User;
}

export interface Category {
  id: string;
  name: string;
}

export interface Customer {
  id: string;
  name: string;
  email: string;
  phone: string;
  document: string;
  is_active: boolean;
}

export interface Product {
  id: string;
  name: string;
  sku: string;
  category_id: string;
  category?: Category;
  cost_price: number;
  sale_price: number;
  stock_quantity: number;
  min_stock: number;
  is_active: boolean;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface SaleItem {
  product_id: string;
  product?: Product;
  quantity: number;
  unit_price: number;
}

export interface Sale {
  id: string;
  seller: User;
  customer?: { id: string; name: string };
  items: SaleItem[];
  total_amount: number;
  discount: number;
  payment_method: string | null;
  status: string;
  created_at: string;
}

export interface DashboardSummary {
  active_products: number;
  monthly_revenue: number;
  previous_revenue: number;
  revenue_change: number | null;
  low_stock_count: number;
  zero_stock_count: number;
  monthly_sales_count: number;
  previous_sales_count: number;
  sales_change: number | null;
  average_ticket: number;
  total_stock_value: number;
}

/**
 * Ponto de dado para gráficos de receita.
 */
export interface IChartDataPoint {
  label: string;
  value: number;
}

/**
 * Registro de atividade do sistema para o feed do dashboard.
 */
export interface IActivityLogEntry {
  id: string;
  user_id: number;
  user: { id: number; name: string } | null;
  action: string;
  entity: string;
  details: Record<string, unknown> | null;
  created_at: string;
}

export interface CartItem {
  product: Product;
  quantity: number;
}
