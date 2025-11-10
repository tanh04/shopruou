<p>Chào {{ $contact->name }},</p>

<p>Cảm ơn bạn đã liên hệ. Phản hồi từ bộ phận hỗ trợ của chúng tôi:</p>

<p>{!! nl2br(e($reply)) !!}</p>

<hr>
<p>Thông tin bạn đã gửi:</p>
<ul>
    <li>Chủ đề: {{ $contact->subject ?: '—' }}</li>
    <li>Nội dung: {!! nl2br(e($contact->message)) !!}</li>
</ul>

<p>Trân trọng,<br>Đội ngũ hỗ trợ</p>
