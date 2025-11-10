<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminLiveChatController extends Controller
{
    public function index() {
        return view('admin.livechat.index');
    }

    // Danh sách hội thoại
    public function conversations(Request $req)
{
    $rows = DB::table('messages as m')
        ->select(
            'm.sender_id',
            DB::raw('MAX(m.conversation_id) as id'),
            DB::raw('MAX(m.sender_name) as customer_name'),
            DB::raw('MAX(m.created_at) as last_message_at'),
            DB::raw("SUM(CASE WHEN m.direction='in' AND m.read_at IS NULL THEN 1 ELSE 0 END) as unread")
        )
        ->where('m.direction', 'in') // chỉ lấy tin user gửi vào
        ->groupBy('m.sender_id')
        ->orderByDesc(DB::raw('MAX(m.created_at)'))
        ->get();

    foreach ($rows as $r) {
        if (!$r->customer_name) $r->customer_name = 'Người dùng #'.$r->sender_id;
    }

    return response()->json(['conversations' => $rows]);
}

    // Lấy messages của hội thoại
    public function messages(Conversation $conversation, Request $req) {
        $req->validate(['after_id' => 'nullable|integer']);
        $q = $conversation->messages()->orderBy('id');
        if ($req->filled('after_id')) $q->where('id', '>', $req->after_id);

        return response()->json([
            'messages' => $q->take(200)->get(['id','direction','sender_name','body','created_at'])
        ]);
    }

    // Gửi tin nhắn
    public function send(Conversation $conversation, Request $req) {
        $data = $req->validate(['body' => 'required|string|max:5000']);

        $msg = Message::create([
            'conversation_id' => $conversation->id,
            'direction'       => 'out', // admin → khách
            'sender_id'       => Auth::id(),
            'sender_name'     => Auth::user()->name ?? 'Admin',
            'body'            => $data['body'],
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json(['ok' => true, 'message_id' => $msg->id]);
    }

    // Đóng hội thoại
    public function close(Conversation $conversation) {
        $conversation->status = 'closed';
        $conversation->save();
        return response()->json(['ok'=>true]);
    }
}
