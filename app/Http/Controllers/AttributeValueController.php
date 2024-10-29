<?php

namespace App\Http\Controllers;

use App\Models\AttributeValue;
use App\Models\Attribute;
use Illuminate\Http\Request;

class AttributeValueController extends Controller
{
    public function index($attributeId)
    {
        // Fetch all attribute values for the given attribute ID
        $attribute = Attribute::findOrFail($attributeId);
        $values = $attribute->values; // Assuming the relationship is defined in the Attribute model

        return response()->json($values, 200);
    }

    public function show($attributeId, $valueId)
    {
        // Fetch a specific attribute value for the given attribute ID and value ID
        $attribute = Attribute::findOrFail($attributeId);
        $attributeValue = $attribute->values()->findOrFail($valueId);

        return response()->json($attributeValue, 200);
    }

    public function store(Request $request, $attributeId)
    {
        $attribute = Attribute::findOrFail($attributeId);

        $validated = $request->validate([
            'value' => 'required|string|max:255',
        ]);

        $attributeValue = $attribute->values()->create([
            'value' => $validated['value'],
        ]);

        return response()->json($attributeValue, 201);
    }

    public function update(Request $request, $attributeId, $valueId)
    {
        $attribute = Attribute::findOrFail($attributeId);
        $attributeValue = $attribute->values()->findOrFail($valueId);

        $validated = $request->validate([
            'value' => 'required|string|max:255',
        ]);

        $attributeValue->update([
            'value' => $validated['value'],
        ]);

        return response()->json($attributeValue, 200);
    }

    public function destroy($attributeId, $valueId)
    {
        $attribute = Attribute::findOrFail($attributeId);
        $attributeValue = $attribute->values()->findOrFail($valueId);

        $attributeValue->delete();

        return response()->json(['message' => 'Attribute value deleted successfully'], 200);
    }
}
