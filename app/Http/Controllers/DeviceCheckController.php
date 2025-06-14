<?php

namespace App\Http\Controllers;

use App\Models\DeviceCheck;
use App\Models\SecurityDevice;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class DeviceCheckController extends Controller
{
    /**
     * Store a newly created device check in storage. (Guard)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->role->name !== 'guard') {
            return response()->json(['message' => 'فقط الحراس يمكنهم تسجيل فحص.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'security_device_id' => 'required|exists:security_devices,id',
            'status_reported' => 'required|in:operational,needs_maintenance,out_of_service',
            'notes' => 'nullable|string|max:1000',
            'image' => 'nullable|image|max:5120', // 5MB
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }
        
        $device = SecurityDevice::find($request->security_device_id);
        if ($device->branch_id !== $user->branch_id) {
            return response()->json(['message' => 'هذا الجهاز لا ينتمي إلى فرعك.'], 403);
        }

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('device_checks', 'public');
            $imageUrl = Storage::url($imagePath);
        }

        DB::beginTransaction();
        try {
            $deviceCheck = DeviceCheck::create([
                'security_device_id' => $device->id,
                'checked_by_user_id' => $user->id,
                'check_time' => now(),
                'status_reported' => $request->status_reported,
                'notes' => $request->notes,
                'image_url' => $imageUrl,
            ]);

            // Update the security device's status
            $device->status = $request->status_reported;
            $device->last_checked_at = $deviceCheck->check_time;
            $device->last_checked_by_user_id = $user->id;
            $device->save();
            
            // If status is problematic, automatically create or update a MaintenanceRequest
            if (in_array($request->status_reported, ['needs_maintenance', 'out_of_service'])) {
                MaintenanceRequest::updateOrCreate(
                    ['security_device_id' => $device->id, 'status' => 'open'],
                    [
                        'reported_by_user_id' => $user->id,
                        'description' => "تم الإبلاغ عن مشكلة أثناء الفحص الدوري. الحالة: '{$request->status_reported}'. " . ($request->notes ?? ''),
                        'priority' => ($request->status_reported === 'out_of_service') ? 'high' : 'medium',
                    ]
                );
            }
            
            DB::commit();
            return response()->json(['message' => 'تم تسجيل فحص الجهاز بنجاح.', 'device_check' => $deviceCheck], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Device check creation failed: " . $e->getMessage());
            if ($imageUrl && isset($imagePath)) { Storage::disk('public')->delete($imagePath); }
            return response()->json(['message' => 'حدث خطأ أثناء تسجيل الفحص.'], 500);
        }
    }
}

