<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AiAssistantController extends Controller
{
    /**
     * Handle a question from the user.
     */
    public function ask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string|min:3|max:1000',
        ]);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }

        $userPrompt = $request->input('prompt');
        Log::info("AI Assistant request from user ".Auth::id().": '{$userPrompt}'");

        // Mock Response Logic (Phase 1)
        // In a real implementation, call getKnowledgeBaseContent() and Gemini API.
        $mockAnswer = "عذرًا، لم أفهم سؤالك. هل يمكنك إعادة صياغته؟";
        if (str_contains(strtolower($userPrompt), "حريق")) {
            $mockAnswer = "في حالة الحريق، قم بتفعيل الإنذار، ثم أخلِ المبنى بهدوء عبر مخارج الطوارئ، واتصل بالدفاع المدني.";
        } elseif (str_contains(strtolower($userPrompt), "سرقة")) {
            $mockAnswer = "عند الاشتباه في سرقة، سلامتك أولاً. قم بتفعيل زر الطوارئ بصمت إذا أمكن، ولا تقاوم. حاول تذكر التفاصيل وأبلغ المشرف فورًا بعد الموقف.";
        }

        return response()->json(['answer' => $mockAnswer]);
    }
}

