<?php
namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI; // dùng facade

class OpenAIService
{
    public function reply(string $prompt): string
    {
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini', // model mới
            'messages' => [
                ['role' => 'system', 'content' => 'Bạn là nhân viên CSKH thân thiện, trả lời ngắn gọn.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        return $response->choices[0]->message->content ?? "Xin lỗi, tôi chưa có câu trả lời.";
    }
}
