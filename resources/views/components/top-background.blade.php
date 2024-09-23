<div class="card text-bg-dark border-black border-0 rounded-0 top-background">
    <img src="{{ asset($image) }}" class="mc-top-background" alt="Test">

    <div class="card-img-overlay d-flex flex-column justify-content-center align-items-center">
        <h1><strong>{{ $slot }}</strong></h1>
    </div>
</div>

<style>
.mc-top-background {
    max-height: 300px;
    object-fit: cover;
}
.top-background::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.1);
}

.top-background h1 {
    text-shadow: 2px 2px 4px #000000;
}
</style>
