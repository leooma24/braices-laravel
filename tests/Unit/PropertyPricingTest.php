<?php

namespace Tests\Unit;

use App\Models\Property;
use PHPUnit\Framework\TestCase;

/**
 * Una habitacion cobrada por noche salia como "$600 /mes" porque el sufijo
 * solo miraba el tipo de transaccion. Estos casos fijan la regla.
 */
class PropertyPricingTest extends TestCase
{
    private function propiedad(array $atributos = []): Property
    {
        $property = new Property();
        $property->forceFill(array_merge([
            'price' => 8000,
            'transaction_type_id' => 1, // Renta
            'is_reservable' => false,
            'rate_period' => null,
            'price_per_night' => null,
        ], $atributos));

        return $property;
    }

    public function test_renta_normal_se_cobra_por_mes(): void
    {
        $property = $this->propiedad();

        $this->assertSame('/mes', $property->priceSuffix());
        $this->assertSame(8000.0, $property->cardPrice());
    }

    public function test_venta_no_lleva_sufijo(): void
    {
        $property = $this->propiedad(['transaction_type_id' => 3, 'price' => 2500000]);

        $this->assertNull($property->priceSuffix());
        $this->assertSame(2500000.0, $property->cardPrice());
    }

    public function test_reservable_por_noche_usa_la_tarifa_y_dice_noche(): void
    {
        $property = $this->propiedad([
            'is_reservable' => true,
            'rate_period' => 'noche',
            'price_per_night' => 600,
        ]);

        $this->assertTrue($property->chargesPerStay());
        $this->assertSame('/noche', $property->priceSuffix());
        $this->assertSame(600.0, $property->cardPrice());
    }

    public function test_reservable_por_mes_sigue_diciendo_mes(): void
    {
        $property = $this->propiedad([
            'is_reservable' => true,
            'rate_period' => 'mes',
            'price_per_night' => 5500,
        ]);

        $this->assertSame('/mes', $property->priceSuffix());
        $this->assertSame(5500.0, $property->cardPrice());
    }

    /**
     * El caso que reporto Alberto: lleno el precio por noche pero dejo
     * apagado "¿Se puede reservar?". Sin el switch, la tarifa no cuenta.
     */
    public function test_tarifa_sin_el_switch_prendido_no_cuenta(): void
    {
        $property = $this->propiedad([
            'is_reservable' => false,
            'rate_period' => 'noche',
            'price_per_night' => 600,
        ]);

        $this->assertFalse($property->chargesPerStay());
        $this->assertSame('/mes', $property->priceSuffix());
        $this->assertSame(8000.0, $property->cardPrice());
    }

    public function test_reservable_sin_tarifa_cae_al_precio_de_lista(): void
    {
        $property = $this->propiedad([
            'is_reservable' => true,
            'rate_period' => 'noche',
            'price_per_night' => 0,
        ]);

        $this->assertFalse($property->chargesPerStay());
        $this->assertSame('/mes', $property->priceSuffix());
        $this->assertSame(8000.0, $property->cardPrice());
    }
}
