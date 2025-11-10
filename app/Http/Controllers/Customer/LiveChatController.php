<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Conversation;
use App\Models\Message;

class LiveChatController extends Controller
{
    // Tạo/nhận conversation theo SESSION
    public function boot(Request $req)
    {
        if (!$req->session()->isStarted()) $req->session()->start();
        $sid = $req->session()->getId();

        // Tạo hoặc lấy hội thoại theo SESSION
        $conv = Conversation::firstOrCreate(
            ['session_id' => $sid, 'status' => 'open'],
            [
                'user_id'         => Auth::id(),
                'customer_name'   => Auth::user()->name ?? null,
                'last_message_at' => now(),
            ]
        );

        // Lưu để đóng khi logout
        $req->session()->put('lc.conversation_id', $conv->id);

        // === AUTO GREETING: chỉ chèn 1 lần đầu (khi vừa tạo/hoặc chưa có message) ===
        if ($conv->wasRecentlyCreated || $conv->messages()->count() === 0) {
            Message::create([
                'conversation_id' => $conv->id,
                'direction'       => 'out', // từ phía shop / CSKH
                'sender_id'       => null,
                'sender_name'     => 'CSKH',
                'body'            => 'Xin chào, tôi có thể giúp gì được cho bạn!',
            ]);
            $conv->update(['last_message_at' => now()]);
        }

        return response()->json([
            'conversation_id' => $conv->id,
            'customer_name'   => $conv->customer_name,
        ]);
    }

    // Khách gửi tin nhắn
    public function send(Request $req)
    {
        $data = $req->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'body'            => 'required|string|max:5000',
            'customer_name'   => 'nullable|string|max:100',
        ]);

        $conv = Conversation::findOrFail($data['conversation_id']);
        if (!empty($data['customer_name'])) $conv->customer_name = $data['customer_name'];
        $conv->last_message_at = now();
        $conv->save();

        // KHÁCH → direction = 'in' (hiển thị bên trái ở widget)
        Message::create([
            'conversation_id' => $conv->id,
            'direction'       => 'in',
            'sender_id'       => Auth::id(),
            'sender_name'     => $conv->customer_name ?: 'Khách',
            'body'            => $data['body'],
        ]);

        return response()->json(['ok' => true]);
    }

    // Lấy tin nhắn mới
    public function poll(Request $req)
    {
        $req->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'after_id'        => 'nullable|integer',
        ]);

        $q = Message::where('conversation_id', $req->conversation_id)->orderBy('id');
        if ($req->filled('after_id')) $q->where('id', '>', $req->after_id);

        return response()->json([
            'messages' => $q->take(100)->get(['id','direction','sender_name','body','created_at']),
        ]);
    }
}
