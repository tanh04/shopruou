@extends('admin_layout')
@section('title', 'Báo cáo')
@section('admin_content')

<style>
  /* Layout */
  .report-wrap { padding: 12px 16px; }

  /* Flash messages */
  .flash-box { min-height: 32px; margin-bottom: 12px; }

  /* Card */
  .report-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 10px;
    margin-bottom: 16px; padding: 14px 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
  }
  .section-title { font-weight: 700; margin: 0 0 12px; color: #111827; }

  /* Table */
  .table-clean {
    width: 100%; border-collapse: separate; border-spacing: 0;
    border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden;
    table-layout: fixed;
  }
  .table-clean th, .table-clean td {
    padding: 10px 12px; text-align: center; vertical-align: middle;
  }
  .table-clean thead th {
    background: #f8fafc; border-bottom: 1px solid #e5e7eb;
    font-weight: 600; color: #334155;
  }
  .table-clean tbody tr:nth-child(odd) { background: #fff; }
  .table-clean tbody tr:nth-child(even) { background: #f9fafb; }

  /* Stat cards */
  .stat-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(200px,1fr)); gap: 12px; margin: 12px 0 20px; }
  .stat-card {
    background: #fff; border: 1px solid #eef2f7; border-radius: 10px;
    padding: 12px 14px; box-shadow: 0 1px 2px rgba(0,0,0,.05);
  }
  .stat-title { font-size: .85rem; color: #64748b; margin: 0; }
  .stat-value { font-size: 1.3rem; font-weight: 700; margin-top: 4px; color: #0f172a; }

  /* Filter form */
  .filter-form { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 14px; }
  .filter-form label { font-size: .8rem; color: #6b7280; margin-bottom: 4px; }
  .filter-form .form-control { height: 34px; }
</style>

<div class="report-wrap">

  {{-- Flash messages --}}
  <div class="flash-box">
    @if (session('message')) <div class="alert alert-success">{{ session('message') }}</div> @endif
    @if (session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif
  </div>

  {{-- Bộ lọc --}}
  <!-- <form method="GET" class="filter-form">
    <div>
      <label>Từ ngày</label>
      <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
    </div>
    <div>
      <label>Đến ngày</label>
      <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
    </div>

    @php
      $currentStatus = is_array($paidStatuses ?? null)
        ? (count($paidStatuses) === 1 ? $paidStatuses[0] : '')
        : ($paidStatuses ?? '');
    @endphp

    <div>
      <label>Trạng thái</label>
      <select name="status" class="form-control" style="min-width:180px">
        <option value="">— Tất cả trạng thái —</option>
        @foreach(\App\Models\Order::getStatusOptions() as $st)
          <option value="{{ $st }}" {{ $currentStatus === $st ? 'selected' : '' }}>{{ $st }}</option>
        @endforeach
      </select>
    </div>

    <div class="align-self-end">
      <button type="submit" class="btn btn-primary btn-sm" style="margin-top: 27px;">Lọc</button>
    </div>
  </form> -->

  @php
    $vn = fn($n) => number_format((float)$n, 0, ',', '.') . ' đ';
    $sumRevenue = collect($revenueByDate)->sum('total_revenue');
  @endphp

  {{-- Thống kê nhanh --}}
  <div class="stat-grid">
    <div class="stat-card">
      <p class="stat-title"><i class="fa fa-list-ul"></i> Tổng số đơn</p>
      <p class="stat-value">{{ number_format($totalOrders) }}</p>
    </div>
    <div class="stat-card">
      <p class="stat-title"><i class="fa fa-users"></i> Tổng khách hàng</p>
      <p class="stat-value">{{ number_format($totalCustomers) }}</p>
    </div>
    <div class="stat-card">
      <p class="stat-title"><i class="fa fa-coins"></i> Doanh thu (lọc)</p>
      <p class="stat-value">{{ $vn($sumRevenue) }}</p>
    </div>
  </div>

  {{-- Các bảng thống kê --}}
  <div class="row g-3">
    {{-- Danh mục --}}
    <div class="col-lg-6">
      <div class="report-card">
        <h5 class="section-title">Doanh thu theo danh mục</h5>
        <div class="table-container">
          <table class="table-clean">
            <thead><tr><th>Danh mục</th><th>Tổng SL</th><th>Tổng doanh thu</th></tr></thead>
            <tbody>
              @forelse($categoryRevenue as $row)
                <tr>
                  <td>{{ $row->category_name ?? $row->category_id }}</td>
                  <td>{{ number_format($row->total_qty ?? 0) }}</td>
                  <td>{{ $vn($row->total_revenue ?? 0) }}</td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-muted">Không có dữ liệu</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
  
      {{-- Thương hiệu --}}
      <div class="report-card">
        <h5 class="section-title">Doanh thu theo thương hiệu</h5>
        <div class="table-container">
          <table class="table-clean">
            <thead><tr><th>Thương hiệu</th><th>Tổng SL</th><th>Tổng doanh thu</th></tr></thead>
            <tbody>
              @forelse($brandRevenue as $row)
                <tr>
                  <td>{{ $row->brand_name ?? $row->category_id }}</td>
                  <td>{{ number_format($row->total_qty ?? 0) }}</td>
                  <td>{{ $vn($row->total_revenue ?? 0) }}</td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-muted">Không có dữ liệu</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    
      {{-- Sản phẩm --}}
      <div class="report-card">
        <h5 class="section-title">Doanh thu theo sản phẩm</h5>
        <div class="table-container">
          <table class="table-clean">
            <thead><tr><th>Sản phẩm</th><th>Tổng SL</th><th>Tổng doanh thu</th></tr></thead>
            <tbody>
              @forelse($productRevenue as $row)
                <tr>
                  <td>{{ $row->product_name ?? $row->product_id }}</td>
                  <td>{{ number_format($row->total_qty ?? 0) }}</td>
                  <td>{{ $vn($row->total_revenue ?? 0) }}</td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-muted">Không có dữ liệu</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- Thanh toán --}}
      <div class="report-card">
        <h5 class="section-title">Theo phương thức thanh toán</h5>
        <div class="table-container">
          <table class="table-clean">
            <thead><tr><th>Phương thức</th><th>Số đơn</th><th>Doanh thu</th></tr></thead>
            <tbody>
              @forelse($paymentBreakdown as $row)
                <tr>
                  <td>{{ $row->payment_method ?? 'Khác/Chưa rõ' }}</td>
                  <td>{{ number_format($row->order_count ?? 0) }}</td>
                  <td>{{ $vn($row->total_revenue ?? 0) }}</td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-muted">Không có dữ liệu</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

    </div>

    {{-- Theo ngày --}}
    <div class="col-lg-6">
      <div class="report-card">
        <h5 class="section-title">Doanh thu theo ngày</h5>
        <div class="table-container">
          <table class="table-clean">
            <thead><tr><th>Ngày</th><th>Số đơn</th><th>Doanh thu</th></tr></thead>
            <tbody>
              @forelse($revenueByDate as $row)
                <tr>
                  <td>{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                  <td>{{ number_format($row->order_count ?? 0) }}</td>
                  <td>{{ $vn($row->total_revenue ?? 0) }}</td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-muted">Không có dữ liệu</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
 
      {{-- Theo tháng --}}
  
      <div class="report-card">
        <h5 class="section-title">Doanh thu theo tháng</h5>
        <div class="table-container">
          <table class="table-clean">
            <thead><tr><th>Tháng</th><th>Số đơn</th><th>Doanh thu</th></tr></thead>
            <tbody>
              @forelse($revenueByMonth as $row)
                <tr>
                  <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $row->month)->format('m/Y') }}</td>
                  <td>{{ number_format($row->order_count ?? 0) }}</td>
                  <td>{{ $vn($row->total_revenue ?? 0) }}</td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-muted">Không có dữ liệu</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
 
      {{-- Theo năm --}}
   
      <div class="report-card">
        <h5 class="section-title">Doanh thu theo năm</h5>
        <div class="table-container">
          <table class="table-clean">
            <thead><tr><th>Năm</th><th>Số đơn</th><th>Doanh thu</th></tr></thead>
            <tbody>
              @forelse($revenueByYear as $row)
                <tr>
                  <td>{{ $row->year }}</td>
                  <td>{{ number_format($row->order_count ?? 0) }}</td>
                  <td>{{ $vn($row->total_revenue ?? 0) }}</td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-muted">Không có dữ liệu</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>

<style>
.report-card {
  margin-bottom: 20px;
  padding: 16px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}
.table-container {
  max-height: 400px;   /* chiều cao tương ứng 10 dòng */
  overflow-y: auto;    /* scroll dọc */
  overflow-x: auto;    /* scroll ngang nếu cần */
  border: 1px solid #eee;
  border-radius: 6px;
  max-height: calc(10 * 42px); /* 42px là chiều cao trung bình 1 dòng */
}

/* Custom scrollbar */
.table-container::-webkit-scrollbar {
  width: 8px;
}
.table-container::-webkit-scrollbar-thumb {
  background: #ccc;
  border-radius: 10px;
}
.table-container::-webkit-scrollbar-thumb:hover {
  background: 
  #999;
}
.table-container thead th {
  position: sticky;
  top: 0;
  background: #f8fafc; /* giữ màu nền header khi scroll */
  z-index: 1;
}

</style>
@endsection
