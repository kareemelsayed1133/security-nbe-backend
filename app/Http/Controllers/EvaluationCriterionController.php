<?php

namespace App\Http\Controllers;

use App\Models\EvaluationCriterion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EvaluationCriterionController extends Controller
{
    public function __construct()
    {
        // Allow index for supervisors/admins, but modification only for admins.
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || !in_array(Auth::user()->role->name, ['admin', 'supervisor'])) {
                return response()->json(['message' => 'غير مصرح لك.'], 403);
            }
            if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('DELETE')) {
                if (Auth::user()->role->name !== 'admin') {
                    return response()->json(['message' => 'فقط المدير يمكنه تعديل معايير التقييم.'], 403);
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
        $query = EvaluationCriterion::select('id', 'name_ar', 'description_ar', 'max_score', 'is_active');
        
        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        if ($request->boolean('paginate') === false) {
             return response()->json($query->orderBy('id')->get());
        }

        return response()->json($query->paginate(15));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|max:255|unique:evaluation_criteria,name_ar',
            'description_ar' => 'nullable|string',
            'max_score' => 'required|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }

        $criterion = EvaluationCriterion::create($validator->validated());
        return response()->json(['message' => 'تم إنشاء المعيار بنجاح.', 'criterion' => $criterion], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EvaluationCriterion $evaluationCriterion)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => ['required', 'string', 'max:255', Rule::unique('evaluation_criteria')->ignore($evaluationCriterion->id)],
            'description_ar' => 'nullable|string',
            'max_score' => 'required|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }
        
        $evaluationCriterion->update($validator->validated());
        return response()->json(['message' => 'تم تحديث المعيار بنجاح.', 'criterion' => $evaluationCriterion]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EvaluationCriterion $evaluationCriterion)
    {
        if ($evaluationCriterion->ratings()->exists()) {
             return response()->json(['message' => 'لا يمكن حذف هذا المعيار لوجود تقييمات مرتبطة به.'], 409);
        }
        $evaluationCriterion->delete();
        return response()->json(['message' => 'تم حذف المعيار بنجاح.']);
    }
}

