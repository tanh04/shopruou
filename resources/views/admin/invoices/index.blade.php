@extends('admin_layout')
@section('admin_content')

<div class="panel panel-default">
  <div class="panel-heading d-flex justify-content-between align-items-center">
    <strong>BÁO CÁO DOANH THU THEO NGÀY</strong>
    <div>
      <a class="btn btn-success btn-sm" href="{{ route('views.export', ['format'=>'excel','from'=>$from,'to'=>$to]) }}">
        <i class="fa fa-file-excel-o"></i> Excel
      </a>
      <a class="btn btn-info btn-sm" href="{{ route('views.export', ['format'=>'csv','from'=>$from,'to'=>$to]) }}">
        <i class="fa fa-file-text-o"></i> CSV
      </a>
      <a class="btn btn-danger btn-sm" href="{{ route('views.export', ['format'=>'pdf','from'=>$from,'to'=>$to]) }}">
        <i class="fa fa-file-pdf-o"></i> PDF
      </a>
    </div>
  </div>

  <div class="panel-body">
    <form class="row g-2" method="GET" action="{{ route('views.index') }}">
      <div class="col-md-3">
        <label class="small">Từ ngày</label>
        <input type="date" class="form-control" name="from" value="{{ $from }}">
      </div>
      <div class="col-md-3">
        <label class="small">Đến ngày</label>
        <input type="date" class="form-control" name="to" value="{{ $to }}">
      </div>
      <div class="col-md-3 align-self-end">
        <button class="btn btn-primary"><i class="fa fa-search"></i> Lọc</button>
        <a class="btn btn-default" href="{{ route('views.index') }}">Reset</a>
      </div>
    </form>

    <hr>

    <div class="row text-center">
      <div class="col-sm-3"><h5>Đơn hàng</h5><h3>{{ number_format($totals['orders']) }}</h3></div>
      <div class="col-sm-3"><h5>Số lượng</h5><h3>{{ number_format($totals['quantity']) }}</h3></div>
      <div class="col-sm-3"><h5>Doanh thu</h5><h3>{{ number_format($totals['sales'],0,',','.') }} đ</h3></div>
      <div class="col-sm-3"><h5>Lợi nhuận</h5><h3>{{ number_format($totals['profit'],0,',','.') }} đ</h3></div>
    </div>

    <div class="table-responsive m-t">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Ngày</th>
            <th class="text-right">Số đơn</th>
            <th class="text-right">Số lượng</th>
            <th class="text-right">Doanh thu</th>
            <th class="text-right">Lợi nhuận</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rows as $r)
            <tr>
              <td>{{ $r->period }}</td>
              <td class="text-right">{{ number_format($r->orders) }}</td>
              <td class="text-right">{{ number_format($r->quantity) }}</td>
              <td class="text-right">{{ number_format($r->sales,0,',','.') }} đ</td>
              <td class="text-right">{{ number_format($r->profit,0,',','.') }} đ</td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted">Không có dữ liệu trong khoảng này</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@endsection
