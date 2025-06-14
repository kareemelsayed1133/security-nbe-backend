<?php

namespace App\Http\Controllers;

use App\Models\AiAssistantDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AiAssistantDocumentController extends Controller
{
    public function __construct()
    {
        // Admin only
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || Auth::user()->role->name !== 'admin') {
                return response()->json(['message' => 'غير مصرح لك.'], 403);
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $documents = AiAssistantDocument::with('uploadedBy:id,name')->latest()->paginate(15);
        return response()->json($documents);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document_file' => 'required|file|mimes:pdf,doc,docx,txt|max:10240',
            'display_name_ar' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }

        $file = $request->file('document_file');
        $filePath = $file->store('ai_assistant_documents', 'public'); 

        try {
            $document = AiAssistantDocument::create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'mime_type' => $file->getClientMimeType(),
                'file_size_bytes' => $file->getSize(),
                'display_name_ar' => $request->input('display_name_ar', $file->getClientOriginalName()),
                'description_ar' => $request->description_ar,
                'uploaded_by_user_id' => Auth::id(),
                'is_active' => $request->input('is_active', true),
            ]);
            return response()->json(['message' => 'تم رفع المستند بنجاح.', 'document' => $document], 201);
        } catch (\Exception $e) {
            Log::error("AiAssistantDocument creation failed: " . $e->getMessage());
            if (Storage::disk('public')->exists($filePath)) { Storage::disk('public')->delete($filePath); }
            return response()->json(['message' => 'حدث خطأ أثناء حفظ المستند.'], 500);
        }
    }

    public function update(Request $request, AiAssistantDocument $aiAssistantDocument)
    {
        $validator = Validator::make($request->all(), [
            'display_name_ar' => 'sometimes|required|string|max:255',
            'description_ar' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|required|boolean',
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }
        $aiAssistantDocument->update($validator->validated());
        return response()->json(['message' => 'تم تحديث بيانات المستند بنجاح.', 'document' => $aiAssistantDocument]);
    }

    public function destroy(AiAssistantDocument $aiAssistantDocument)
    {
        try {
            if (Storage::disk('public')->exists($aiAssistantDocument->file_path)) {
                Storage::disk('public')->delete($aiAssistantDocument->file_path);
            }
            $aiAssistantDocument->delete();
            return response()->json(['message' => 'تم حذف المستند بنجاح.']);
        } catch (\Exception $e) {
            Log::error("AiAssistantDocument deletion failed for ID {$aiAssistantDocument->id}: " . $e->getMessage());
            return response()->json(['message' => 'حدث خطأ أثناء حذف المستند.'], 500);
        }
    }
}

