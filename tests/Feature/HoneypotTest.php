<?php

namespace Tests\Feature;

use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class HoneypotTest extends TestCase
{
    use RefreshDatabase;

    /**
     * El captcha se valida después del honeypot, así que para probar el
     * middleware en aislamiento basta con una ruta propia protegida por él.
     */
    protected function setUp(): void
    {
        parent::setUp();

        \Route::post('/_test/honeypot', function () {
            Lead::create([
                'name' => 'humano',
                'email' => 'humano@test.com',
                'message' => 'mensaje',
                'source' => 'contacto',
            ]);

            return back()->with('success', 'Tu mensaje ha sido enviado correctamente');
        })->middleware(['web', 'honeypot']);
    }

    public function test_deja_pasar_un_envio_normal(): void
    {
        $this->post('/_test/honeypot', [
            'direccion_alterna' => '',
            'hp_ts' => Crypt::encrypt(time() - 30),
        ]);

        $this->assertSame(1, Lead::count());
    }

    public function test_descarta_cuando_el_campo_trampa_viene_lleno(): void
    {
        $response = $this->post('/_test/honeypot', [
            'direccion_alterna' => 'http://spam.example',
            'hp_ts' => Crypt::encrypt(time() - 30),
        ]);

        $this->assertSame(0, Lead::count());
        // Al bot se le responde igual que a una persona para no delatarlo.
        $response->assertSessionHas('success');
    }

    public function test_descarta_cuando_se_envia_demasiado_rapido(): void
    {
        $this->post('/_test/honeypot', [
            'direccion_alterna' => '',
            'hp_ts' => Crypt::encrypt(time()),
        ]);

        $this->assertSame(0, Lead::count());
    }

    public function test_descarta_cuando_la_marca_de_tiempo_fue_alterada(): void
    {
        $this->post('/_test/honeypot', [
            'direccion_alterna' => '',
            'hp_ts' => 'esto-no-es-un-valor-cifrado',
        ]);

        $this->assertSame(0, Lead::count());
    }

    public function test_sin_marca_de_tiempo_no_se_castiga(): void
    {
        // Una vista vieja en caché puede no traer el campo todavía.
        $this->post('/_test/honeypot', ['direccion_alterna' => '']);

        $this->assertSame(1, Lead::count());
    }
}
