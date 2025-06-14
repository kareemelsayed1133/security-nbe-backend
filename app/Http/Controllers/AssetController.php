<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    public function __construct()
    {
        // Apply authorization logic. A policy would be better for complex rules.
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || !in_array(Auth::user()->role->name, ['admin', 'supervisor'])) {
                return response()->json(['message' => 'غير مصرح لك.'], 403);
            }
            if (($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('DELETE')) && Auth::user()->role->name !== 'admin') {
                return response()->json(['message' => 'فقط المدير يمكنه تعديل الأصول.'], 403);
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Asset::with(['assetType:id,name_ar,icon_class', 'branch:id,name', 'currentUser:id,name']);

        if ($user->role->name === 'supervisor') {
            $query->where('branch_id', $request->input('branch_id', $user->branch_id));
        } elseif ($user->role->name === 'admin' && $request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('asset_type_id')) {
            $query->where('asset_type_id', $request->asset_type_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%{$searchTerm}%")->orWhere('identifier', 'like', "%{$searchTerm}%"));
        }

        $assets = $query->orderBy('name')->paginate(15);
        return response()->json($assets);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'asset_type_id' => 'required|exists:asset_types,id',
            'branch_id' => 'required|exists:branches,id',
            'identifier' => 'nullable|string|max:255|unique:assets,identifier',
            'status' => ['required', Rule::in(['available', 'assigned', 'in_maintenance', 'retired'])],
            'notes' => 'nullable|string',
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }

        try {
            $asset = Asset::create($validator->validated());
            return response()->json(['message' => 'تم إضافة الأصل بنجاح.', 'asset' => $asset->load('assetType', 'branch')], 201);
        } catch (\Exception $e) {
            Log::error("Asset creation failed: " . $e->getMessage());
            return response()->json(['message' => 'حدث خطأ أثناء إضافة الأصل.'], 500);
        }
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Asset $asset)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'asset_type_id' => 'sometimes|required|exists:asset_types,id',
            'branch_id' => 'sometimes|required|exists:branches,id',
            'identifier' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('assets')->ignore($asset->id)],
            'status' => ['sometimes', 'required', Rule::in(['available', 'assigned', 'in_maintenance', 'retired'])],
            'notes' => 'nullable|string',
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }
        
        try {
            $asset->update($validator->validated());
            return response()->json(['message' => 'تم تحديث الأصل بنجاح.', 'asset' => $asset->fresh()->load('assetType', 'branch')]);
        } catch (\Exception $e) {
             Log::error("Asset update failed for ID {$asset->id}: " . $e->getMessage());
            return response()->json(['message' => 'حدث خطأ أثناء تحديث الأصل.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asset $asset)
    {
        if ($asset->logs()->exists() || $asset->status === 'assigned') {
            return response()->json(['message' => 'لا يمكن حذف الأصل لوجود سجلات مرتبطة به أو لأنه في عهدة أحد الحراس.'], 409);
        }
        $asset->delete();
        return response()->json(['message' => 'تم حذف الأصل بنجاح.']);
    }
}

