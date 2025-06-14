<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AssetAssignmentController extends Controller
{
    /**
     * Assign an asset to a guard.
     */
    public function assign(Request $request, Asset $asset)
    {
        $supervisor = Auth::user();
        if (!in_array($supervisor->role->name, ['supervisor', 'admin'])) {
            return response()->json(['message' => 'غير مصرح لك.'], 403);
        }
        if ($supervisor->role->name === 'supervisor' && $asset->branch_id !== $supervisor->branch_id) {
             return response()->json(['message' => 'لا يمكنك تعيين أصل لا ينتمي لفرعك.'], 403);
        }
        if ($asset->status !== 'available') {
            return response()->json(['message' => 'هذا الأصل غير متاح للتعيين حاليًا.'], 409);
        }

        $validator = Validator::make($request->all(), [
            'guard_user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }

        $guard = User::find($request->guard_user_id);
        if (!$guard || $guard->role->name !== 'guard' || $guard->branch_id !== $asset->branch_id) {
            return response()->json(['message' => 'لا يمكن تعيين هذا الأصل لهذا الحارس.'], 422);
        }

        DB::beginTransaction();
        try {
            $asset->status = 'assigned';
            $asset->current_user_id = $guard->id;
            $asset->save();

            AssetLog::create([
                'asset_id' => $asset->id,
                'user_id' => $guard->id,
                'processed_by_user_id' => $supervisor->id,
                'action' => 'assigned',
                'notes' => $request->notes,
            ]);

            DB::commit();
            return response()->json(['message' => "تم تعيين الأصل '{$asset->name}' للحارس '{$guard->name}' بنجاح.", 'asset' => $asset->fresh()->load('currentUser')]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Asset assignment failed: " . $e->getMessage());
            return response()->json(['message' => 'حدث خطأ أثناء عملية التعيين.'], 500);
        }
    }

    /**
     * Return an asset from a guard.
     */
    public function returnAsset(Request $request, Asset $asset)
    {
        $supervisor = Auth::user();
        if (!in_array($supervisor->role->name, ['supervisor', 'admin'])) {
            return response()->json(['message' => 'غير مصرح لك.'], 403);
        }
        if ($supervisor->role->name === 'supervisor' && $asset->branch_id !== $supervisor->branch_id) {
             return response()->json(['message' => 'لا يمكنك استلام هذا الأصل.'], 403);
        }
        if ($asset->status !== 'assigned') {
            return response()->json(['message' => 'هذا الأصل ليس في عهدة أحد.'], 409);
        }
        
        $guardId = $asset->current_user_id;
        DB::beginTransaction();
        try {
            $asset->status = 'available';
            $asset->current_user_id = null;
            $asset->save();

            AssetLog::create([
                'asset_id' => $asset->id,
                'user_id' => $guardId,
                'processed_by_user_id' => $supervisor->id,
                'action' => 'returned',
                'notes' => $request->notes,
            ]);
            DB::commit();
            return response()->json(['message' => "تم استلام الأصل بنجاح.", 'asset' => $asset->fresh()]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Asset return failed: " . $e->getMessage());
            return response()->json(['message' => 'حدث خطأ أثناء عملية الاستلام.'], 500);
        }
    }
    
    /**
     * Get assets currently assigned to the authenticated guard.
     */
    public function myAssets()
    {
        $guard = Auth::user();
        if ($guard->role->name !== 'guard') {
            return response()->json([]);
        }

        $assets = Asset::with('assetType:id,name_ar,icon_class')
                       ->where('current_user_id', $guard->id)
                       ->where('status', 'assigned')
                       ->orderBy('name')
                       ->get();
        return response()->json($assets);
    }
}

