<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePermisoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'   => 'required|string|max:100|unique:permissions,name',
            'modulo' => 'required|string|max:60',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'El nombre del permiso es obligatorio.',
            'name.unique'     => 'Ya existe ese permiso.',
            'modulo.required' => 'El módulo es obligatorio.',
        ];
    }

    // Construye el nombre final como "modulo.accion"
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => strtolower($this->modulo) . '.' . strtolower($this->accion),
        ]);
    }
}
