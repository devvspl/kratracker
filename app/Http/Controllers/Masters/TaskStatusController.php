<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\TaskStatus;
use App\Traits\ScopedMasterController;
use Illuminate\Http\Request;

class TaskStatusController extends Controller
{
    use ScopedMasterController;

    public function index()
    {
        $statuses = $this->isUserScoped()
            ? TaskStatus::ownedByUser()->orderBy('sort_order')->get()
            : TaskStatus::orderBy('sort_order')->get();

        return view('masters.task-statuses', compact('statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'color_class' => 'required|string',
            'sort_order'  => 'required|integer|min:0',
            'is_active'   => 'boolean',
        ]);
        $status = TaskStatus::create([...$validated, 'user_id' => $this->scopedUserId()]);
        return response()->json(['success' => true, 'message' => 'Status created successfully', 'data' => $status]);
    }

    public function update(Request $request, TaskStatus $taskStatus)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'color_class' => 'required|string',
            'sort_order'  => 'required|integer|min:0',
            'is_active'   => 'boolean',
        ]);
        $taskStatus->update($validated);
        return response()->json(['success' => true, 'message' => 'Status updated successfully', 'data' => $taskStatus]);
    }

    public function destroy(TaskStatus $taskStatus)
    {
        $taskStatus->delete();
        return response()->json(['success' => true, 'message' => 'Status deleted successfully']);
    }
}
