<?php

namespace Tests\Feature;

use App\Models\Comprobante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use App\Models\User;
use App\Models\Articulo;
use Tymon\JWTAuth\Facades\JWTAuth;

class ComprobanteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    /** @test */
    public function it_can_list_comprobantes()
    {
        Comprobante::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get('/api/comprobantes');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_can_store_comprobantes()
    {
        // Cargar múltiples archivos para la prueba
        $data = [
            'xml_file' => [
                $this->getFileForTest('stubs/archivo1.xml'),
                $this->getFileForTest('stubs/archivo2.xml'),
                // Agregar más archivos si es necesario
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->post('/api/comprobantes', $data);

        $response->assertStatus(201);


        $this->assertDatabaseHas('comprobantes', [
            'receiver_name' => 'CORPORACION TURISTICA HOTELERA PERUANA SOCIEDAD ANONIMA CERRADA - CORPORACION THP S.A.C.' // Primer comprobante
        ]);
        $this->assertDatabaseHas('comprobantes', [
            'receiver_name' => 'CENCOSUD RETAIL PERU S.A.' // Segundo comprobante
        ]);
        
    }


    // Función auxiliar para obtener el archivo XML de prueba
    protected function getFileForTest($path)
    {
        return new UploadedFile(base_path("tests/{$path}"), 'test.xml', 'text/xml', null, true);
    }

    /** @test */
    public function it_can_show_a_comprobante()
    {
        $comprobante = Comprobante::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get("/api/comprobantes/{$comprobante->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'issuer_name' => $comprobante->issuer_name
        ]);
    }

    /** @test */
    public function it_can_get_total_amount_by_article()
    {
            // Crear un usuario
            $user = User::factory()->create();
    
            // Crear un comprobante para ese usuario
            $comprobante = Comprobante::factory()->create(['user_id' => $user->id]);
    
            // Crear un artículo con un nombre específico y asociarlo al comprobante
            $nombreArticulo = 'ALFAJORES';
            $cantidad = 5;
            $precio = 10;
            Articulo::factory()->create([
                'comprobante_id' => $comprobante->id,
                'nombre' => $nombreArticulo,
                'cantidad' => $cantidad,
                'precio' => $precio,
            ]);
    
            // Calcular el monto total esperado
            $montoTotalEsperado = $cantidad * $precio;
    
            // Llamar a la función getTotalAmountByArticle
            $montoTotal = app('App\Http\Controllers\ComprobanteController')->getTotalAmountByArticle($user->id, $nombreArticulo);
    
            // Asegurarse de que el monto total calculado sea correcto
            $this->assertEquals($montoTotalEsperado, $montoTotal);
    }

    /** @test */
    public function it_can_get_total_amount()
    {
        Comprobante::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get('/api/comprobantes/monto/total');

        $response->assertStatus(200);
    }
}
