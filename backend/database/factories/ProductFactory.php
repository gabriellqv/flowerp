<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para geração de produtos de teste.
 *
 * Utiliza uma lista curada de nomes de produtos em pt-BR,
 * mapeados por categoria, para gerar dados de demonstração
 * realistas. Preços de custo variam entre R$ 5,00 e R$ 500,00,
 * com margem de lucro entre 20% e 150%.
 *
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Mapa de nomes de produtos por categoria.
     *
     * Cada chave corresponde ao nome da categoria cadastrada
     * no CategoryFactory. Usado pelo método definition() para
     * gerar nomes realistas conforme a categoria do produto.
     *
     * @var array<string, list<string>>
     */
    private const PRODUCT_NAMES = [
        'Eletrônicos' => [
            'Smart TV 50" 4K', 'Fone de Ouvido Bluetooth', 'Caixa de Som Portátil',
            'Carregador Turbo USB-C', 'Power Bank 10000mAh', 'Cabo HDMI 2.1 2m',
            'Controle Remoto Universal', 'Adaptador Bluetooth USB', 'Webcam Full HD',
            'Microfone Condensador USB', 'Ring Light 26cm', 'Relógio Digital LED',
            'Rádio Portátil AM/FM', 'Pilha Alcalina AA (4un)', 'Tomada Inteligente Wi-Fi',
        ],
        'Móveis' => [
            'Cadeira de Escritório Ergonômica', 'Mesa de Jantar 6 Lugares', 'Estante Modular 5 Prateleiras',
            'Sofá Retrátil 3 Lugares', 'Guarda-Roupa 6 Portas', 'Cômoda 4 Gavetas',
            'Escrivaninha Compacta', 'Rack para TV 55"', 'Banqueta Alta de Madeira',
            'Poltrona Decorativa', 'Criado-Mudo com Gaveta', 'Sapateira Organizadora',
            'Mesa Lateral Redonda', 'Painel para TV MDF', 'Prateleira Flutuante 80cm',
        ],
        'Limpeza' => [
            'Detergente Neutro 500ml', 'Desinfetante Floral 2L', 'Água Sanitária 1L',
            'Sabão em Pó 1kg', 'Amaciante de Roupas 2L', 'Esponja Multiuso (3un)',
            'Pano de Microfibra (5un)', 'Vassoura de Nylon', 'Rodo de Alumínio 40cm',
            'Balde Plástico 10L', 'Saco de Lixo 50L (30un)', 'Luva de Borracha M',
            'Limpa Vidros Spray 500ml', 'Cera Líquida para Piso 750ml', 'Álcool Gel 70% 500ml',
        ],
        'Alimentos' => [
            'Arroz Tipo 1 5kg', 'Feijão Carioca 1kg', 'Macarrão Espaguete 500g',
            'Óleo de Soja 900ml', 'Açúcar Cristal 1kg', 'Farinha de Trigo 1kg',
            'Molho de Tomate 340g', 'Sal Refinado 1kg', 'Café Torrado e Moído 500g',
            'Leite Integral 1L', 'Biscoito Cream Cracker 400g', 'Achocolatado em Pó 400g',
            'Granola com Mel 250g', 'Azeite de Oliva Extra Virgem 500ml', 'Aveia em Flocos 250g',
        ],
        'Bebidas' => [
            'Refrigerante Cola 2L', 'Suco de Laranja Integral 1L', 'Água Mineral 500ml (12un)',
            'Cerveja Pilsen Lata 350ml', 'Chá Verde com Limão 1.5L', 'Energético 250ml',
            'Vinho Tinto Seco 750ml', 'Suco de Uva Integral 1L', 'Água de Coco 1L',
            'Café em Cápsulas (10un)', 'Isotônico Limão 500ml', 'Leite de Amêndoas 1L',
            'Cerveja IPA Artesanal 473ml', 'Refrigerante Guaraná 2L', 'Chá de Camomila (25 sachês)',
        ],
        'Papelaria' => [
            'Caderno Universitário 200 Folhas', 'Caneta Esferográfica Azul (12un)', 'Lápis Grafite HB (12un)',
            'Borracha Branca Escolar', 'Régua Transparente 30cm', 'Tesoura Escolar 13cm',
            'Cola Bastão 40g', 'Marca-Texto Amarelo', 'Papel Sulfite A4 (500 Folhas)',
            'Envelope Pardo A4 (50un)', 'Clips Niquelados (100un)', 'Grampeador de Mesa',
            'Fita Adesiva Transparente 45mm', 'Post-it Amarelo 76x76mm (100 Folhas)', 'Pasta Catálogo 50 Plásticos',
        ],
        'Ferramentas' => [
            'Furadeira de Impacto 550W', 'Jogo de Chaves Allen (9 peças)', 'Alicate Universal 8"',
            'Chave de Fenda 1/4" x 6"', 'Martelo de Unha 27mm', 'Trena Emborrachada 5m',
            'Nível de Bolha 30cm', 'Serra Tico-Tico 400W', 'Parafusadeira a Bateria 12V',
            'Jogo de Brocas Aço Rápido (13 peças)', 'Chave Inglesa 10"', 'Arco de Serra Manual',
            'Fita Isolante 20m', 'Lanterna LED Recarregável', 'Caixa de Ferramentas Organizadora',
        ],
        'Informática' => [
            'Mouse Sem Fio 1600dpi', 'Teclado Mecânico RGB', 'Monitor LED 24" Full HD',
            'Headset Gamer com Microfone', 'Mousepad Gamer XL 70x30cm', 'Hub USB 3.0 4 Portas',
            'Pen Drive 64GB USB 3.0', 'SSD 480GB SATA III', 'Memória RAM DDR4 8GB',
            'Roteador Wi-Fi 6 AX1500', 'Cabo de Rede Cat6 2m', 'Suporte para Notebook Alumínio',
            'Filtro de Linha 6 Tomadas', 'Pasta para Notebook 15.6"', 'Webcam HD 720p',
        ],
        'Vestuário' => [
            'Camiseta Algodão Lisa M', 'Calça Jeans Slim 42', 'Bermuda Sarja Masculina',
            'Vestido Casual Feminino M', 'Moletom com Capuz Unissex G', 'Jaqueta Corta-Vento',
            'Meias Algodão (3 Pares)', 'Cueca Boxer Algodão M', 'Saia Midi Feminina P',
            'Polo Manga Curta M', 'Blazer Social Masculino 48', 'Regata Fitness Feminina',
            'Shorts Esportivo Masculino', 'Pijama Longo Feminino', 'Boné Aba Curva Ajustável',
        ],
        'Higiene' => [
            'Sabonete Líquido 250ml', 'Shampoo Anticaspa 400ml', 'Condicionador Hidratante 400ml',
            'Creme Dental 90g', 'Escova de Dentes Macia', 'Desodorante Roll-On 50ml',
            'Papel Higiênico (12 Rolos)', 'Fio Dental 100m', 'Protetor Solar FPS 50 200ml',
            'Hidratante Corporal 400ml', 'Creme para Mãos 75g', 'Lenço Umedecido (100un)',
            'Aparelho de Barbear Descartável (3un)', 'Cotonete Hastes Flexíveis (75un)', 'Enxaguante Bucal 500ml',
        ],
    ];

    /**
     * Define os valores padrão para um novo produto.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cost = fake()->randomFloat(2, 5, 500);

        return [
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-#####')),
            'cost_price' => $cost,
            'sale_price' => $cost * fake()->randomFloat(2, 1.2, 2.5),
            'stock_quantity' => fake()->numberBetween(0, 200),
            'min_stock' => fake()->numberBetween(5, 20),
            'is_active' => true,
        ];
    }

    /**
     * Resolve o nome do produto com base na categoria atribuída.
     *
     * Busca a categoria pelo ID informado nos atributos e seleciona
     * um nome aleatório da lista correspondente. Se a categoria não
     * for encontrada no mapa, gera um nome genérico comercial.
     *
     * @param  string|null  $categoryId  UUID da categoria
     * @return string Nome realista do produto
     */
    private function resolveProductName(?string $categoryId): string
    {
        if ($categoryId) {
            $category = Category::find($categoryId);

            if ($category && isset(self::PRODUCT_NAMES[$category->name])) {
                return fake()->randomElement(self::PRODUCT_NAMES[$category->name]);
            }
        }

        $allNames = array_merge(...array_values(self::PRODUCT_NAMES));

        return fake()->randomElement($allNames);
    }

    /**
     * Configura o modelo após a criação pelo factory.
     *
     * Intercepta a criação para substituir o nome genérico
     * por um nome realista baseado na categoria do produto.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Product $product) {
            $product->name = $this->resolveProductName($product->category_id);
        });
    }
}
