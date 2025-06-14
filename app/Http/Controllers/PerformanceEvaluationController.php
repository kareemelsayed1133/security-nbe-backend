<?php
// Note: The getCriteria method has been moved to EvaluationCriterionController
// This controller will now handle the evaluations themselves.

namespace App\Http\Controllers;

use App\Models\PerformanceEvaluation;
use App\Models\EvaluationCriterion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceEvaluationController extends Controller
{
    /**
     * Display a listing of evaluations.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = PerformanceEvaluation::with(['guard:id,name,employee_id', 'supervisor:id,name', 'ratings.criterion:id,name_ar,max_score']);

        if ($user->role->name === 'guard') {
            $query->where('guard_user_id', $user->id)->where('status', 'finalized');
        } elseif ($user->role->name === 'supervisor') {
            $query->where('supervisor_user_id', $user->id);
            if ($request->filled('guard_user_id')) {
                $query->where('guard_user_id', $request->guard_user_id);
            }
        } elseif ($user->role->name === 'admin' && $request->filled('guard_user_id')) {
            $query->where('guard_user_id', $request->guard_user_id);
        }

        $evaluations = $query->latest('evaluation_date')->paginate(10);
        return response()->json($evaluations);
    }

    /**
     * Store a newly created performance evaluation.
     */
    public function store(Request $request)
    {
        $supervisor = Auth::user();
        if (!in_array($supervisor->role->name, ['supervisor', 'admin'])) {
            return response()->json(['message' => 'فقط المشرفون أو المديرون يمكنهم إنشاء تقييمات.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'guard_user_id' => 'required|exists:users,id',
            'evaluation_date' => 'required|date_format:Y-m-d',
            'evaluation_period' => 'required|string|max:100',
            'supervisor_comments' => 'nullable|string|max:2000',
            'ratings' => 'required|array|min:1',
            'ratings.*.criterion_id' => 'required|exists:evaluation_criteria,id',
            'ratings.*.score' => 'required|integer|min:0',
            'ratings.*.comments' => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }
        
        // ... (Authorization and score validation logic from previous response) ...
        
        DB::beginTransaction();
        try {
             // ... (Logic to calculate score and create evaluation and ratings from previous response) ...
            DB::commit();
            return response()->json(['message' => 'تم حفظ تقييم الأداء بنجاح.'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("PerformanceEvaluation creation failed: " . $e->getMessage());
            return response()->json(['message' => 'حدث خطأ أثناء حفظ تقييم الأداء.'], 500);
        }
    }

    /**
     * Display the specified performance evaluation.
     */
    public function show(PerformanceEvaluation $performanceEvaluation)
    {
        // ... (Authorization logic from previous response) ...
        return response()->json($performanceEvaluation->load(['guard', 'supervisor', 'ratings.criterion']));
    }
}

