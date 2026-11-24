<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TypeCommerce;
use App\Models\UniteCommerciale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TypeCommerceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fonctionnalité désactivée - redirection vers page d'information
        return view('types-commerce.disabled');
    }

        /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Fonctionnalité désactivée - redirection vers page d'information
        return view('types-commerce.disabled');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Fonctionnalité désactivée - redirection vers page d'information
        return redirect()->route('types-commerce.index')
            ->with('warning', 'La gestion des types de commerce a été désactivée.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TypeCommerce $typeCommerce)
    {
        // Fonctionnalité désactivée - redirection vers page d'information
        return view('types-commerce.disabled');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TypeCommerce $typeCommerce)
    {
        // Fonctionnalité désactivée - redirection vers page d'information
        return view('types-commerce.disabled');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TypeCommerce $typeCommerce)
    {
        // Fonctionnalité désactivée - redirection vers page d'information
        return redirect()->route('types-commerce.index')
            ->with('warning', 'La gestion des types de commerce a été désactivée.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TypeCommerce $typeCommerce)
    {
        // Fonctionnalité désactivée - redirection vers page d'information
        return redirect()->route('types-commerce.index')
            ->with('warning', 'La gestion des types de commerce a été désactivée.');
    }
}
