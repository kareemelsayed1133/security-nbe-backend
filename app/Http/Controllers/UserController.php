<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // In a real app, use a policy: $this->authorize('viewAny', User::class);
        $currentUser = Auth::user();
        
        $query = User::with(['role:id,display_name_ar', 'branch:id,name']);

        // Scope visibility based on role
        if ($currentUser->role->name === 'supervisor') {
            $query->where('branch_id', $currentUser->branch_id)
                  ->where('role_id', '!=', $currentUser->role_id); // Supervisors don't see other supervisors or admins
        } elseif ($currentUser->role->name !== 'admin') {
            return response()->json(['message' => 'غير مصرح لك بعرض هذه القائمة.'], 403);
        }

        // Apply filters for admin
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%{$searchTerm}%")->orWhere('username', 'like', "%{$searchTerm}%"));
        }
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $users = $query->latest()->paginate(15);
        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Auth::user()->role->name !== 'admin') {
            return response()->json(['message' => 'فقط المدير يمكنه إضافة مستخدمين جدد.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }
        
        $role = Role::find($request->role_id);
        if ($role && in_array($role->name, ['guard', 'supervisor']) && !$request->branch_id) {
             return response()->json(['errors' => ['branch_id' => ['حقل الفرع مطلوب لدور الحارس أو المشرف.']]], 422);
        }

        $data = $validator->validated();
        $data['password'] = Hash::make($data['password']);
        
        $user = User::create($data);

        return response()->json(['message' => 'تم إنشاء المستخدم بنجاح.', 'user' => $user->load('role','branch')], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        // Use policy for fine-grained access control
        return response()->json($user->load('role', 'branch'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $targetUser)
    {
        if (Auth::user()->role->name !== 'admin') {
            return response()->json(['message' => 'فقط المدير يمكنه تعديل المستخدمين.'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'username' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('users')->ignore($targetUser->id)],
            'email' => ['sometimes', 'nullable', 'string', 'email', 'max:255', Rule::unique('users')->ignore($targetUser->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'sometimes|required|exists:roles,id',
            'branch_id' => 'sometimes|nullable|exists:branches,id',
            'status' => 'sometimes|required|in:active,inactive,suspended',
        ]);

        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }

        $dataToUpdate = $request->except(['password', 'password_confirmation']);
        if ($request->filled('password')) {
            $dataToUpdate['password'] = Hash::make($request->password);
        }
        
        $targetUser->update($dataToUpdate);

        return response()->json(['message' => 'تم تحديث بيانات المستخدم بنجاح.', 'user' => $targetUser->fresh()->load('role','branch')]);
    }

    /**
     * Update the status of the specified user.
     */
    public function updateStatus(Request $request, User $user)
    {
        if (Auth::user()->role->name !== 'admin') {
            return response()->json(['message' => 'غير مصرح لك.'], 403);
        }
        if ($user->id === Auth::id()) {
             return response()->json(['message' => 'لا يمكنك تغيير حالة حسابك الخاص.'], 403);
        }
        $validator = Validator::make($request->all(), ['status' => 'required|in:active,inactive,suspended']);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }

        $user->status = $request->status;
        $user->save();
        return response()->json(['message' => 'تم تحديث حالة المستخدم بنجاح.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
         if (Auth::user()->role->name !== 'admin') {
            return response()->json(['message' => 'غير مصرح لك.'], 403);
        }
        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'لا يمكنك حذف حسابك الخاص.'], 403);
        }
        
        // It's better to deactivate than delete.
        // For a real delete, add checks for related data.
        $user->status = 'inactive';
        $user->save();
        return response()->json(['message' => 'تم تعطيل حساب المستخدم بدلاً من الحذف.']);
    }
}

