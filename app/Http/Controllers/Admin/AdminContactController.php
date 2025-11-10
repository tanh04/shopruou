<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class AdminContactController extends Controller
{
    /** Danh sách + lọc */
    public function all_contacts(Request $request)
    {
        $q = Contact::query();

        if ($kw = $request->input('s')) {
            $q->where(function ($x) use ($kw) {
                $x->where('name', 'like', "%{$kw}%")
                  ->orWhere('email', 'like', "%{$kw}%")
                  ->orWhere('phone', 'like', "%{$kw}%")
                  ->orWhere('subject', 'like', "%{$kw}%")
                  ->orWhere('message', 'like', "%{$kw}%");
            });
        }

        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        $q = $q->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.manage_contacts.all_contacts', [
            'q' => $q,
        ]);
    }

    /** Xem chi tiết 1 liên hệ */
    public function show_contacts(Contact $contact)
    {
        return view('admin.manage_contacts.show_contact', compact('contact'));
    }

    /** Cập nhật trạng thái: chỉ cho đổi khi đang pending */
    public function update(Request $request, Contact $contact)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending','done','spam'])],
        ]);

        if ($contact->status !== 'pending') {
            return back()->with('error', 'Liên hệ đã ở trạng thái "'.$contact->status.'", không thể đổi nữa.');
        }

        if ($data['status'] === 'done' && is_null($contact->replied_at)) {
            $contact->replied_at = now();
        }

        $contact->status = $data['status'];
        $contact->save();

        return back()->with('success', 'Đã cập nhật trạng thái.');
    }

    /** Xoá liên hệ */
    public function destroy(Contact $contact)
    {
        $contact->delete();
        return back()->with('success', 'Đã xoá liên hệ.');
    }

    /** Admin phản hồi: gửi email cho khách + lưu nội dung + set done */
    public function reply(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);

        $data = $request->validate([
            'reply_message' => 'required|string|max:8000',
        ]);

        // Gửi email (truyền cả 'content' và 'reply' để view nào cũng nhận được)
        try {
            Mail::send('emails.contact_reply', [
                'contact' => $contact,
                'content' => $data['reply_message'],
                'reply'   => $data['reply_message'],
            ], function ($m) use ($contact) {
                $m->to($contact->email, $contact->name)
                  ->subject('Re: '.$contact->subject);
            });
        } catch (\Throwable $e) {
            report($e);
            // vẫn lưu nội dung đã soạn, ghi thời điểm; báo lỗi gửi mail
            $contact->update([
                'reply_message' => $data['reply_message'],
                'replied_at'    => now(),
            ]);
            return back()->with('error', 'Gửi email thất bại: '.$e->getMessage());
        }

        // Lưu & đánh dấu ĐÃ DUYỆT
        $contact->update([
            'reply_message' => $data['reply_message'],
            'replied_at'    => now(),
            'status'        => 'done',
        ]);

        return back()->with('success', 'Đã gửi phản hồi cho khách và đánh dấu ĐÃ DUYỆT.');
    }
}
