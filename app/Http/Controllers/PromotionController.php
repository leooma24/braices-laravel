<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackagePromotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index()
    {
        $packages = Package::with(['promotions' => fn ($q) => $q->orderBy('from_count')])
            ->orderBy('price')
            ->get();

        return view('admin.promotions.index', compact('packages'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'label' => 'nullable|string|max:120',
            'discount_percent' => 'required|integer|min:1|max:100',
            'from_count' => 'required|integer|min:1',
            'to_count' => 'required|integer|gte:from_count',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? true);
        PackagePromotion::create($data);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promoción creada.');
    }

    public function update(Request $request, PackagePromotion $promotion)
    {
        $data = $request->validate([
            'label' => 'nullable|string|max:120',
            'discount_percent' => 'required|integer|min:1|max:100',
            'from_count' => 'required|integer|min:1',
            'to_count' => 'required|integer|gte:from_count',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $promotion->update($data);

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promoción actualizada.');
    }

    public function destroy(PackagePromotion $promotion)
    {
        $promotion->delete();
        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promoción eliminada.');
    }
}
