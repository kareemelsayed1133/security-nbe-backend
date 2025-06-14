<?php

namespace App\Http\Controllers;

use App\Models\AssetType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AssetTypeController extends Controller
{
     public function __construct()
    {
        // Admins can manage, others can view for dropdowns
        $this->middleware(function ($request, $next) {
            if ($request->isMethod('GET')) {
                return $next($request);
            }
            if (!Auth::check() || Auth::user()->role->name !== 'admin') {
                return response()->json(['message' => 'غير مصرح لك.'], 403);
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = AssetType::select('id', 'name_ar', 'icon_class');

        if ($request->boolean('paginate') === false) {
            return response()->json($query->orderBy('name_ar')->get());
        }
        return response()->json($query->paginate(15));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|max:255|unique:asset_types,name_ar',
            'icon_class' => 'nullable|string|max:50',
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }
        
        $assetType = AssetType::create($validator->validated());
        return response()->json(['message' => 'تم إنشاء نوع الأصل بنجاح.', 'asset_type' => $assetType], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AssetType $assetType)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => ['required', 'string', 'max:255', Rule::unique('asset_types')->ignore($assetType->id)],
            'icon_class' => 'nullable|string|max:50',
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }
        
        $assetType->update($validator->validated());
        return response()->json(['message' => 'تم تحديث نوع الأصل بنجاح.', 'asset_type' => $assetType]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssetType $assetType)
    {
        if ($assetType->assets()->exists()) {
            return response()->json(['message' => 'لا يمكن حذف هذا النوع لوجود أصول مرتبطة به.'], 409);
        }
        $assetType->delete();
        return response()->json(['message' => 'تم حذف نوع الأصل بنجاح.']);
    }
}

