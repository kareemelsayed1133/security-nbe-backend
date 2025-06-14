<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DailyReportController extends Controller
{
    public function store(Request $request)
    {
        $guard = Auth::user();
        if ($guard->role->name !== 'guard') {
            return response()->json(['message' => 'فقط الحراس يمكنهم تقديم تقارير يومية.'], 403);
        }
        $validator = Validator::make($request->all(), [
            'report_date' => 'required|date_format:Y-m-d',
            'shift_summary_status' => 'required|in:quiet,normal,busy,eventful',
            'notes' => 'nullable|string|max:5000',
            'shift_id' => 'nullable|exists:shifts,id,user_id,'.$guard->id,
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }
        
        try {
            $report = DailyReport::updateOrCreate(
                [
                    'user_id' => $guard->id,
                    'report_date' => $request->report_date,
                ],
                [
                    'branch_id' => $guard->branch_id,
                    'shift_id' => $request->shift_id,
                    'shift_summary_status' => $request->shift_summary_status,
                    'notes' => $request->notes,
                ]
            );
            return response()->json(['message' => 'تم حفظ تقرير نهاية الوردية بنجاح.', 'report' => $report], 201);
        } catch (\Exception $e) {
            Log::error("Daily report creation failed for user {$guard->id}: " . $e->getMessage());
            return response()->json(['message' => 'حدث خطأ أثناء إرسال التقرير.'], 500);
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role->name, ['supervisor', 'admin'])) {
            return response()->json(['message' => 'غير مصرح لك بعرض هذه البيانات.'], 403);
        }
        $query = DailyReport::with('user:id,name', 'branch:id,name');

        if ($user->role->name === 'supervisor') {
            $query->where('branch_id', $user->branch_id);
        }
        
        if ($user->role->name === 'admin' && $request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('report_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('report_date', '<=', $request->end_date);
        }

        $reports = $query->latest('report_date')->paginate(20);
        return response()->json($reports);
    }
}

