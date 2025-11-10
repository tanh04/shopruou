<h4>Phản hồi khách hàng</h4>
<form action="{{ route('admin.contacts.reply', $contact->id) }}" method="POST" class="mb-3">
  @csrf
  <div class="form-group">
    <label>Nội dung phản hồi (email sẽ được gửi cho khách)</label>
    <textarea name="reply_message" class="form-control" rows="6" required>{{ old('reply_message', $contact->reply_message) }}</textarea>
  </div>
  <button class="btn btn-primary"><i class="fa fa-paper-plane"></i> Gửi phản hồi</button>
</form>

@if($contact->replied_at)
  <div class="alert alert-info">
    <strong>Đã phản hồi lúc:</strong> {{ $contact->replied_at->format('d/m/Y H:i') }}<br>
    {!! nl2br(e($contact->reply_message)) !!}
  </div>
@endif
