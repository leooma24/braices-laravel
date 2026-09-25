<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    /**
     * Bandeja de prospectos del asesor (el admin ve los de todos).
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $estado = $request->query('estado');

        $query = Lead::visibleTo($user)
            ->with(['property', 'user'])
            ->orderByRaw("CASE WHEN status = 'nuevo' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at');

        if ($estado && array_key_exists($estado, Lead::ESTADOS)) {
            $query->where('status', $estado);
        }

        $leads = $query->paginate(20)->withQueryString();

        $pendientes = Lead::visibleTo($user)->pending()->count();

        return view('leads', [
            'leads' => $leads,
            'pendientes' => $pendientes,
            'estadoActual' => $estado,
            'estados' => Lead::ESTADOS,
        ]);
    }

    /**
     * Cambia el estado del prospecto y deja nota de seguimiento.
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();

        $lead = Lead::visibleTo($user)->findOrFail($id);

        $request->validate([
            'status' => 'required|in:'.implode(',', array_keys(Lead::ESTADOS)),
            'notes' => 'nullable|string|max:2000',
        ]);

        $lead->status = $request->input('status');

        if ($request->filled('notes')) {
            $lead->notes = $request->input('notes');
        }

        // La primera vez que sale de "nuevo" se guarda cuándo se contestó.
        if ($lead->status !== 'nuevo' && $lead->responded_at === null) {
            $lead->responded_at = now();
        }

        $lead->save();

        return redirect()->back()->with('success', 'Prospecto actualizado');
    }
}
