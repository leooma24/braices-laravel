<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Reservation;
use App\Notifications\Reservation\ReservationCancelled;
use App\Notifications\Reservation\ReservationConfirmedGuest;
use App\Notifications\Reservation\ReservationConfirmedHost;
use App\Services\ReservationAvailabilityService;
use App\Services\ReservationPricingService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use MercadoPago\Item;
use MercadoPago\Payer;
use MercadoPago\Preference;

class ReservationController extends Controller
{
    /** Minutos que una reservación pendiente vive antes de expirar. */
    private const PENDING_TTL_MINUTES = 15;

    /** Horas mínimas antes del check-in para cancelar gratis. */
    private const FREE_CANCEL_WINDOW_HOURS = 48;

    public function __construct(
        private readonly ReservationAvailabilityService $availability,
        private readonly ReservationPricingService $pricing,
    ) {
    }

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
            throw ValidationException::withMessages(['property_id' => 'La propiedad no acepta reservaciones.']);
        }

        $guests = (int) ($data['guests'] ?? 1);
        if ($property->max_guests && $guests > $property->max_guests) {
            throw ValidationException::withMessages(['guests' => "El máximo de huéspedes es {$property->max_guests}."]);
        }

        $available = $this->availability->isAvailable($property, $data['check_in'], $data['check_out']);
        $quote = $this->pricing->quote($property, $data['check_in'], $data['check_out']);

        return response()->json([
            'available' => $available,
            'guests' => $guests,
            ...$quote,
        ]);
    }

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
            throw ValidationException::withMessages(['from' => 'Fechas inválidas.']);
        }

        return response()->json([
            'blocked' => $this->availability->blockedDates($property, $from, $to),
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * Crea una reservación pendiente, bloqueando la propiedad bajo lock pesimista
     * para evitar doble-reserva por race condition entre dos huéspedes simultáneos.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests' => ['required', 'integer', 'min:1'],
        ]);

        $reservation = DB::transaction(function () use ($data) {
            // Lock pesimista sobre el row de propiedad mientras validamos disponibilidad
            // y creamos la reservación. Otra petición concurrente espera aquí.
            $property = Property::where('id', $data['property_id'])
                ->where('is_reservable', true)
                ->lockForUpdate()
                ->firstOrFail();

            if ($property->max_guests && $data['guests'] > $property->max_guests) {
                throw ValidationException::withMessages([
                    'guests' => "El máximo de huéspedes es {$property->max_guests}.",
                ]);
            }

            if (!$this->availability->isAvailable($property, $data['check_in'], $data['check_out'])) {
                throw ValidationException::withMessages([
                    'check_in' => 'Las fechas seleccionadas ya no están disponibles.',
                ]);
            }

            $quote = $this->pricing->quote($property, $data['check_in'], $data['check_out']);

            return Reservation::create([
                'property_id' => $property->id,
                'user_id' => Auth::id(),
                'check_in_date' => $data['check_in'],
                'check_out_date' => $data['check_out'],
                'guests' => $data['guests'],
                'status' => ReservationStatus::Pendiente,
                'nights' => $quote['nights'],
                'subtotal' => $quote['subtotal'],
                'cleaning_fee_snapshot' => $quote['cleaning_fee'],
                'total_price' => $quote['total'],
                'expires_at' => now()->addMinutes(self::PENDING_TTL_MINUTES),
            ]);
        });

        return redirect()->route('reservation.checkout', $reservation);
    }

    /**
     * Pantalla de pago: genera (o reutiliza) la preferencia de MercadoPago
     * y muestra el botón de checkout.
     */
    public function checkout(Reservation $reservation)
    {
        $this->authorizeOwner($reservation);

        if ($reservation->isExpired()) {
            $reservation->update(['status' => ReservationStatus::Cancelada]);
            return redirect()->route('my.reservations')
                ->with('error', 'La reservación expiró antes de pagar. Vuelve a intentar.');
        }

        if ($reservation->status === ReservationStatus::Confirmada) {
            return redirect()->route('my.reservations')
                ->with('success', 'Esta reservación ya está pagada y confirmada.');
        }

        if ($reservation->status !== ReservationStatus::Pendiente) {
            return redirect()->route('my.reservations')
                ->with('error', 'Esta reservación no se puede pagar.');
        }

        $payment = Payment::firstOrCreate(
            [
                'reservation_id' => $reservation->id,
                'status' => 'Pending',
            ],
            [
                'user_id' => $reservation->user_id,
                'external_reference' => '',
                'payment_id' => '',
                'amount' => $reservation->total_price,
                'provider' => 'mercadopago',
            ],
        );

        if (empty($payment->external_reference)) {
            try {
                $preferenceId = $this->createMpPreference($reservation);
            } catch (\Throwable $e) {
                Log::error('MercadoPago preference creation failed', [
                    'reservation_id' => $reservation->id,
                    'error' => $e->getMessage(),
                ]);
                return redirect()->route('my.reservations')
                    ->with('error', 'No se pudo iniciar el cobro. Verifica las credenciales de MercadoPago e intenta más tarde.');
            }
            $payment->update(['external_reference' => $preferenceId]);
        }

        $reservation->load('property');

        return view('reservation-checkout', [
            'reservation' => $reservation,
            'preferenceId' => $payment->external_reference,
            'mpPublicKey' => env('MERCADO_PAGO_PUBLIC_KEY'),
        ]);
    }

    /**
     * Endpoint que MercadoPago llama (success/failure/pending). Los webhooks reales
     * idealmente irían a un endpoint server-to-server con verificación de firma; este
     * patrón de back_url es el que usa el resto del proyecto, así que mantengo consistencia.
     */
    public function paymentReturn(Request $request)
    {
        $preferenceId = $request->query('preference_id');
        $status = $request->query('status');

        if (!$preferenceId) {
            return redirect()->route('main')->with('error', 'Pago no encontrado');
        }

        $payment = Payment::where('external_reference', $preferenceId)
            ->whereNotNull('reservation_id')
            ->first();

        if (!$payment) {
            return redirect()->route('main')->with('error', 'Pago no encontrado');
        }

        $reservation = Reservation::with('property.user', 'user')->find($payment->reservation_id);
        if (!$reservation) {
            return redirect()->route('main')->with('error', 'Reservación no encontrada');
        }

        if ($status === 'approved') {
            $payment->update([
                'payment_id' => $request->query('payment_id', ''),
                'status' => 'Approved',
            ]);

            if ($reservation->status === ReservationStatus::Pendiente) {
                $reservation->update([
                    'status' => ReservationStatus::Confirmada,
                    'expires_at' => null,
                ]);

                $this->notifyConfirmation($reservation);
            }

            return redirect()->route('my.reservations')
                ->with('success', '¡Tu reservación fue confirmada!');
        }

        $payment->update(['status' => 'Rejected']);

        return redirect()->route('reservation.checkout', $reservation)
            ->with('error', 'El pago no se completó. Puedes intentar de nuevo.');
    }

    /**
     * Cancelación por parte del huésped.
     * Política: gratis si quedan más de FREE_CANCEL_WINDOW_HOURS antes del check-in.
     */
    public function cancel(Reservation $reservation)
    {
        $this->authorizeOwner($reservation);

        if (!$reservation->isCancellable()) {
            return redirect()->back()->with('error', 'Esta reservación ya no se puede cancelar.');
        }

        $hoursToCheckIn = now()->diffInHours($reservation->check_in_date->startOfDay(), false);
        $isFree = $hoursToCheckIn >= self::FREE_CANCEL_WINDOW_HOURS;

        $reservation->update([
            'status' => ReservationStatus::Cancelada,
            'expires_at' => null,
        ]);

        $reservation->loadMissing('user', 'property.user');
        $reservation->user?->notify(new ReservationCancelled($reservation, 'guest'));
        $reservation->property?->user?->notify(new ReservationCancelled($reservation, 'guest'));

        $msg = $isFree
            ? 'Reservación cancelada. Procesaremos tu reembolso completo.'
            : 'Reservación cancelada. La política indica que este caso no aplica reembolso completo; te contactaremos.';

        return redirect()->route('my.reservations')->with('success', $msg);
    }

    private function authorizeOwner(Reservation $reservation): void
    {
        abort_if($reservation->user_id !== Auth::id(), 403);
    }

    private function createMpPreference(Reservation $reservation): string
    {
        if (!env('MERCADO_PAGO_ACCESS_TOKEN')) {
            throw new \RuntimeException('MERCADO_PAGO_ACCESS_TOKEN no está configurado.');
        }

        $reservation->loadMissing('property', 'user');
        $preference = new Preference();

        $item = new Item();
        $item->title = "Reservación: {$reservation->property->title}";
        $item->quantity = 1;
        $item->unit_price = (float) $reservation->total_price;
        $preference->items = [$item];

        $preference->external_reference = (string) $reservation->id;

        $preference->back_urls = [
            'success' => route('reservation.payment.return'),
            'failure' => route('reservation.payment.return'),
            'pending' => route('reservation.payment.return'),
        ];

        // auto_return solo es seguro en HTTPS publico. En dev (http://*.test, localhost)
        // MP rechaza la preferencia y deja id en null. Solo lo activamos en URLs https.
        if (str_starts_with(url('/'), 'https://')) {
            $preference->auto_return = 'approved';
        }

        $payer = new Payer();
        if ($reservation->user?->email) {
            $payer->email = $reservation->user->email;
        }
        $preference->payer = $payer;

        $saved = $preference->save();

        if (!$saved || empty($preference->id)) {
            $error = $preference->error?->message ?? 'desconocido';
            $cause = json_encode($preference->error?->causes ?? []);
            throw new \RuntimeException("MercadoPago rechazó la preferencia: {$error}. Causes: {$cause}");
        }

        return $preference->id;
    }

    private function notifyConfirmation(Reservation $reservation): void
    {
        try {
            $reservation->user?->notify(new ReservationConfirmedGuest($reservation));
            $reservation->property?->user?->notify(new ReservationConfirmedHost($reservation));
        } catch (\Throwable $e) {
            Log::error('Reservation notification failed', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function myReservations()
    {
        $reservations = Reservation::with(['property', 'review'])
            ->where('user_id', Auth::id())
            ->orderByDesc('check_in_date')
            ->paginate(15);

        return view('my-reservations', compact('reservations'));
    }

    public function hostReservations()
    {
        $reservations = Reservation::with('property', 'user')
            ->whereIn('property_id', Property::where('user_id', Auth::id())->pluck('id'))
            ->orderByDesc('check_in_date')
            ->paginate(15);

        return view('host-reservations', compact('reservations'));
    }

    /**
     * Calendario para que el anfitrión vea y administre la disponibilidad
     * de una de sus propiedades.
     */
    public function hostCalendar(string $slug)
    {
        $property = Property::where('slug', $slug)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $rangeStart = now()->toDateString();
        $rangeEnd = now()->addMonths(6)->toDateString();
        $blockedDates = $this->availability->blockedDates($property, $rangeStart, $rangeEnd);

        $reservations = $property->reservations()
            ->blocking()
            ->where('check_out_date', '>=', $rangeStart)
            ->orderBy('check_in_date')
            ->with('user')
            ->get();

        return view('host-calendar', compact('property', 'blockedDates', 'reservations', 'rangeStart', 'rangeEnd'));
    }

    /**
     * Bloquear/liberar un rango de fechas (solo el anfitrión de la propiedad).
     * Body: { from: 'Y-m-d', to: 'Y-m-d', action: 'block'|'unblock' }
     */
    public function hostUpdateAvailability(string $slug, Request $request): JsonResponse
    {
        $property = Property::where('slug', $slug)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'action' => ['required', 'in:block,unblock'],
        ]);

        if ($data['action'] === 'block') {
            $this->availability->block($property, $data['from'], $data['to']);
        } else {
            $this->availability->unblock($property, $data['from'], $data['to']);
        }

        $blockedDates = $this->availability->blockedDates(
            $property,
            now()->toDateString(),
            now()->addMonths(6)->toDateString(),
        );

        return response()->json([
            'ok' => true,
            'blocked' => $blockedDates,
        ]);
    }

    /**
     * Crear review de una reservación completada.
     */
    public function storeReview(Reservation $reservation, Request $request)
    {
        $this->authorizeOwner($reservation);

        if ($reservation->status !== ReservationStatus::Completada) {
            return redirect()->back()->with('error', 'Solo puedes reseñar reservaciones completadas.');
        }

        if ($reservation->review()->exists()) {
            return redirect()->back()->with('error', 'Ya enviaste una reseña para esta estadía.');
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $reservation->review()->create([
            'property_id' => $reservation->property_id,
            'user_id' => $reservation->user_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ]);

        return redirect()->route('my.reservations')->with('success', '¡Gracias por tu reseña!');
    }
}
