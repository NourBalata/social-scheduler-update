<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AIController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|min:5',
        ]);

        try {
            // هنا نرسل الطلب للذكاء الاصطناعي (مثال باستخدام Gemini API)
            // ملاحظة: ستحتاج لـ API Key من Google AI Studio
            $apiKey = config('services.gemini.key'); 
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => "You are a professional social media manager. Rewrite this text to be more engaging, professional, and include emojis and hashtags: " . $request->prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $aiText = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Could not generate text.';
                
                return response()->json([
                    'success' => true,
                    'text' => trim($aiText)
                ]);
            }

            return response()->json(['success' => false, 'error' => 'API Error'], 500);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}