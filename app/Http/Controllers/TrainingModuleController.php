<?php

namespace App\Http\Controllers;

use App\Models\TrainingModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TrainingModuleController extends Controller
{
    public function __construct()
    {
        // Admin manages, others can view academy content
        $this->middleware(function ($request, $next) {
            $isPublicAction = in_array($request->route()->getActionMethod(), ['indexForAcademy', 'showForAcademy']);
            if (!$isPublicAction && (!Auth::check() || Auth::user()->role->name !== 'admin')) {
                return response()->json(['message' => 'غير مصرح لك.'], 403);
            }
            return $next($request);
        });
    }

    /**
     * Display a listing for admin management.
     */
    public function index(Request $request)
    {
        $query = TrainingModule::with('category:id,name_ar');
        if ($request->filled('training_category_id')) {
            $query->where('training_category_id', $request->training_category_id);
        }
        $modules = $query->orderBy('order_in_category')->paginate(15);
        return response()->json($modules);
    }

    /**
     * Display a listing of active modules for the academy view.
     */
    public function indexForAcademy(Request $request)
    {
        $modules = TrainingModule::with('category:id,name_ar,icon_class')
                    ->where('is_active', true)
                    ->orderBy('training_category_id')
                    ->orderBy('order_in_category')
                    ->get();
                    
        $grouped = $modules->groupBy('category.name_ar');
        return response()->json($grouped);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation logic from previous response
        // ...
        $module = TrainingModule::create($request->all()); // Simplified for brevity
        return response()->json(['message' => 'تم إنشاء الوحدة بنجاح.', 'module' => $module], 201);
    }

    /**
     * Display the specified resource for admin.
     */
    public function show(TrainingModule $trainingModule)
    {
        return response()->json($trainingModule->load('category:id,name_ar'));
    }

    /**
     * Display the specified active module for academy users.
     */
    public function showForAcademy(TrainingModule $trainingModule)
    {
        if (!$trainingModule->is_active) {
            return response()->json(['message' => 'الوحدة التدريبية غير متاحة.'], 404);
        }
        return response()->json($trainingModule->load('category:id,name_ar'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TrainingModule $trainingModule)
    {
        // Validation logic from previous response
        // ...
        $trainingModule->update($request->all()); // Simplified for brevity
        return response()->json(['message' => 'تم تحديث الوحدة بنجاح.', 'module' => $trainingModule]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TrainingModule $trainingModule)
    {
        $trainingModule->delete();
        return response()->json(['message' => 'تم حذف الوحدة بنجاح.']);
    }
}

