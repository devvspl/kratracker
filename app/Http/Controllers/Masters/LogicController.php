<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Logic;
use App\Traits\ScopedMasterController;
use Illuminate\Http\Request;

class LogicController extends Controller
{
    use ScopedMasterController;

    public function index()
    {
        $logics = $this->isUserScoped()
            ? Logic::withCount('subKras')->ownedByUser()->latest()->get()
            : Logic::withCount('subKras')->latest()->get();

        $baseUrl = $this->isUserScoped() ? '/my-kra/logics' : '/masters/logics';
        return view('masters.logics', compact('logics', 'baseUrl'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'scoring_type' => 'required|in:proportional,binary',
        ]);
        $logic = Logic::create([...$validated, 'user_id' => $this->scopedUserId()]);
        return response()->json(['success' => true, 'message' => 'Logic created successfully', 'data' => $logic]);
    }

    public function update(Request $request, Logic $logic)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'scoring_type' => 'required|in:proportional,binary',
        ]);
        $logic->update($validated);
        return response()->json(['success' => true, 'message' => 'Logic updated successfully', 'data' => $logic]);
    }

    public function destroy(Logic $logic)
    {
        if ($logic->subKras()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete logic with linked Sub-KRAs'], 422);
        }
        $logic->delete();
        return response()->json(['success' => true, 'message' => 'Logic deleted successfully']);
    }
}
