<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Services\ReservationAvailabilityService;
use App\Services\ReservationPricingService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationAvailabilityService $availability,
        private readonly ReservationPricingService $pricing,
    ) {
    }

    /**
     * Página de reserva por propiedad.
     */
    public function show(string $slug)
    {
        $property = Property::where('slug', $slug)
            ->where('is_reservable', true)
            ->with(['propertyTypes', 'suburbName', 'townshipName', 'images'])
            ->firstOrFail();

        $rangeStart = now()->toDateString();
        $rangeEnd = now()->addMonths(6)->toDateString();
        $blockedDates = $this->availability->blockedDates($property, $rangeStart, $rangeEnd);
        $averageRating = $property->averageRating();
        $reviewsCount = $property->reviews()->count();

        return view('property-reserve', compact(
            'property',
            'blockedDates',
            'averageRating',
            'reviewsCount',
            'rangeStart',
            'rangeEnd',
        ));
    }

    /**
     * Cotización en vivo para el rango y huéspedes seleccionados.
     */
    public function quote(Request $request): JsonResponse
    {
        $data = $request->validate([
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests' => ['nullable', 'integer', 'min:1'],
        ]);

        $property = Property::findOrFail($data['property_id']);

        if (!$property->is_reservable) {
            throw ValidationException::withMessages([
                'property_id' => 'La propiedad no acepta reservaciones.',
            ]);
        }

        $guests = (int) ($data['guests'] ?? 1);
        if ($property->max_guests && $guests > $property->max_guests) {
            throw ValidationException::withMessages([
                'guests' => "El máximo de huéspedes es {$property->max_guests}.",
            ]);
        }

        $available = $this->availability->isAvailable($property, $data['check_in'], $data['check_out']);
        $quote = $this->pricing->quote($property, $data['check_in'], $data['check_out']);

        return response()->json([
            'available' => $available,
            'guests' => $guests,
            ...$quote,
        ]);
    }

    /**
     * Lista de fechas bloqueadas en el rango pedido (default: 6 meses).
     */
    public function availability(int $propertyId, Request $request): JsonResponse
    {
        $property = Property::where('id', $propertyId)
            ->where('is_reservable', true)
            ->firstOrFail();

        $from = $request->input('from', now()->toDateString());
        $to = $request->input('to', now()->addMonths(6)->toDateString());

        try {
            CarbonImmutable::parse($from);
            CarbonImmutable::parse($to);
        } catch (\Exception) {
            throw ValidationException::withMessages([
                'from' => 'Fechas inválidas.',
            ]);
        }

        return response()->json([
            'blocked' => $this->availability->blockedDates($property, $from, $to),
            'from' => $from,
            'to' => $to,
        ]);
    }
}
