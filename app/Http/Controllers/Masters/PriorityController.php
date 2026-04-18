<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Priority;
use Illuminate\Http\Request;

class PriorityController extends Controller
{
    public function index()
    {
        $priorities = Priority::orderBy('level', 'desc')->get();
        return view('masters.priorities', compact('priorities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'color_class' => 'required|string',
            'level'       => 'required|integer|min:1',
            'is_active'   => 'boolean',
        ]);
        $priority = Priority::create($validated);
        return response()->json(['success' => true, 'message' => 'Priority created successfully', 'data' => $priority]);
    }

    public function update(Request $request, Priority $priority)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'color_class' => 'required|string',
            'level'       => 'required|integer|min:1',
            'is_active'   => 'boolean',
        ]);
        $priority->update($validated);
        return response()->json(['success' => true, 'message' => 'Priority updated successfully', 'data' => $priority]);
    }

    public function destroy(Priority $priority)
    {
        $priority->delete();
        return response()->json(['success' => true, 'message' => 'Priority deleted successfully']);
    }
}
