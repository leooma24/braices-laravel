<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'max_listings',
        'price',
        'duration',
        'characteristics'
    ];

    protected $casts = [
        'characteristics' => 'array',
    ];

    public function userPackages()
    {
        return $this->hasMany(UserPackage::class);
    }

    public function promotions()
    {
        return $this->hasMany(PackagePromotion::class);
    }

    /* -----------------------------------------------------------------
     | Pricing helpers
     |
     | `price` está guardado como precio ANUAL (12 meses). Se divide
     | entre 12 para obtener el mensual de referencia. El "precio anual"
     | que se muestra al usuario al pagar de golpe = mensual * 10
     | (regala 2 meses). Las promociones aplican % sobre ambos.
     | ----------------------------------------------------------------- */

    public function getMonthlyPriceAttribute(): float
    {
        return $this->price > 0 ? (float) $this->price / 12 : 0.0;
    }

    /** Precio anual con beneficio "2 meses gratis" — = mensual * 10. */
    public function getAnnualPriceAttribute(): float
    {
        return $this->monthly_price * 10;
    }

    /** Cuántos UserPackages se han creado de este paquete (ventas reales). */
    public function getSalesCountAttribute(): int
    {
        return $this->userPackages()->count();
    }

    /**
     * Promoción aplicable a la PRÓXIMA venta. Si hay varias activas
     * que abarcan el rango actual, gana la de mayor descuento.
     */
    public function activePromotion(): ?PackagePromotion
    {
        $next = $this->sales_count + 1;
        return $this->promotions()
            ->where('is_active', true)
            ->where('from_count', '<=', $next)
            ->where('to_count', '>=', $next)
            ->orderByDesc('discount_percent')
            ->first();
    }

    /** Mensual después de aplicar la promo vigente (o regular si no hay). */
    public function getEffectiveMonthlyAttribute(): float
    {
        $p = $this->activePromotion();
        if (!$p) return $this->monthly_price;
        return round($this->monthly_price * (100 - $p->discount_percent) / 100, 2);
    }

    /** Anual después de aplicar la promo vigente. */
    public function getEffectiveAnnualAttribute(): float
    {
        return round($this->effective_monthly * 10, 2);
    }

    /** Cuántos cupones quedan en la promo vigente. 0 si no hay promo. */
    public function getPromoSlotsRemainingAttribute(): int
    {
        $p = $this->activePromotion();
        if (!$p) return 0;
        return max(0, $p->to_count - $this->sales_count);
    }

    public function hasActivePromo(): bool
    {
        return $this->activePromotion() !== null;
    }
}
