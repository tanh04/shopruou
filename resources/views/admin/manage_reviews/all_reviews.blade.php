@extends('admin_layout')
@section('admin_content')

<div class="table-agile-info">
  <div class="panel panel-default">
    <div class="panel-heading">
      LIỆT KÊ ĐÁNH GIÁ
    </div>

    <!-- Thông báo -->
    <div id="flash-message" style="min-height: 40px; margin-bottom: 20px;">
      @if (session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
      @endif
      @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif
    </div>

    <div class="row w3-res-tb">
      <div class="col-sm-5">
        <form method="GET" action="{{ route('all_reviews') }}" class="mb-3 form-inline">
          <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm kiếm..." class="form-control mr-2">

          <select name="status" class="form-control mr-2">
            <option value="">-- Trạng thái --</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Hiện</option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Ẩn</option>
          </select>

          <button class="btn btn-primary">Tìm kiếm</button>
          <a href="{{ route('all_reviews') }}" class="btn btn-secondary">Xóa lọc</a>
        </form>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-striped b-t b-light">
        <thead>
          <tr>
            <th style="width: 6%; text-align: center;">Mã</th>
            <th style="width: 20%; text-align: center;">Sản phẩm</th>
            <th style="width: 10%; text-align: center;">Khách hàng</th>
            <th style="width: 10%; text-align: center;">Số sao</th>
            <th style="width: 24%; text-align: center;">Bình luận</th>
            <th style="width: 12%; text-align: center;">Trạng thái</th>
            <th style="width: 18%; text-align: center;">Hành động</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($reviews as $review)
            @php
              $filled = str_repeat('★', (int) $review->rating);
              $empty  = str_repeat('☆', max(0, 5 - (int) $review->rating));
              $status = (int) ($review->status ?? 0);
              $statusBadge = $status === 1 ? 'bg-success' : 'bg-secondary';
              $statusText  = $status === 1 ? 'HIỆN' : 'ẨN';
            @endphp
            <tr>
              <td class="text-center">{{ $review->review_id }}</td>

              <td class="text-center">
                @if($review->product)
                  <div>{{ $review->product->product_name }}</div>
                  <small class="text-muted">#{{ $review->product->product_id }}</small>
                @else
                  <em class="text-muted">Đã xoá / Không tìm thấy</em>
                @endif
              </td>

              <td class="text-center">
                {{ $review->user->name ?? 'Ẩn danh' }}
                @if($review->user?->email)
                  <div><small class="text-muted">{{ $review->user->email }}</small></div>
                @endif
              </td>

              <td class="text-center" style="font-size:15px; letter-spacing:1px;">
                <strong>{{ $filled }}{{ $empty }}</strong>
                <div><small class="text-muted">{{ $review->rating }}/5</small></div>
              </td>

              <td class="text-left">
                @if($review->comment)
                  {{ \Illuminate\Support\Str::limit($review->comment, 120) }}
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>

              {{-- CỘT TRẠNG THÁI: chỉ hiển thị badge --}}
              <td class="text-center">
                <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
              </td>

              {{-- CỘT HÀNH ĐỘNG: Duyệt/Hiện, Ẩn, Xoá --}}
              <td class="text-center">
                @if ($status === 0)
                  {{-- Duyệt/Hiện --}}
                  <form action="{{ route('admin.manage_reviews.toggle', $review) }}" method="POST" class="d-inline">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="1">
                    <button type="submit" class="btn btn-success btn-sm">Duyệt/Hiện</button>
                  </form>
                @endif

                @if ($status === 1)
                  {{-- Ẩn --}}
                  <form action="{{ route('admin.manage_reviews.toggle', $review) }}" method="POST" class="d-inline">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="0">
                    <button type="submit" class="btn btn-warning btn-sm">Ẩn</button>
                  </form>
                @endif

                {{-- Xoá --}}
                <form action="{{ route('admin.manage_reviews.destroy', $review) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Xoá đánh giá này?');">
                  @csrf @method('DELETE')
                  <button class="btn btn-danger btn-sm">Xoá</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Legend trạng thái --}}
    <div class="mt-3" style="padding: 10px 15px;">
      <strong>Trạng thái đánh giá:</strong>
      <span class="badge bg-success"   style="margin-left:8px;">HIỆN (1)</span>
      <span class="badge bg-secondary" style="margin-left:8px;">ẨN (0)</span>
    </div>

    {{-- Phân trang --}}
    <footer class="panel-footer">
      @include('partials.pagination', ['paginator' => $reviews , 'infoLabel' => 'review'])
    </footer>
  </div>
</div>

{{-- Thêm đoạn script để ẩn sau 3 giây --}}
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const flash = document.getElementById('flash-message');
    if(flash){
      setTimeout(() => {
        flash.style.transition = "opacity 0.5s";
        flash.style.opacity = 0;
        setTimeout(() => flash.remove(), 500); // remove sau khi mờ
      }, 3000);
    }
  });
</script>

@endsection
