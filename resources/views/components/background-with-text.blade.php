<section class="section-background-with-text py-5">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-12 col-md-6">
                <h2 class="mb-3">Encuentra la propiedad ideal con nuestras opciones de venta y renta</h2>
                <p class="text-muted-2 mb-3">
                    Ya sea que busques comprar tu hogar soñado o alquilar un espacio cómodo y funcional,
                    ofrecemos una amplia gama de bienes inmobiliarios adaptados a tus necesidades.
                </p>
                <p class="text-muted-2 mb-4">
                    Nuestro equipo de expertos te acompaña en cada paso del proceso, asegurando una experiencia
                    rápida y sin complicaciones.
                </p>
                <a href="/propiedades" class="btn btn-primary btn-lg">
                    Ir a Propiedades <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>

            <div class="col-12 col-md-6 text-center">
                <div class="background-image-wrap">
                    <img src="{{ asset('roberto-nickson-smJ6XsYy8gA-unsplash.jpg') }}" alt="" class="background-image">
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .section-background-with-text { background: var(--color-surface); margin: 4rem 0 0; }
    .background-image-wrap {
        border-radius: var(--radius-xl);
        overflow: hidden;
        box-shadow: var(--shadow-lg);
        aspect-ratio: 4 / 3;
        max-width: 520px;
        margin-inline: auto;
    }
    .background-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
</style>
