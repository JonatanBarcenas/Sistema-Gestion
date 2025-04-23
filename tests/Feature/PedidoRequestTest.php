<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Product;

class PedidoRequestTest extends TestCase
{
    use RefreshDatabase;

    // Usuario autenticado para las pruebas
    protected $user;

    // Configuración inicial para cada test
    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear y autenticar un usuario
        $this->user = User::factory()->create();
    }

    public function test_no_permite_fecha_de_entrega_pasada()
    {
        $this->actingAs($this->user);

        // Insertar cliente con status
        $customerId = DB::table('customers')->insertGetId([
            'name' => 'Cliente Prueba',
            'email' => 'cliente@example.com',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insertar producto con todos los campos requeridos
        $productId = DB::table('products')->insertGetId([
            'name' => 'Producto Prueba',
            'base_price' => 100,
            'sku' => 'SKU-001',
            'category' => 'general', // Campo requerido que faltaba
            'is_active' => true,     // Asumimos que es requerido por el controlador
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/orders', [
            'customer_id' => $customerId,
            'delivery_date' => '2023-01-01', // Fecha pasada
            'status' => 'pending',
            'notes' => 'Notas de prueba',
            'products' => [
                ['product_id' => $productId, 'quantity' => 2, 'unit_price' => 100]
            ]
        ]);

        $response->assertSessionHasErrors(['delivery_date']);
    }

    public function test_permite_crear_pedido_con_datos_validos()
    {
        $this->actingAs($this->user);

        // Insertar cliente
        $customerId = DB::table('customers')->insertGetId([
            'name' => 'Cliente Prueba',
            'email' => 'cliente@example.com',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insertar producto
        $productId = DB::table('products')->insertGetId([
            'name' => 'Producto Prueba',
            'base_price' => 100,
            'sku' => 'SKU-001',
            'category' => 'general',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/orders', [
            'customer_id' => $customerId,
            'delivery_date' => now()->addDays(1)->toDateString(), // Fecha futura
            'status' => 'pending',
            'notes' => 'Notas de prueba',
            'products' => [
                ['product_id' => $productId, 'quantity' => 2, 'unit_price' => 100]
            ]
        ]);

        $response->assertSessionHasNoErrors();
        // Verificar que redirige (lo que indicaría éxito)
        $response->assertRedirect();
        
        // Verificar que el pedido se guardó en la base de datos
        $this->assertDatabaseHas('orders', [
            'customer_id' => $customerId,
            'status' => 'pending',
        ]);
    }

    public function test_no_permite_pedido_sin_productos()
    {
        $this->actingAs($this->user);

        // Insertar cliente
        $customerId = DB::table('customers')->insertGetId([
            'name' => 'Cliente Prueba',
            'email' => 'cliente@example.com',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/orders', [
            'customer_id' => $customerId,
            'delivery_date' => now()->addDays(2)->toDateString(),
            'status' => 'pending',
            'notes' => 'Notas de prueba',
            'products' => [] // sin productos
        ]);

        $response->assertSessionHasErrors(['products']);
    }

    public function test_validacion_coherencia_fechas_pedido()
    {
        $this->actingAs($this->user);

        // Crear cliente y producto necesarios para la prueba
        $cliente = Customer::factory()->create(['status' => 'active']);
        $producto = Product::factory()->create(['is_active' => true]);

        // Caso 1: Fecha de entrega anterior a la fecha actual
        $response1 = $this->post('/orders', [
            'customer_id' => $cliente->id,
            'delivery_date' => now()->subDays(1)->toDateString(),
            'status' => 'pending',
            'notes' => 'Notas de prueba',
            'products' => [
                [
                    'product_id' => $producto->id,
                    'quantity' => 1,
                    'unit_price' => $producto->base_price
                ]
            ]
        ]);
        $response1->assertSessionHasErrors(['delivery_date']);

        // Caso 2: Fecha de entrega muy lejana
        $response2 = $this->post('/orders', [
            'customer_id' => $cliente->id,
            'delivery_date' => now()->addYears(2)->toDateString(),
            'status' => 'pending',
            'notes' => 'Notas de prueba',
            'products' => [
                [
                    'product_id' => $producto->id,
                    'quantity' => 1,
                    'unit_price' => $producto->base_price
                ]
            ]
        ]);
        $response2->assertSessionHasErrors(['delivery_date']);

        // Caso 3: Fecha válida
        $response3 = $this->post('/orders', [
            'customer_id' => $cliente->id,
            'delivery_date' => now()->addDays(5)->toDateString(),
            'status' => 'pending',
            'notes' => 'Notas de prueba',
            'products' => [
                [
                    'product_id' => $producto->id,
                    'quantity' => 1,
                    'unit_price' => $producto->base_price
                ]
            ]
        ]);
        $response3->assertSessionHasNoErrors();
        $response3->assertRedirect();
        
        // Verificar que el pedido se guardó en la base de datos
        $this->assertDatabaseHas('orders', [
            'customer_id' => $cliente->id,
            'status' => 'pending',
        ]);
    }
}