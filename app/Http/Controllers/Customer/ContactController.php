<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
        public function index()
    {
        return view('pages.contacts.contact', [
        'hideSlider'  => true,
        'hideSidebar' => true,
    ]);
    }

    public function sendContact(Request $request)
    {
        $request->validate([
            'name'    => ['required','string','min:3','max:50'],
            'email'   => ['required','email'],
            'phone'   => ['nullable','regex:/^(0|\+84)\d{9,10}$/'],
            'subject' => ['nullable','string','max:120'],
            'message' => ['required','string','min:5','max:2000'],
        ],[
            'name.min'      => 'Họ và tên phải từ 3 ký tự.',
            'message.min'   => 'Nội dung phải từ 5 ký tự.',
            'phone.regex'   => 'SĐT bắt đầu bằng 0 hoặc +84 và dài 10-11 số.',
        ]);

        // Lưu DB
        $contact = Contact::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'subject'    => $request->subject,
            'message'    => $request->message,
            'status'     => 'pending',
            'ip'         => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);

        // Gửi email cho bộ phận CSKH (tuỳ chọn)
        try {
            Mail::raw($request->message, function ($mail) use ($request, $contact) {
                $mail->to('support@yourshop.com')
                     ->subject('Liên hệ #' . $contact->id . ($request->subject ? " - {$request->subject}" : ''))
                     ->replyTo($request->email);
            });
        } catch (\Throwable $e) {
            // không làm hỏng UX nếu mail lỗi
            report($e);
        }

        return back()->with('success', 'Cảm ơn bạn đã liên hệ, chúng tôi sẽ phản hồi sớm.');
    }

}
