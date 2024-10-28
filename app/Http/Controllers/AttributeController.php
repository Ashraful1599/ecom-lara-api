<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::with('values')->get();
        return response()->json($attributes, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
        ]);

        $attribute = Attribute::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? Str::slug($validated['name']), // Generate slug if not provided
        ]);

        return response()->json($attribute, 201);
    }

    public function update(Request $request, $id)
    {
        $attribute = Attribute::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
        ]);

        $attribute->update([
            'name' => $validated['name'],
           // 'slug' => $validated['slug'] ?? Str::slug($validated['name']), // Update slug if provided or regenerate
        ]);

        return response()->json($attribute, 200);
    }

    public function destroy($id)
    {
        $attribute = Attribute::findOrFail($id);
        $attribute->delete();

        return response()->json(['message' => 'Attribute deleted successfully'], 200);
    }
}
