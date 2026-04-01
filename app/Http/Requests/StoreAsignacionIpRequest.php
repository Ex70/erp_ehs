<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAsignacionIpRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_id'          => 'nullable|exists:users,id',
            'nombre'           => 'required|string|max:100',
            'direccion_ip'     => [
                'required',
                'ip',
                'unique:asignaciones_ip,direccion_ip',
            ],
            'direccion_mac'    => [
                'required',
                'regex:/^([0-9a-fA-F]{2}:){5}[0-9a-fA-F]{2}$/',
                'unique:asignaciones_ip,direccion_mac',
            ],
            'dispositivo'      => 'required|string|max:50',
            'marca'            => 'required|string|max:60',
            'modelo'           => 'required|string|max:80',
            'numero_serie'     => 'required|string|max:60|unique:asignaciones_ip,numero_serie',
            'area'             => 'required|string|max:100',
            'puesto'           => 'required|string|max:100',
            'fecha_asignacion' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'           => 'El nombre del usuario es obligatorio.',
            'direccion_ip.required'     => 'La dirección IP es obligatoria.',
            'direccion_ip.ip'           => 'El formato de IP no es válido. Ej: 192.168.0.111',
            'direccion_ip.unique'       => 'Esta dirección IP ya está registrada en el sistema.',
            'direccion_mac.required'    => 'La dirección MAC es obligatoria.',
            'direccion_mac.regex'       => 'Formato MAC inválido. Ej: 00:1e:c2:9e:28:6b',
            'direccion_mac.unique'      => 'Esta dirección MAC ya está registrada.',
            'dispositivo.required'      => 'El tipo de dispositivo es obligatorio.',
            'marca.required'            => 'La marca es obligatoria.',
            'modelo.required'           => 'El modelo es obligatorio.',
            'numero_serie.required'     => 'El número de serie es obligatorio.',
            'numero_serie.unique'       => 'Este número de serie ya está registrado.',
            'area.required'             => 'El área es obligatoria.',
            'puesto.required'           => 'El puesto es obligatorio.',
        ];
    }
}