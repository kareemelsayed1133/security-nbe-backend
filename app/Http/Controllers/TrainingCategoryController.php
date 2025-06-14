<?php

namespace App\Http\Controllers;

use App\Models\TrainingCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TrainingCategoryController extends Controller
{
    public function __construct()
    {
        // Admins can manage, others can view
        $this->middleware(function ($request, $next) {
            if ($request->isMethod('GET')) {
                return $next($request); // Allow viewing
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
        $query = TrainingCategory::select('id', 'name_ar', 'description_ar', 'icon_class', 'is_active');
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
            'name_ar' => 'required|string|max:255|unique:training_categories,name_ar',
            'icon_class' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }
        
        $category = TrainingCategory::create($validator->validated());
        return response()->json(['message' => 'تم إنشاء الفئة بنجاح.', 'category' => $category], 201);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TrainingCategory $trainingCategory)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => ['required', 'string', 'max:255', Rule::unique('training_categories')->ignore($trainingCategory->id)],
            'icon_class' => 'nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }

        $trainingCategory->update($validator->validated());
        return response()->json(['message' => 'تم تحديث الفئة بنجاح.', 'category' => $trainingCategory]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TrainingCategory $trainingCategory)
    {
        if ($trainingCategory->trainingModules()->exists()) {
            return response()->json(['message' => 'لا يمكن حذف الفئة لوجود وحدات تدريبية مرتبطة بها.'], 409);
        }
        $trainingCategory->delete();
        return response()->json(['message' => 'تم حذف الفئة بنجاح.']);
    }
}

