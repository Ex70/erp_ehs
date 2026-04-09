<?php

namespace App\Http\Controllers\Adquisiciones;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $request->validate(['nombre' => 'required|string|max:120|unique:clientes,nombre',
            'contacto' => 'nullable|string|max:100', 'correo' => 'nullable|email',
            'telefono' => 'nullable|string|max:30']);
        Cliente::create($request->only(['nombre','contacto','correo','telefono']) + ['activo' => true]);
        return redirect()->route('adquisiciones.catalogos.index')->with('success', 'Cliente agregado.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente) {
        $request->validate(['nombre' => "required|string|max:120|unique:clientes,nombre,{$cliente->id}",
            'contacto' => 'nullable|string|max:100', 'correo' => 'nullable|email',
            'telefono' => 'nullable|string|max:30', 'activo' => 'boolean']);
        $cliente->update($request->only(['nombre','contacto','correo','telefono']) + ['activo' => $request->boolean('activo')]);
        return redirect()->route('adquisiciones.catalogos.index')->with('success', 'Cliente actualizado.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente) {
        if ($cliente->requerimientos()->count() > 0)
            return redirect()->route('adquisiciones.catalogos.index')->with('error', 'Tiene requerimientos asociados.');
        $cliente->delete();
        return redirect()->route('adquisiciones.catalogos.index')->with('success', 'Cliente eliminado.');
    }
}
