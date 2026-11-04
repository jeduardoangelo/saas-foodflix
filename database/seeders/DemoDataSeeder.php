<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'FoodflixDemo2026!';

    public function run()
    {
        DB::transaction(function () {
            $categories = $this->categories();
            $products = $this->products($categories);
            $customers = $this->customers();
            $this->orders($customers, $products);
        });

        $this->command->info('Dados de demonstração inseridos: 24 produtos, 16 clientes e 40 pedidos.');
        $this->command->line('A senha comum das contas de demonstração é: '.self::DEMO_PASSWORD);
    }

    private function categories(): array
    {
        $definitions = [
            ['name' => 'Lanches', 'slug' => 'lanches', 'display_order' => 1],
            ['name' => 'Pizzas', 'slug' => 'pizzas', 'display_order' => 2],
            ['name' => 'Pastéis', 'slug' => 'pasteis', 'display_order' => 3],
            ['name' => 'Bebidas', 'slug' => 'bebidas', 'display_order' => 4],
        ];
        $categories = [];

        foreach ($definitions as $definition) {
            $categories[$definition['slug']] = Category::firstOrCreate(
                ['slug' => $definition['slug']],
                $definition
            );
        }

        return $categories;
    }

    private function products(array $categories): array
    {
        $definitions = [
            ['lanches', 'X-Burger da Casa', '24.90', 'Hambúrguer artesanal com queijo e molho da casa.', 'Pão, carne bovina, queijo prato, alface, tomate e molho da casa.', 'demo-x-burger-da-casa.png'],
            ['lanches', 'X-Salada Especial', '27.90', 'Clássico com salada fresca e hambúrguer artesanal.', 'Pão, carne bovina, queijo, alface, tomate e maionese.', 'demo-x-salada-especial.png'],
            ['lanches', 'Smash Duplo Cheddar', '32.90', 'Dois discos smash com cheddar cremoso.', 'Pão brioche, duas carnes, cheddar e cebola caramelizada.', 'demo-smash-duplo-cheddar.png'],
            ['lanches', 'Burger Bacon Crocante', '31.90', 'Hambúrguer com bacon crocante e queijo.', 'Pão, carne bovina, bacon, queijo, alface e molho especial.', 'demo-burger-bacon-crocante.png'],
            ['lanches', 'Frango Crocante', '26.90', 'Filé de frango empanado servido com salada.', 'Pão, frango empanado, queijo, alface e maionese temperada.', 'demo-frango-crocante.png'],
            ['lanches', 'Veggie Grill', '25.90', 'Opção vegetariana com hambúrguer de legumes.', 'Pão, burger de grão-de-bico, queijo, rúcula e tomate.', 'demo-veggie-grill.png'],
            ['pizzas', 'Pizza Marguerita', '49.90', 'Pizza média com molho de tomate, queijo e manjericão.', 'Massa, molho de tomate, muçarela, tomate e manjericão.', 'demo-pizza-marguerita.png'],
            ['pizzas', 'Pizza Calabresa', '52.90', 'Pizza média de calabresa com cebola.', 'Massa, molho, muçarela, calabresa e cebola.', 'demo-pizza-calabresa.png'],
            ['pizzas', 'Pizza Quatro Queijos', '59.90', 'Combinação cremosa de quatro queijos.', 'Massa, muçarela, provolone, parmesão e gorgonzola.', 'demo-pizza-quatro-queijos.png'],
            ['pizzas', 'Pizza Frango com Requeijão', '57.90', 'Pizza média de frango desfiado e requeijão.', 'Massa, molho, frango, requeijão cremoso e orégano.', 'demo-pizza-frango-requeijao.png'],
            ['pizzas', 'Pizza Pepperoni', '58.90', 'Pizza média com pepperoni e muçarela.', 'Massa, molho, muçarela, pepperoni e orégano.', 'demo-pizza-pepperoni.png'],
            ['pizzas', 'Pizza Portuguesa', '56.90', 'Receita clássica com presunto, ovo e cebola.', 'Massa, molho, muçarela, presunto, ovo, cebola e azeitona.', 'demo-pizza-portuguesa.png'],
            ['pasteis', 'Pastel de Carne', '12.90', 'Pastel frito recheado com carne temperada.', 'Massa de pastel, carne bovina, cebola e temperos.', 'demo-pastel-carne.png'],
            ['pasteis', 'Pastel de Queijo', '12.90', 'Pastel crocante com queijo derretido.', 'Massa de pastel e queijo muçarela.', 'demo-pastel-queijo.png'],
            ['pasteis', 'Pastel de Frango', '13.90', 'Recheio de frango desfiado e temperado.', 'Massa de pastel, frango, cebola e cheiro-verde.', 'demo-pastel-frango.png'],
            ['pasteis', 'Pastel de Pizza', '13.90', 'Recheio inspirado no sabor de pizza.', 'Massa de pastel, muçarela, tomate e orégano.', 'demo-pastel-pizza.png'],
            ['pasteis', 'Pastel de Palmito', '14.90', 'Pastel vegetariano com palmito cremoso.', 'Massa de pastel, palmito, tomate e requeijão.', 'demo-pastel-palmito.png'],
            ['pasteis', 'Pastel de Chocolate', '14.90', 'Pastel doce servido com chocolate.', 'Massa de pastel e chocolate ao leite.', 'demo-pastel-chocolate.png'],
            ['bebidas', 'Refrigerante Cola 350 ml', '6.50', 'Lata gelada de refrigerante sabor cola.', 'Refrigerante sabor cola.', 'demo-refri-cola-350ml.png'],
            ['bebidas', 'Refrigerante Guaraná 350 ml', '6.50', 'Lata gelada de refrigerante sabor guaraná.', 'Refrigerante sabor guaraná.', 'demo-refri-guarana-350ml.png'],
            ['bebidas', 'Suco de Laranja 300 ml', '8.90', 'Suco de laranja pronto para beber.', 'Suco de laranja.', 'demo-suco-laranja-300ml.png'],
            ['bebidas', 'Água Mineral 500 ml', '4.00', 'Água mineral sem gás.', 'Água mineral.', 'demo-agua-mineral-500ml.png'],
            ['bebidas', 'Chá Gelado de Limão 450 ml', '7.90', 'Chá gelado com sabor de limão.', 'Chá, água, açúcar e suco de limão.', 'demo-cha-gelado-limao-450ml.png'],
            ['bebidas', 'Milk-shake de Chocolate 400 ml', '16.90', 'Milk-shake cremoso de chocolate.', 'Leite, sorvete de chocolate e calda.', 'demo-milkshake-chocolate-400ml.png'],
        ];
        $products = [];

        foreach ($definitions as $definition) {
            list($categorySlug, $name, $price, $description, $ingredients, $image) = $definition;
            $product = Product::firstOrCreate(
                ['name' => $name],
                [
                    'category_id' => $categories[$categorySlug]->id,
                    'description' => $description,
                    'ingredients' => $ingredients,
                    'price' => $price,
                    'image_url' => 'products/'.$image,
                    'active' => true,
                ]
            );

            if ($product->image_url === null) {
                $product->image_url = 'products/'.$image;
                $product->save();
            }

            $products[] = $product;
        }

        return $products;
    }

    private function customers(): array
    {
        $names = [
            'Ana Souza', 'Bruno Lima', 'Carla Mendes', 'Diego Martins',
            'Elisa Rocha', 'Felipe Alves', 'Gabriela Costa', 'Henrique Nunes',
            'Isabela Ferreira', 'João Ribeiro', 'Karina Barros', 'Lucas Carvalho',
            'Marina Teixeira', 'Nicolas Gomes', 'Olívia Santos', 'Pedro Almeida',
        ];
        $customers = [];

        foreach ($names as $index => $name) {
            $number = $index + 1;
            $email = sprintf('demo.cliente%02d@example.test', $number);
            $customer = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'cpf' => $this->syntheticCpf($number),
                    'phone' => sprintf('1190000%04d', $number),
                    'password' => Hash::make(self::DEMO_PASSWORD),
                ]
            );

            Address::firstOrCreate(
                [
                    'user_id' => $customer->id,
                    'street' => sprintf('Rua de Demonstração %02d', $number),
                    'number' => (string) (100 + $number),
                    'neighborhood' => 'Bairro Teste',
                    'city' => 'São Paulo',
                ],
                ['complement' => null, 'is_default' => true]
            );

            $customers[] = $customer;
        }

        return $customers;
    }

    private function orders(array $customers, array $products): void
    {
        $statuses = ['novo', 'em_preparo', 'saiu_para_entrega', 'concluido'];

        for ($number = 1; $number <= 40; $number++) {
            $customer = $customers[($number - 1) % count($customers)];
            $deliveryMethod = $number % 5 === 0 ? 'retirada' : 'entrega';
            $address = $deliveryMethod === 'entrega'
                ? $customer->addresses()->where('is_default', true)->first()
                : null;
            $placedAt = $this->orderDate($number);
            $status = $statuses[($number - 1) % count($statuses)];
            $orderNumber = sprintf('%06d', $number);
            $lineCount = 1 + (($number - 1) % 3);
            $items = [];
            $subtotalCents = 0;

            for ($line = 0; $line < $lineCount; $line++) {
                $product = $products[(($number - 1) * 3 + $line * 7) % count($products)];
                $quantity = 1 + (($number + $line) % 3);
                $unitPriceCents = (int) round(((float) $product->price) * 100);
                $itemSubtotalCents = $unitPriceCents * $quantity;
                $subtotalCents += $itemSubtotalCents;
                $items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price_cents' => $unitPriceCents,
                    'subtotal_cents' => $itemSubtotalCents,
                ];
            }

            $deliveryFeeCents = $deliveryMethod === 'entrega' ? 700 : 0;
            $totalCents = $subtotalCents + $deliveryFeeCents;
            $paymentMethod = $number % 2 === 0 ? 'pix' : 'cartao_credito';
            $order = Order::firstOrCreate(
                ['order_number' => $orderNumber],
                [
                    'user_id' => $customer->id,
                    'status' => $status,
                    'delivery_method' => $deliveryMethod,
                    'delivery_address_snapshot' => $address ? [
                        'street' => $address->street,
                        'number' => $address->number,
                        'neighborhood' => $address->neighborhood,
                        'city' => $address->city,
                        'complement' => $address->complement,
                    ] : null,
                    'payment_method' => $paymentMethod,
                    'payment_status' => 'pago',
                    'subtotal' => $this->money($subtotalCents),
                    'delivery_fee' => $this->money($deliveryFeeCents),
                    'total' => $this->money($totalCents),
                    'created_at' => $placedAt,
                    'updated_at' => $placedAt,
                ]
            );

            if ($order->wasRecentlyCreated) {
                foreach ($items as $item) {
                    $order->items()->create([
                        'product_id' => $item['product']->id,
                        'product_name_snapshot' => $item['product']->name,
                        'unit_price_snapshot' => $this->money($item['unit_price_cents']),
                        'quantity' => $item['quantity'],
                        'subtotal' => $this->money($item['subtotal_cents']),
                        'created_at' => $placedAt,
                        'updated_at' => $placedAt,
                    ]);
                }

                $order->payment()->create([
                    'gateway' => 'demo',
                    'gateway_transaction_id' => 'DEMO-TXN-'.sprintf('%06d', $number),
                    'method' => $paymentMethod === 'cartao_credito' ? 'credit_card' : 'pix',
                    'status' => 'aprovado',
                    'amount' => $this->money($totalCents),
                    'paid_at' => $placedAt,
                    'created_at' => $placedAt,
                    'updated_at' => $placedAt,
                ]);
            }
        }
    }

    private function orderDate(int $number): Carbon
    {
        $date = now()->copy()->startOfDay();

        if ($number > 20) {
            $daysAgo = (($number - 21) % 9) + 1;
            $date->subDays($daysAgo);
        }

        $slot = ($number - 1) % 20;

        return $date->setTime(11 + ($slot % 11), ($slot * 7) % 60);
    }

    private function money(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    private function syntheticCpf(int $sequence): string
    {
        $digits = str_split(str_pad((string) $sequence, 9, '0', STR_PAD_LEFT));
        $sum = 0;

        for ($index = 0; $index < 9; $index++) {
            $sum += (int) $digits[$index] * (10 - $index);
        }

        $remainder = $sum % 11;
        $digits[] = (string) ($remainder < 2 ? 0 : 11 - $remainder);
        $sum = 0;

        for ($index = 0; $index < 10; $index++) {
            $sum += (int) $digits[$index] * (11 - $index);
        }

        $remainder = $sum % 11;
        $digits[] = (string) ($remainder < 2 ? 0 : 11 - $remainder);

        return implode('', $digits);
    }
}
