<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index()
    {
        $applications = Application::latest()->get();
        return view('masters.applications', compact('applications'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'tech_stack'  => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);
        $application = Application::create($validated);
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
