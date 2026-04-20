<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Kra;
use App\Models\Logic;
use App\Models\SubKra;
use App\Traits\ScopedMasterController;
use Illuminate\Http\Request;

class SubKraController extends Controller
{
    use ScopedMasterController;

    public function index()
    {
        $subKras = SubKra::with(['kra', 'logic'])->latest()->get();
        $kras    = $this->isUserScoped()
            ? Kra::forCurrentUser()->where('is_active', true)->get()
            : Kra::where('is_active', true)->get();
        $logics  = $this->isUserScoped()
            ? Logic::forCurrentUser()->get()
            : Logic::all();

        return view('masters.sub-kras', compact('subKras', 'kras', 'logics'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kra_id'        => 'required|exists:kras,id',
            'name'          => 'required|string|max:255',
            'weightage'     => 'required|numeric|min:0|max:100',
            'unit'          => 'required|string',
            'measure_type'  => 'nullable|string',
            'logic_id'      => 'required|exists:logics,id',
            'review_period' => 'required|in:Monthly,Quarterly,Annually',
            'description'   => 'nullable|string',
            'is_active'     => 'boolean',
        ]);
        $subKra = SubKra::create($validated);
        return response()->json(['success' => true, 'message' => 'Sub-KRA created successfully', 'data' => $subKra->load(['kra', 'logic'])]);
    }

    public function update(Request $request, SubKra $subKra)
    {
        $validated = $request->validate([
            'kra_id'        => 'required|exists:kras,id',
            'name'          => 'required|string|max:255',
            'weightage'     => 'required|numeric|min:0|max:100',
            'unit'          => 'required|string',
            'measure_type'  => 'nullable|string',
            'logic_id'      => 'required|exists:logics,id',
            'review_period' => 'required|in:Monthly,Quarterly,Annually',
            'description'   => 'nullable|string',
            'is_active'     => 'boolean',
        ]);
        $subKra->update($validated);
        return response()->json(['success' => true, 'message' => 'Sub-KRA updated successfully', 'data' => $subKra->load(['kra', 'logic'])]);
    }

    public function destroy(SubKra $subKra)
    {
        $subKra->delete();
        return response()->json(['success' => true, 'message' => 'Sub-KRA deleted successfully']);
    }
}
