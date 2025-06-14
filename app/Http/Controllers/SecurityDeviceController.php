<?php

namespace App\Http\Controllers;

use App\Models\SecurityDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SecurityDeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = SecurityDevice::with([
            'deviceType:id,name_ar,icon_class', 
            'branch:id,name', 
            'lastCheckedBy:id,name'
        ]);

        // Role-based filtering
        if ($user->role->name === 'guard') {
            if (!$user->branch_id) {
                return response()->json(['data' => []], 403);
            }
            $query->where('branch_id', $user->branch_id);
        } elseif ($user->role->name === 'supervisor') {
            $supervisorBranchId = $user->branch_id;
            $query->where('branch_id', $request->input('branch_id', $supervisorBranchId));
        } elseif ($user->role->name === 'admin' && $request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // General Filters
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%{$searchTerm}%")->orWhere('serial_number', 'like', "%{$searchTerm}%"));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('device_type_id')) {
            $query->where('device_type_id', $request->device_type_id);
        }

        $devices = $query->orderBy('name')->paginate($request->input('per_page', 10));
        return response()->json($devices);
    }

    /**
     * Store a newly created resource in storage. (Admin only)
     */
    public function store(Request $request)
    {
        if (Auth::user()->role->name !== 'admin') {
            return response()->json(['message' => 'غير مصرح لك.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
            'device_type_id' => 'required|exists:device_types,id',
            'serial_number' => 'nullable|string|max:255|unique:security_devices,serial_number',
            'location_description' => 'nullable|string|max:500',
            'qr_code_identifier' => 'nullable|string|max:255|unique:security_devices,qr_code_identifier',
            'status' => ['required', Rule::in(['operational', 'needs_maintenance', 'out_of_service', 'under_maintenance'])],
            'installation_date' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }

        $device = SecurityDevice::create($validator->validated());
        return response()->json(['message' => 'تم إضافة الجهاز بنجاح.', 'device' => $device->load('deviceType', 'branch')], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(SecurityDevice $securityDevice)
    {
        $user = Auth::user();
        if (($user->role->name === 'guard' || $user->role->name === 'supervisor') && $securityDevice->branch_id !== $user->branch_id) {
             return response()->json(['message' => 'غير مصرح لك بعرض هذا الجهاز.'], 403);
        }
        
        $securityDevice->load([
            'deviceType', 'branch', 'lastCheckedBy:id,name',
            'deviceChecks' => fn($q) => $q->with('checkedBy:id,name')->latest()->limit(5),
            'maintenanceRequests' => fn($q) => $q->with('reportedBy:id,name')->latest()->limit(5)
        ]);
        return response()->json($securityDevice);
    }

    /**
     * Update the specified resource in storage. (Admin/Supervisor)
     */
    public function update(Request $request, SecurityDevice $securityDevice)
    {
        $user = Auth::user();
        if ($user->role->name === 'guard') { return response()->json(['message' => 'غير مصرح لك.'], 403); }
        if ($user->role->name === 'supervisor' && $securityDevice->branch_id !== $user->branch_id) {
            return response()->json(['message' => 'غير مصرح لك.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'branch_id' => ['sometimes','required','exists:branches,id', Rule::when($user->role->name !== 'admin', fn($q) => $q->prohibited())],
            'device_type_id' => 'sometimes|required|exists:device_types,id',
            'serial_number' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('security_devices')->ignore($securityDevice->id)],
            'status' => ['sometimes', 'required', Rule::in(['operational', 'needs_maintenance', 'out_of_service', 'under_maintenance'])],
        ]);

        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }
        
        $securityDevice->update($validator->validated());
        return response()->json(['message' => 'تم تحديث الجهاز بنجاح.', 'device' => $securityDevice->fresh()->load('deviceType', 'branch')]);
    }

    /**
     * Remove the specified resource from storage. (Admin only)
     */
    public function destroy(SecurityDevice $securityDevice)
    {
        if (Auth::user()->role->name !== 'admin') {
            return response()->json(['message' => 'غير مصرح لك.'], 403);
        }

        if ($securityDevice->deviceChecks()->exists() || $securityDevice->maintenanceRequests()->exists()) {
            return response()->json(['message' => 'لا يمكن حذف الجهاز لوجود سجلات مرتبطة به.'], 409);
        }

        $securityDevice->delete();
        return response()->json(['message' => 'تم حذف الجهاز بنجاح.']);
    }
}

