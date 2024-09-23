<div class="form-floating mb-3">
    <input name="{{ $name }}" type="{{ $type ?? 'text' }}" class="form-control @error($name) {{ 'is-invalid' }} @enderror" id="{{ $name }}"
        value="{{ $value ?? old($name) }}"
        aria-describedby="invalid{{ $name }}"
        placeholder="{{ $label }}">
    <label for="{{ $name }}">{{ $label }}</label>
      @error($name)
          <div id="invalid{{ $name }}" class="invalid-feedback">
              {{ $message }}
          </div>
      @enderror
</div>
