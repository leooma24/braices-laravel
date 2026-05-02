<div class="top-background position-relative overflow-hidden">
    <img src="{{ asset($image) }}" class="mc-top-background w-100" alt="">
    <div class="top-background-overlay"></div>
    <div class="top-background-content text-center text-white px-3">
        <h1 class="display-4 fw-bold mb-0">{{ $slot }}</h1>
    </div>
</div>

<style>
    .top-background {
        height: clamp(220px, 32vh, 340px);
    }
    .mc-top-background {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: absolute;
        inset: 0;
    }
    .top-background-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.35) 0%, rgba(15, 23, 42, 0.55) 100%);
    }
    .top-background-content {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }
    .top-background h1 {
        color: #fff !important;
        letter-spacing: -0.02em;
    }
</style>
