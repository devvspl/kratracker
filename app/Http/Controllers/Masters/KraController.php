<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Kra;
use Illuminate\Http\Request;

class KraController extends Controller
{
    public function index()
    {
        $kras = Kra::withCount('subKras')->latest()->get();
        return view('masters.kras', compact('kras'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'total_weightage'  => 'required|numeric|min:0|max:100',
            'description'      => 'nullable|string',
            'is_active'        => 'boolean',
        ]);
        $kra = Kra::create($validated);
        return response()->json(['success' => true, 'message' => 'KRA created successfully', 'data' => $kra]);
    }

    public function update(Request $request, Kra $kra)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'total_weightage'  => 'required|numeric|min:0|max:100',
            'description'      => 'nullable|string',
            'is_active'        => 'boolean',
        ]);
        $kra->update($validated);
        return response()->json(['success' => true, 'message' => 'KRA updated successfully', 'data' => $kra]);
    }

    public function destroy(Kra $kra)
    {
        $kra->delete();
        return response()->json(['success' => true, 'message' => 'KRA deleted successfully']);
    }
}
