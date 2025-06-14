<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IncidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Incident::with(['reporter:id,name', 'incidentType:id,name_ar', 'branch:id,name']);

        if ($user->role->name === 'supervisor') {
            $query->where('branch_id', $user->branch_id);
        } else if ($user->role->name !== 'admin') {
             return response()->json(['message' => 'غير مصرح لك.'], 403);
        }
        // Admin can see all, with optional branch filter
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $incidents = $query->latest('reported_at')->paginate(15);
        return response()->json($incidents);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->role->name !== 'guard') {
            return response()->json(['message' => 'فقط الحراس يمكنهم الإبلاغ عن حوادث.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'incident_type_id' => 'required|exists:incident_types,id',
            'description' => 'required|string|min:10',
            'severity' => 'required|in:low,medium,high,critical',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov|max:10240',
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }
        
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('incident_media', 'public');
        }

        $incident = Incident::create([
            'reference_number' => 'NBE-INC-' . strtoupper(Str::random(8)),
            'reported_by_user_id' => $user->id,
            'branch_id' => $user->branch_id,
            'incident_type_id' => $request->incident_type_id,
            'description' => $request->description,
            'severity' => $request->severity,
            'status' => 'reported',
            'reported_at' => now(),
        ]);

        if ($attachmentPath) {
            $incident->media()->create([
                'file_path' => $attachmentPath,
                'file_name' => $request->file('attachment')->getClientOriginalName(),
                'mime_type' => $request->file('attachment')->getClientMimeType(),
                'file_size_bytes' => $request->file('attachment')->getSize(),
            ]);
        }
        
        return response()->json(['message' => 'تم الإبلاغ عن الحادث بنجاح!', 'incident' => $incident], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Incident $incident)
    {
        // Use policy for authorization
        return response()->json($incident->load(['reporter:id,name', 'supervisor:id,name', 'branch', 'incidentType', 'media']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Incident $incident)
    {
        $user = Auth::user();
        if (!in_array($user->role->name, ['supervisor', 'admin'])) {
            return response()->json(['message' => 'غير مصرح لك.'], 403);
        }
        if ($user->role->name === 'supervisor' && $incident->branch_id !== $user->branch_id) {
            return response()->json(['message' => 'لا يمكنك تعديل حادث لا يخص فرعك.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:investigating,resolved,escalated,closed',
            'severity' => 'sometimes|in:low,medium,high,critical',
            'resolution_notes' => 'nullable|string|required_if:status,resolved,closed',
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }
        
        $dataToUpdate = $request->only(['status', 'severity', 'resolution_notes']);
        if ($request->input('status') === 'resolved' && !$incident->resolved_at) {
            $dataToUpdate['resolved_at'] = now();
        }
        $incident->update($dataToUpdate);

        return response()->json(['message' => 'تم تحديث الحادث بنجاح!', 'incident' => $incident->fresh()->load('reporter', 'supervisor')]);
    }
}

