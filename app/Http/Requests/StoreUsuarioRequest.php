<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
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
            'name'      => 'required|string|max:100',
            'username'  => 'required|string|max:50|unique:users,username',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
            'puesto_id' => 'required|exists:puestos,id',
            'role'      => 'required|exists:roles,name',
            'avatar'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'activo'    => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'El nombre es obligatorio.',
            'username.required'  => 'El usuario es obligatorio.',
            'username.unique'    => 'Ese nombre de usuario ya está en uso.',
            'email.required'     => 'El correo es obligatorio.',
            'email.unique'       => 'Ese correo ya está registrado.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'puesto_id.required' => 'El puesto es obligatorio.',
            'puesto_id.exists'   => 'El puesto seleccionado no existe.',
            'role.required'      => 'El rol es obligatorio.',
            'role.exists'        => 'El rol seleccionado no es válido.',
            'avatar.image'       => 'El archivo debe ser una imagen.',
            'avatar.max'         => 'La imagen no debe superar 2MB.',
        ];
    }
}
