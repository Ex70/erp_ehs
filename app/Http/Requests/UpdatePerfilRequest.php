<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePerfilRequest extends FormRequest
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
        $userId = Auth::id();

        return [
            'name'     => 'required|string|max:100',
            'username' => "required|string|max:50|unique:users,username,{$userId}",
            'email'    => "required|email|unique:users,email,{$userId}",
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'El nombre es obligatorio.',
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.unique'   => 'Ese nombre de usuario ya está en uso.',
            'email.required'    => 'El correo es obligatorio.',
            'email.unique'      => 'Ese correo ya está registrado.',
        ];
    }
}
