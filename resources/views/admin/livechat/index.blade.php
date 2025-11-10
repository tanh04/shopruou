@extends('admin_layout')
@section('admin_content')

<div class="row" style="min-height:70vh;">
  <div class="col-sm-4">
    <h4><i class="fa fa-comments"></i> Live Chat – Operator</h4>
    <ul id="conv-list" class="list-group" style="max-height:65vh;overflow:auto;"></ul>
    <div class="mt-2">
      <button id="tab-open" class="btn btn-primary btn-sm">Đang mở</button>
      <button id="tab-closed" class="btn btn-secondary btn-sm">Đã đóng</button>
    </div>
  </div>

  <div class="col-sm-8" style="border-left:1px solid #eee;">
    <div id="thread" style="height:55vh;overflow:auto;background:#fafafa;padding:10px;border:1px solid #eee;border-radius:6px;"></div>
    <div class="input-group mt-2">
      <input id="msg" type="text" class="form-control" placeholder="Nhập tin nhắn...">
      <button id="send" class="btn btn-success">Gửi</button>
      <button id="close" class="btn btn-warning">Đóng hội thoại</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  let currentId = null;
  let lastId = null;
  let mode = 'open';
  const list = document.getElementById('conv-list');
  const thread = document.getElementById('thread');
  const msg = document.getElementById('msg');

  // hiển thị danh sách hội thoại
  function li(c){
  const el = document.createElement('li');
  el.className = 'list-group-item';
  el.style.cursor='pointer';
  el.style.borderRadius='10px';
  el.style.position='relative';
  el.dataset.senderId = c.sender_id;

  // Fallback: nếu không có tên -> hiển thị User #id
  const name = (c.customer_name && c.customer_name.trim() !== '') 
                ? c.customer_name 
                : `User #${c.sender_id}`;

  el.innerHTML = `
    <div style="font-weight:600">${name}</div>
    <div style="font-size:12px;color:#777">${new Date(c.last_message_at).toLocaleString()}</div>
    ${c.unread > 0 ? '<span style="position:absolute;right:14px;top:12px;width:10px;height:10px;border-radius:50%;background:#dc3545;box-shadow:0 0 0 2px #fff;"></span>' : ''}
  `;
  el.onclick = ()=>loadThread(c.id, el);
  return el;
}



  async function loadList(){
  const res = await fetch(`{{ route('admin.livechat.conversations') }}`);
  const data = await res.json();
  const listData = data.conversations || [];

  // Xóa toàn bộ danh sách cũ
  list.innerHTML = '';
  // Render mới theo user
  listData.forEach(c => list.appendChild(li(c)));
}



  // hiển thị tin nhắn
  function bubble(m){
    const wrap = document.createElement('div');
    wrap.style.textAlign = (m.direction==='out') ? 'right' : 'left';

    const b = document.createElement('div');
    b.style.display='inline-block';
    b.style.padding='8px 10px';
    b.style.margin='4px';
    b.style.border='1px solid #e5e7eb';
    b.style.borderRadius='10px';
    b.style.background = (m.direction==='out') ? '#d1e7ff' : '#fff';

    const small = document.createElement('div');
    small.style.fontSize='12px';
    small.style.color='#888';
    small.textContent = m.sender_name + ' • ' + (new Date(m.created_at)).toLocaleTimeString();

    const text = document.createElement('div');
    text.textContent = m.body;

    b.appendChild(text);
    b.appendChild(small);
    wrap.appendChild(b);

    thread.appendChild(wrap);
    thread.scrollTop = thread.scrollHeight;
  }

  async function loadThread(id, boxEl){
  currentId = id; lastId = null; thread.innerHTML='';
  // đánh dấu đã đọc tin từ user
  fetch(`{{ url('admin/livechat/read') }}/${id}`, {
    method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}
  }).then(()=> boxEl?.querySelector('span')?.remove());
  poll(); // nạp tin nhắn bên phải như cũ
}


  async function poll(){
    if(!currentId) return;
    let url = `{{ url('admin/livechat/messages') }}/${currentId}`;
    if(lastId) url += `?after_id=${lastId}`;
    const res = await fetch(url);
    const data = await res.json();
    (data.messages||[]).forEach(m=>{ bubble(m); lastId = m.id; });
    setTimeout(poll, 2000);
  }

  async function send(){
    if(!currentId) return alert('Chọn hội thoại trước.');
    const body = msg.value.trim(); if(!body) return;
    const res = await fetch(`{{ url('admin/livechat/send') }}/${currentId}`, {
      method:'POST',
      headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
      body: JSON.stringify({body})
    });
    if(res.ok){ msg.value=''; } else { alert('Gửi thất bại'); }
  }

  async function closeConv(){
    if(!currentId) return;
    const res = await fetch(`{{ url('admin/livechat/close') }}/${currentId}`, {
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}
    });
    if(res.ok){ currentId=null; thread.innerHTML=''; loadList(); }
  }

  document.getElementById('send').onclick = send;
  document.getElementById('close').onclick = closeConv;
  document.getElementById('tab-open').onclick = ()=>{ mode='open'; loadList(); };
  document.getElementById('tab-closed').onclick = ()=>{ mode='closed'; loadList(); };

  loadList();
  let knownIds = new Set();

// hàm này chỉ cập nhật danh sách box (như Zalo) — không tự mở
async function refreshConversationsLikeZalo(){
  const convs = await loadList(); // render lại danh sách hội thoại
  const nowIds = new Set(convs.map(c => c.id));

  // Nếu có hội thoại mới (user vừa nhắn lần đầu)
  for (const c of convs) {
    if (!knownIds.has(c.id)) {
      // Thêm box mới bên trái (loadList đã làm việc này rồi)
      console.log("📩 Cuộc chat mới:", c.customer_name);
      // Không tự mở nữa — admin sẽ click thủ công để xem
    }
  }

  // Cập nhật danh sách ID hiện có
  knownIds = nowIds;
}

// chạy lần đầu
refreshConversationsLikeZalo();
// gọi lại mỗi 2 giây để kiểm tra có user mới không
setInterval(refreshConversationsLikeZalo, 2000);

})();
</script>
@endpush
