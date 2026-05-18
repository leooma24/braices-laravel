@extends('layouts.layout')

@section('title', 'BienesCorp - Promociones')
@section('content')

    <x-admin-header />

    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1>Promociones</h1>
                <p>Configura descuentos por rangos de venta (ej. 50% off primeras 20, 30% off siguientes 20).</p>
            </div>
            <button type="button" class="btn btn-accent btn-lg" data-bs-toggle="modal" data-bs-target="#promoModal">
                <i class="fas fa-plus me-2"></i>Nueva promoción
            </button>
        </div>

        @foreach($packages as $package)
            <div class="dashboard-card">
                <div class="dashboard-card__header">
                    <h2>
                        <i class="fas fa-box-open text-primary me-2"></i>{{ $package->name }}
                    </h2>
                    <small class="text-muted-2">
                        Precio mensual: <strong>${{ number_format($package->price, 0) }}</strong>
                        · Ventas totales: <strong>{{ $package->sales_count }}</strong>
                    </small>
                </div>
                <div class="dashboard-card__body p-0">
                    <table class="table mc-table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Etiqueta</th>
                                <th style="width: 120px;">Descuento</th>
                                <th style="width: 180px;">Aplica a ventas</th>
                                <th style="width: 100px;">Activa</th>
                                <th class="text-end" style="width: 200px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($package->promotions as $promo)
                                <tr>
                                    <form action="{{ route('admin.promotions.update', $promo) }}" method="POST">
                                        @csrf @method('PUT')
                                        <td>
                                            <input type="text" name="label" value="{{ $promo->label }}" class="form-control form-control-sm" placeholder="Ej. Lanzamiento">
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="number" name="discount_percent" value="{{ $promo->discount_percent }}" min="1" max="100" class="form-control" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-1">
                                                <input type="number" name="from_count" value="{{ $promo->from_count }}" min="1" class="form-control form-control-sm" style="width: 70px;" required>
                                                <span>–</span>
                                                <input type="number" name="to_count" value="{{ $promo->to_count }}" min="1" class="form-control form-control-sm" style="width: 70px;" required>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="is_active" value="0">
                                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $promo->is_active ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-save"></i> Guardar
                                            </button>
                                    </form>
                                    <form action="{{ route('admin.promotions.destroy', $promo) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Eliminar esta promoción?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                        </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted-2">
                                        Sin promociones configuradas para este paquete.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Modal: nueva promoción --}}
    <div class="modal fade" id="promoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.promotions.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-bolt text-accent me-2"></i>Nueva promoción</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Paquete</label>
                            <select name="package_id" class="form-select" required>
                                @foreach($packages as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} (${{ number_format($p->price, 0) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Etiqueta (opcional)</label>
                            <input type="text" name="label" class="form-control" placeholder="Ej. Oferta de lanzamiento">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label class="form-label">% descuento</label>
                                <div class="input-group">
                                    <input type="number" name="discount_percent" min="1" max="100" class="form-control" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <label class="form-label">Desde venta #</label>
                                <input type="number" name="from_count" min="1" value="1" class="form-control" required>
                            </div>
                            <div class="col-4">
                                <label class="form-label">Hasta venta #</label>
                                <input type="number" name="to_count" min="1" value="20" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active_new" checked>
                            <label class="form-check-label" for="is_active_new">Activa</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Crear promoción
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
