<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Priority;
use App\Traits\ScopedMasterController;
use Illuminate\Http\Request;

class PriorityController extends Controller
{
    use ScopedMasterController;

    public function index()
    {
        $priorities = $this->isUserScoped()
            ? Priority::ownedByUser()->orderBy('level', 'desc')->get()
            : Priority::orderBy('level', 'desc')->get();

        $baseUrl = $this->isUserScoped() ? '/my-kra/priorities' : '/masters/priorities';
        return view('masters.priorities', compact('priorities', 'baseUrl'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'color_class' => 'required|string',
            'level'       => 'required|integer|min:1',
            'is_active'   => 'boolean',
        ]);
        $priority = Priority::create([...$validated, 'user_id' => $this->scopedUserId()]);
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
