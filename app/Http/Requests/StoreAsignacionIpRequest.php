<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAsignacionIpRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // user_id ahora es obligatorio
            'user_id'          => 'required|exists:users,id',

            // dispositivo y marca ahora son FK
            'dispositivo_id'   => 'required|exists:dispositivos,id',
            'marca_id'         => 'required|exists:marcas,id',

            'direccion_ip'     => [
                'required', 'ip',
                'unique:asignaciones_ip,direccion_ip',
            ],
            'direccion_mac'    => [
                'required',
                'regex:/^([0-9a-fA-F]{2}:){5}[0-9a-fA-F]{2}$/',
                'unique:asignaciones_ip,direccion_mac',
            ],
            'modelo'           => 'required|string|max:80',
            'numero_serie'     => 'required|string|max:60|unique:asignaciones_ip,numero_serie',
            'area'             => 'required|string|max:100',
            'fecha_asignacion' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required'        => 'Debes seleccionar un usuario.',
            'user_id.exists'          => 'El usuario seleccionado no existe.',
            'dispositivo_id.required' => 'Selecciona un tipo de dispositivo.',
            'dispositivo_id.exists'   => 'El dispositivo seleccionado no es válido.',
            'marca_id.required'       => 'Selecciona una marca.',
            'marca_id.exists'         => 'La marca seleccionada no es válida.',
            'direccion_ip.required'   => 'La dirección IP es obligatoria.',
            'direccion_ip.ip'         => 'Formato de IP inválido. Ej: 192.168.0.111',
            'direccion_ip.unique'     => 'Esta IP ya está registrada en el sistema.',
            'direccion_mac.required'  => 'La dirección MAC es obligatoria.',
            'direccion_mac.regex'     => 'Formato MAC inválido. Ej: 00:1e:c2:9e:28:6b',
            'direccion_mac.unique'    => 'Esta MAC ya está registrada.',
            'modelo.required'         => 'El modelo es obligatorio.',
            'numero_serie.required'   => 'El número de serie es obligatorio.',
            'numero_serie.unique'     => 'Este número de serie ya está registrado.',
            'area.required'           => 'El área es obligatoria.',
        ];
    }
}