@extends('layouts.layout')

@section('title', 'BienesCorp - Reservaciones')
@section('description', 'Administración de Bienes Inmuebles')
@section('og:title', 'BienesCorp - Administración de Bienes Inmuebles')
@section('og:description', 'Administración de Bienes Inmuebles')
@section('og:image', asset('BienesCorpLogo.png') )
@section('og:url', url()->current())

@section('content')
    <div class="container">
        <div class="filters">
            <div class="input-group date" id="datepicker">
                <input class="datepicker" width="276" />
              </div>
        </div>
        <div class="row">
            @foreach($properties as $property)
            <div class="col-xs-12 col-md-6 col-lg-4">
                <div class="card mb-5 border-1 bg-white position-relative">
                    <div class="position-absolute top-0 end-0 m-3">
                        <a class="addToFavorite"><i class="fa fa-heart fs-3"></i></a>
                    </div>
                    <a href="{{ route('reservation.show', $property->slug) }}">
                        <img src="{{ $property->photo_main }}" class="card-img-top" alt="{{ $property->title }}">
                    </a>

                    <div class="p-3">
                        <div class="row">
                            <div class="col-xs-12 col-md-9">
                                <h5 class="card-title"><strong>{{ $property->title }}</strong></h5>
                            </div>
                            <div class="col-xs-12 col-md-3">
                                <i class="fa fa-star" aria-hidden="true"></i> 4.5 (20)
                            </div>
                            <p class="text-muted mb-1">{{ Str::words($property->description, 10, '...') }}</p>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span><strong class="fs-5">${{ number_format($property->price_per_night ?? 0, 0) }}</strong> por noche</span>
                                <a href="{{ route('reservation.show', $property->slug) }}" class="btn btn-sm btn-primary">Reservar</a>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
            @endforeach
        </div>
    </div>


<script>


    $(document).ready(function() {

        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            startDate: new Date(),
            todayHighlight: true,
            autoclose: true,
            language: 'es',

        });

        $.fn.datepicker.dates['es'] = {
            days: ["Domingo", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
            daysShort: ["Do", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
            daysMin: ["Do", "Mo", "Tu", "We", "Th", "Fr", "Sa"],
            months: ["Enero", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
            monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            today: "Hoy",
            clear: "Limpiar",
            format: "yyyy-mm-dd",
            titleFormat: "MM yyyy", /* Leverages same syntax as 'format' */
            weekStart: 0
        };
    });



</script>

@endsection
