<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Toda;
use Illuminate\Http\Request;

class AdminTodaController extends Controller
{
    /**
     * List all TODA terminals (admin).
     */
    public function index()
    {
        return response()->json(Toda::orderBy('name')->get());
    }

    /**
     * Create a new TODA terminal from a placed pinpoint.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'serves' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'units' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $toda = Toda::create(array_merge($validated, [
            'units' => (int) ($validated['units'] ?? 0),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]));

        return response()->json($toda, 201);
    }

    /**
     * Update an existing TODA terminal (move pinpoint or edit details).
     */
    public function update(Request $request, Toda $toda)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'lat' => 'sometimes|required|numeric|between:-90,90',
            'lng' => 'sometimes|required|numeric|between:-180,180',
            'serves' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'units' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $toda->update($validated);

        return response()->json($toda);
    }

    /**
     * Delete a TODA terminal and its pinpoint.
     */
    public function destroy(Toda $toda)
    {
        $toda->delete();

        return response()->json(['message' => 'TODA terminal deleted.']);
    }
}