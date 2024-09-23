<div id="mc-carousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        @if ($image)
        <div class="carousel-item active">
            <img src="{{ $image }}" class="d-block w-100" alt="...">
        </div>
        @endif
        @if ($images)
            @foreach ($images as $index => $img)
            <div class="carousel-item {{ !$image && $index === 0 ? 'active' : ''}} ">
                <img src="{{ $img->photo ?? $img->image_path }}" class="d-block w-100" alt="...">

                @if($img->title)
                <div class="carousel-caption d-flex flex-column justify-content-start align-items-start mb-0">
                    <h1>{{ $img->title }}</h1>
                    <p>{{ $img->subtitle }}</p>
                </div>
                @endif
            </div>
            @endforeach
        @endif
        @if(!$image && !$images)
        <div class="carousel-item active">
            <img src="https://via.placeholder.com/1600x550" class="d-block w-100" alt="...">
        </div>
        @endif
    <!--
      <div class="carousel-item">
        <img src="https://via.placeholder.com/1600x550" class="d-block w-100" alt="...">
      </div>
    -->
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#mc-carousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#mc-carousel" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Next</span>
    </button>
  </div>

  <style>
    .carousel-caption {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 80%;
        transform: translate(-50%, -50%);
        color: white;
    }
    .carousel-caption h1 {
        font-weight: 700;
        font-size: 4.5rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, .6);
    }
    .carousel-caption p {
        font-size: 2rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, .6);
    }
  </style>
