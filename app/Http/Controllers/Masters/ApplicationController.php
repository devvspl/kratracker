<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Traits\ScopedMasterController;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    use ScopedMasterController;

    public function index()
    {
        $applications = $this->isUserScoped()
            ? Application::ownedByUser()->latest()->get()
            : Application::latest()->get();

        $baseUrl = $this->isUserScoped() ? '/my-kra/applications' : '/masters/applications';

        return view('masters.applications', compact('applications', 'baseUrl'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'tech_stack'  => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);
        $application = Application::create([...$validated, 'user_id' => $this->scopedUserId()]);
        return response()->json(['success' => true, 'message' => 'Application created successfully', 'data' => $application]);
    }

    public function update(Request $request, Application $application)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'tech_stack'  => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);
        $application->update($validated);
        return response()->json(['success' => true, 'message' => 'Application updated successfully', 'data' => $application]);
    }

    public function destroy(Application $application)
    {
        $application->delete();
        return response()->json(['success' => true, 'message' => 'Application deleted successfully']);
    }
}
