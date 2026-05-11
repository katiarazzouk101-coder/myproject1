<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatBotController extends Controller
{
    public function ask(Request $request)
    {
        $userMessage = $request->input('message');

        // التأكد من وصول الرسالة
        if (!$userMessage) {
            return response()->json(['reply' => 'الرسالة فارغة!'], 400);
        }

        $apiUrl = 'https://openrouter.ai/api/v1/chat/completions';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post($apiUrl, [
                'model' => 'nvidia/nemotron-3-nano-omni-30b-a3b-reasoning:free',



                'messages' => [
                    ['role' => 'user', 'content' => $userMessage]
                ],
            ]);

            $data = $response->json();

            // حالة النجاح: إذا استلمنا رد من الذكاء الاصطناعي
            if (isset($data['choices'][0]['message']['content'])) {
                return response()->json([
                    'reply' => $data['choices'][0]['message']['content']
                ]);
            }

            // حالة الفشل: إذا رد موقع OpenRouter بخطأ (مثل الحجب أو مفتاح غلط)
            return response()->json([
                'reply' => 'خطأ من OpenRouter: ' . ($data['error']['message'] ?? 'وصل الطلب للسيرفر ولكن الرد من الذكاء الاصطناعي فارغ.')
            ], 500);

        } catch (\Exception $e) {
            // حالة فشل الاتصال تماماً (غالباً بسبب عدم تشغيل VPN على اللابتوب)
            return response()->json([
                'reply' => 'خطأ في الاتصال بالسيرفر الخارجي: ' . $e->getMessage()
            ], 500);
        }
    }
}
