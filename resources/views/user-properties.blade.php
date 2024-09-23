<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

<div class="user-properties">
    <div class="container">
        <div class="row">
            @foreach($properties as $key => $property)
                <div class="col">
                    <div class="card">
                        <img src="{{ $property->photo_main }}" class="card-img-top" alt="...">
                        <div class="card-body">
                            <h5 class="card-title mb-0">{{ $property->title }}</h5>
                            <p class="card-text mb-2 text-secondary text-truncate">{{ $property->address }}</p>

                            <div class="card-characteristics">
                                <span class="me-3"> <i class="fas fa-bed" color="orange"></i> {{ $property->bedrooms }}
                                    Recámaras</span>
                                <span class="me-3"> <i class="fas fa-camera" color="orange"></i> {{ $property->bathrooms }}
                                    Baños</span>
                                <span> <i class="fas fa-square" color="orange"></i> {{ number_format($property->square_feet) }}
                                    m²</span>
                            </div>

                            <hr />
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="card-price">${{ number_format($property->price) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script defer src="https://use.fontawesome.com/releases/v5.15.4/js/solid.js"
        integrity="sha384-/BxOvRagtVDn9dJ+JGCtcofNXgQO/CCCVKdMfL115s3gOgQxWaX/tSq5V8dRgsbc" crossorigin="anonymous">
    </script>
    <script defer src="https://use.fontawesome.com/releases/v5.15.4/js/fontawesome.js"
        integrity="sha384-dPBGbj4Uoy1OOpM4+aRGfAOc0W37JkROT+3uynUgTHZCHZNMHfGXsmmvYTffZjYO" crossorigin="anonymous">
    </script>

<style>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
    font-family: "Montserrat", sans-serif;
}
.row {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
}
.card {
    border: 1px solid #e1e1e1;
    border-radius: 0.5rem;
    overflow: hidden;
}
.card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}
.card-title {
    font-size: 1.5rem;
    font-weight: bold;
    margin: 0.5rem 0;
    color: blue;
}
.card-text {
    font-size: 1rem;
    color: #333;
    margin: 0 0 1rem;

}
.card-price {
    font-size: 1.5rem;
    font-weight: bold;
    color: blue;
    margin: 0;
}
.card-body {
    padding: 1rem;
}

.card-characteristics {
    display: flex;
    justify-content: space-between;
}

.btn {
    padding: 0.5rem 1rem;
    font-size: 1rem;
    border-radius: 0.5rem;
    background-color: blue;
    color: white;
    text-decoration: none;
    display: inline-block;
    margin-top: 1rem;
}
</style>
