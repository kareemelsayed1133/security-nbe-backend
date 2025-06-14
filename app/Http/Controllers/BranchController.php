<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    /**
     * Apply admin authorization to modification methods.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('DELETE')) {
                if (!Auth::check() || Auth::user()->role->name !== 'admin') {
                    return response()->json(['message' => 'غير مصرح لك بالقيام بهذا الإجراء.'], 403);
                }
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Branch::select('id', 'name', 'address', 'latitude', 'longitude', 'geofence_radius_meters');

        if ($request->boolean('paginate') === false || $request->input('paginate') === '0') {
            $branches = $query->orderBy('name')->get();
        } else {
            $branches = $query->orderBy('name')->paginate($request->input('per_page', 15));
        }
        return response()->json($branches);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:branches,name',
            'address' => 'nullable|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'geofence_radius_meters' => 'required|integer|min:10|max:1000',
        ]);

        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }

        $branch = Branch::create($validator->validated());
        return response()->json(['message' => 'تم إنشاء الفرع بنجاح.', 'branch' => $branch], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', Rule::unique('branches')->ignore($branch->id)],
            'address' => 'nullable|string|max:500',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'geofence_radius_meters' => 'required|integer|min:10|max:1000',
        ]);

        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }

        $branch->update($validator->validated());
        return response()->json(['message' => 'تم تحديث الفرع بنجاح.', 'branch' => $branch]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch)
    {
        if ($branch->users()->count() > 0 || $branch->incidents()->count() > 0) {
            return response()->json(['message' => 'لا يمكن حذف الفرع لوجود مستخدمين أو حوادث مرتبطة به.'], 409);
        }
        $branch->delete();
        return response()->json(['message' => 'تم حذف الفرع بنجاح.']);
    }
}

