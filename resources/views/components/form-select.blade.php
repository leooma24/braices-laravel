<div class="form-floating mb-3">
    <select
        class="form-select @error($name) {{ 'is-invalid' }} @enderror"
        id="{{ $name }}"
        name="{{ $name }}"
        aria-describedby="invalid{{ $name }}"
        >
        @foreach ($options as $option)
            <option value="{{ $option[$textId] }}" {{ $prop }}="{{ $option[$prop] ?? '' }}" {{ $option[$textId] == $value ? 'selected' : '' }}>{{ $option[$textLabel] }}</option>
        @endforeach
    </select>
    <label for="{{ $name }}">{{ $label }}</label>
    @error($name)
        <div id="invalid{{ $name }}" class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
