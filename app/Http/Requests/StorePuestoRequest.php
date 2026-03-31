<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePuestoRequest extends FormRequest
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
            'nombre'      => 'required|string|max:100|unique:puestos,nombre',
            'descripcion' => 'nullable|string|max:255',
            'activo'      => 'boolean',
        ];
    }

    public function messages(): array{
        return [
            'nombre.required' => 'El nombre del puesto es obligatorio.',
            'nombre.unique'   => 'Ya existe un puesto con ese nombre.',
            'nombre.max'      => 'El nombre no debe superar 100 caracteres.',
        ];
    }
}
