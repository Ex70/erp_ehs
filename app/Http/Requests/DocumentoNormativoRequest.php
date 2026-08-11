<?php

namespace App\Http\Requests;

use App\Models\DocumentoNormativo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentoNormativoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización real la resuelve el middleware `can:` de la ruta.
        return true;
    }

    public function rules(): array
    {
        $config = config('normatividad.archivo');

        return [
            'categoria'   => ['required', 'string', Rule::in(DocumentoNormativo::clavesCategorias())],
            'titulo'      => ['required', 'string', 'max:255'],
            'version'     => ['nullable', 'string', 'max:30'],
            'vigencia'    => ['nullable', 'date'],
            'responsable' => ['nullable', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'activo'      => ['nullable', 'boolean'],
            'archivo'     => [
                $this->esCreacion() ? 'nullable' : 'nullable',
                'file',
                'mimes:' . $config['mimes'],
                'max:' . $config['max_kb'],
            ],
            'notas_version' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'categoria'     => 'categoría',
            'titulo'        => 'título',
            'version'       => 'versión',
            'vigencia'      => 'fecha de vigencia',
            'responsable'   => 'responsable',
            'descripcion'   => 'descripción',
            'archivo'       => 'archivo del documento',
            'notas_version' => 'notas de la versión',
        ];
    }

    public function messages(): array
    {
        return [
            'archivo.mimes' => 'El archivo debe ser PDF, Word, Excel o PowerPoint.',
            'archivo.max'   => 'El archivo no debe superar los :max KB.',
        ];
    }

    private function esCreacion(): bool
    {
        return $this->isMethod('POST');
    }
}
