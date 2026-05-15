@props(['image', 'eyebrow' => null, 'subtitle' => null])

<section class="page-hero">
    <div class="page-hero__bg" style="background-image: url('{{ asset($image) }}');"></div>
    <div class="page-hero__overlay"></div>
    <div class="page-hero__content">
        @if($eyebrow)
            <span class="page-hero__eyebrow">{{ $eyebrow }}</span>
        @endif
        <h1 class="page-hero__title">{{ $slot }}</h1>
        @if($subtitle)
            <p class="page-hero__subtitle">{{ $subtitle }}</p>
        @endif
    </div>
</section>
