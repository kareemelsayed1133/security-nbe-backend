<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\SecurityDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MaintenanceRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = MaintenanceRequest::with(['securityDevice.branch:id,name', 'reportedBy:id,name', 'assignedTechnician:id,name']);

        if ($user->role->name === 'supervisor') {
            $query->whereHas('securityDevice', fn($q) => $q->where('branch_id', $user->branch_id));
        } elseif ($user->role->name === 'admin') {
            if ($request->filled('branch_id')) {
                 $query->whereHas('securityDevice', fn($q) => $q->where('branch_id', $request->branch_id));
            }
        } else { // Guard
            return response()->json(['message' => 'غير مصرح لك بعرض هذه القائمة.'], 403);
        }

        // More filters
        if ($request->filled('status')) { $query->where('status', $request->status); }
        if ($request->filled('priority')) { $query->where('priority', $request->priority); }
        
        $requests = $query->latest()->paginate(20);
        return response()->json($requests);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ... (Code from previous response) ...
    }

    /**
     * Display the specified resource.
     */
    public function show(MaintenanceRequest $maintenanceRequest)
    {
         // ... (Code from previous response with authorization) ...
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        // ... (Code from previous response with authorization) ...
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MaintenanceRequest $maintenanceRequest)
    {
        // ... (Code from previous response with authorization) ...
    }
}

