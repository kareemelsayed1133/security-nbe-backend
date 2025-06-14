<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource. Accessible by all authenticated users.
     */
    public function index(Request $request)
    {
        $templates = Template::select('id', 'title_ar', 'description_ar', 'file_path', 'created_at')
                            ->latest()
                            ->paginate(15);
                            
        return response()->json($templates);
    }

    /**
     * Store a newly created resource in storage. Admin only.
     */
    public function store(Request $request)
    {
        if (Auth::user()->role->name !== 'admin') {
            return response()->json(['message' => 'غير مصرح لك بالقيام بهذا الإجراء.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title_ar' => 'required|string|max:255',
            'description_ar' => 'nullable|string|max:1000',
            'template_file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:5120',
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }

        $file = $request->file('template_file');
        $filePath = $file->store('templates', 'public');

        try {
            $template = Template::create([
                'title_ar' => $request->title_ar,
                'description_ar' => $request->description_ar,
                'file_path' => $filePath,
                'mime_type' => $file->getClientMimeType(),
                'file_size_bytes' => $file->getSize(),
                'uploaded_by_user_id' => Auth::id(),
            ]);
            return response()->json(['message' => 'تم رفع النموذج بنجاح.', 'template' => $template], 201);
        } catch (\Exception $e) {
            Log::error("Template creation failed: " . $e->getMessage());
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            return response()->json(['message' => 'حدث خطأ أثناء حفظ النموذج.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage. Admin only.
     */
    public function destroy(Template $template)
    {
        if (Auth::user()->role->name !== 'admin') {
            return response()->json(['message' => 'غير مصرح لك بالقيام بهذا الإجراء.'], 403);
        }

        try {
            if (Storage::disk('public')->exists($template->file_path)) {
                Storage::disk('public')->delete($template->file_path);
            }
            $template->delete();
            return response()->json(['message' => 'تم حذف النموذج بنجاح.'], 200);
        } catch (\Exception $e) {
            Log::error("Template deletion failed for ID {$template->id}: " . $e->getMessage());
            return response()->json(['message' => 'حدث خطأ أثناء حذف النموذج.'], 500);
        }
    }
}
