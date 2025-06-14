<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'branch_id' => 'nullable|exists:branches,id',
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }

        $user = Auth::user();
        $query = Shift::with(['user:id,name', 'branch:id,name']);
        
        $query->whereYear('start_time', $request->year)->whereMonth('start_time', $request->month);
        
        if ($user->role->name === 'supervisor') {
            $query->where('branch_id', $request->input('branch_id', $user->branch_id));
        } elseif ($user->role->name === 'admin' && $request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        } else if ($user->role->name === 'guard') {
            $query->where('user_id', $user->id);
        } else if (!in_array($user->role->name, ['admin', 'supervisor'])) {
            return response()->json([]); // Return empty for others
        }
        
        $shifts = $query->orderBy('start_time')->get();
        return response()->json($shifts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Authorization should be here (supervisor/admin)
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
            'start_time' => 'required|date_format:Y-m-d\TH:i:s',
            'end_time' => 'required|date_format:Y-m-d\TH:i:s|after:start_time',
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }

        // Check for overlapping shifts
        $overlapping = Shift::where('user_id', $request->user_id)
            ->where('start_time', '<', $request->end_time)
            ->where('end_time', '>', $request->start_time)
            ->exists();
            
        if ($overlapping) {
            return response()->json(['errors' => ['start_time' => ['توجد وردية متداخلة لهذا الحارس.']]], 422);
        }

        $shift = Shift::create([
            'user_id' => $request->user_id,
            'branch_id' => $request->branch_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'notes' => $request->notes,
            'created_by_user_id' => Auth::id(),
        ]);

        return response()->json(['message' => 'تم إنشاء الوردية بنجاح.', 'shift' => $shift->load('user', 'branch')], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shift $shift)
    {
        // Authorization
        $validator = Validator::make($request->all(), [
            'user_id' => 'sometimes|required|exists:users,id',
            'start_time' => 'sometimes|required|date_format:Y-m-d\TH:i:s',
            'end_time' => 'sometimes|required|date_format:Y-m-d\TH:i:s|after:start_time',
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }
        
        // ... (Add overlap check on update) ...

        $shift->update($request->all());
        return response()->json(['message' => 'تم تحديث الوردية بنجاح.', 'shift' => $shift->fresh()->load('user', 'branch')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shift $shift)
    {
        // Authorization
        $shift->delete();
        return response()->json(['message' => 'تم حذف الوردية بنجاح.']);
    }
}

