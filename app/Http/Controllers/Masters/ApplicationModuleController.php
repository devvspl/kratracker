<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationModule;
use Illuminate\Http\Request;

class ApplicationModuleController extends Controller
{
    public function index()
    {
        $modules      = ApplicationModule::with('application')->latest()->get();
        $applications = Application::where('is_active', true)->orderBy('name')->get();
        return view('masters.application-modules', compact('modules', 'applications'));
    }

    /**
     * API: return modules for a given application_id (or global ones if null).
     */
    public function byApplication(Request $request)
    {
        $appId = $request->query('application_id');

        $query = ApplicationModule::where('is_active', true)->orderBy('name');

        if ($appId) {
            // Return app-specific modules + global (null) modules
            $query->where(function ($q) use ($appId) {
                $q->where('application_id', $appId)
                  ->orWhereNull('application_id');
            });
        } else {
            $query->whereNull('application_id');
        }

        return response()->json($query->get(['id', 'name', 'application_id']));
    }

    /**
     * API: create a module on-the-fly from TomSelect "create" option.
     */
    public function storeApi(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'application_id' => 'nullable|exists:applications,id',
        ]);

        $module = ApplicationModule::firstOrCreate(
            [
                'name'           => $validated['name'],
                'application_id' => $validated['application_id'] ?? null,
            ],
            ['is_active' => true]
        );

        return response()->json(['success' => true, 'data' => $module]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'application_id' => 'nullable|exists:applications,id',
            'is_active'      => 'boolean',
        ]);
        $module = ApplicationModule::create($validated);
        return response()->json(['success' => true, 'message' => 'Module created successfully', 'data' => $module]);
    }

    public function update(Request $request, ApplicationModule $applicationModule)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'application_id' => 'nullable|exists:applications,id',
            'is_active'      => 'boolean',
        ]);
        $applicationModule->update($validated);
        return response()->json(['success' => true, 'message' => 'Module updated successfully', 'data' => $applicationModule]);
    }

    public function destroy(ApplicationModule $applicationModule)
    {
        $applicationModule->delete();
        return response()->json(['success' => true, 'message' => 'Module deleted successfully']);
    }
}
