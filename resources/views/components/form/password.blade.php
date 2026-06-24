@props([
    'name'         => 'password',
    'id'           => null,
    'label'        => 'Contraseña',
    'value'        => '',
    'required'     => false,
    'autocomplete' => 'current-password',
    'placeholder'  => null,
])

@php $id = $id ?? $name; @endphp

<div class="form-group mb-3">
    @if($label)
        <label for="{{ $id }}">{{ $label }}</label>
    @endif

    <div class="input-group @error($name) has-validation @enderror">
        <input type="password"
               id="{{ $id }}"
               name="{{ $name }}"
               value="{{ $value }}"
               autocomplete="{{ $autocomplete }}"
               placeholder="{{ $placeholder ?? $label }}"
               {{ $required ? 'required' : '' }}
               {{ $attributes->merge(['class' => 'form-control' . ($errors->has($name) ? ' is-invalid' : '')]) }}>

        <span class="input-group-text password-toggle"
              role="button"
              tabindex="-1"
              data-password-toggle="#{{ $id }}"
              aria-label="Mostrar contraseña">
            <i class="fas fa-eye"></i>
        </span>
    </div>

    @error($name)
        <span class="text-danger small d-block mt-1">{{ $message }}</span>
    @enderror
</div>