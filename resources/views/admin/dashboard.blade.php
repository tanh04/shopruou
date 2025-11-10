@extends('admin_layout')

@section('title', 'Dashboard')

@section('admin_content')
  {{-- Tiles KPI --}}
  <div class="market-updates">
    <!-- <div class="col-md-3 market-update-gd">
      <div class="market-update-block clr-block-2">
        <div class="col-md-4 market-update-right"><i class="fa fa-eye"></i></div>
        <div class="col-md-8 market-update-left">
          <h4>Visitors (online)</h4>
          <h3>{{ number_format($kpi['visitors']) }}</h3>
          <p>Trong 10 phút gần nhất</p>
        </div><div class="clearfix"></div>
      </div>
    </div> -->

    <div class="col-md-3 market-update-gd">
      <div class="market-update-block clr-block-1">
        <div class="col-md-4 market-update-right"><i class="fa fa-users"></i></div>
        <div class="col-md-8 market-update-left">
          <h4>Users</h4>
          <h3>{{ number_format($kpi['users']) }}</h3>
          <p>Tổng tài khoản</p>
        </div><div class="clearfix"></div>
      </div>
    </div>

    <div class="col-md-3 market-update-gd">
      <div class="market-update-block clr-block-3">
        <div class="col-md-4 market-update-right"><i class="fa fa-usd"></i></div>
        <div class="col-md-8 market-update-left">
          <h4>Sales (30d)</h4>
          <h3>{{ number_format($kpi['sales'], 0, ',', '.') }} đ</h3>
          <p>Tổng doanh thu</p>
        </div><div class="clearfix"></div>
      </div>
    </div>

    <div class="col-md-3 market-update-gd">
      <div class="market-update-block clr-block-4">
        <div class="col-md-4 market-update-right"><i class="fa fa-shopping-cart"></i></div>
        <div class="col-md-8 market-update-left">
          <h4>Orders (30d)</h4>
          <h3>{{ number_format($kpi['orders']) }}</h3>
          <p>Số đơn hoàn tất</p>
        </div><div class="clearfix"></div>
      </div>
    </div>
    <div class="clearfix"> </div>
  </div>

  {{-- Row 1 --}}
  <div class="row">
    <div class="col-md-6">
      <div class="agileinfo-grap">
        <div class="agileits-box">
          <header class="agileits-box-header clearfix"><h3>Doanh thu &amp; Lợi nhuận (30 ngày)</h3></header>
          <div class="agileits-box-body clearfix">
            <div id="chart-revenue-profit" style="height:280px;"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="agileinfo-grap">
        <div class="agileits-box">
          <header class="agileits-box-header clearfix"><h3>Đơn hàng &amp; Số lượng (30 ngày)</h3></header>
          <div class="agileits-box-body clearfix">
            <div id="chart-orders-qty" style="height:280px;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Row 2 --}}
  <div class="row">
    <div class="col-md-6">
      <div class="agileinfo-grap">
        <div class="agileits-box">
          <header class="agileits-box-header clearfix"><h3>Tỷ trọng Danh mục (số sản phẩm)</h3></header>
          <div class="agileits-box-body clearfix">
            <div id="chart-categories-share" style="height:280px;"></div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="agileinfo-grap">
        <div class="agileits-box">
          <header class="agileits-box-header clearfix"><h3>Tỷ trọng Thương hiệu (số sản phẩm)</h3></header>
          <div class="agileits-box-body clearfix">
            <div id="chart-brands-share" style="height:280px;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Row 3 --}}
  <div class="row">
    <div class="col-md-12">
      <div class="agileinfo-grap">
        <div class="agileits-box">
          <header class="agileits-box-header clearfix"><h3>Top 10 sản phẩm bán chạy (30 ngày)</h3></header>
          <div class="agileits-box-body clearfix">
            <div id="chart-top-products" style="height:320px;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

    <div class="pull-right">
    <a class="btn btn-success btn-sm"
      href="{{ route('views.export', ['format'=>'excel'] + request()->query()) }}">
      <i class="fa fa-file-excel-o"></i> Excel
    </a>
    <a class="btn btn-danger btn-sm"
      href="{{ route('views.export', ['format'=>'pdf'] + request()->query()) }}">
      <i class="fa fa-file-pdf-o"></i> PDF
    </a>
</div>
@endsection

@push('scripts')
<script>
(function ($) {
  $(function () {

    // --- helpers dùng chung ---
    function getJSON(url, ok, err) {
      $.getJSON(url).done(ok).fail(function (xhr) {
        console.error('GET ' + url + ' failed:', xhr.status, xhr.responseText || xhr.statusText);
        if (err) err(xhr);
      });
    }
    function ensureDonutData(data, emptyLabel) {
      return (Array.isArray(data) && data.length)
        ? data
        : [{ label: emptyLabel || 'Không có dữ liệu', value: 1 }];
    }
    function ensureSeriesData(data, emptyRow) {
      return (Array.isArray(data) && data.length)
        ? data
        : [emptyRow];
    }

    // --- Area: Doanh thu & Lợi nhuận ---
    var chartRevenueProfit = new Morris.Area({
      element: 'chart-revenue-profit',
      data: [{ period: '—', sales: 0, profit: 0 }], // placeholder
      xkey: 'period',
      ykeys: ['sales', 'profit'],
      labels: ['Doanh thu', 'Lợi nhuận'],
      parseTime: false,
      hideHover: 'auto',
      fillOpacity: 0.6,
      behaveLikeLine: true,
      resize: true,
      yLabelFormat: function (y) {
        try { return y.toLocaleString('vi-VN') + ' đ'; } catch (e) { return y + ' đ'; }
      }
    });
    getJSON("{{ url('/chart/revenue-profit') }}", function (data) {
      setTimeout(function () {
        chartRevenueProfit.setData(ensureSeriesData(data, { period: '—', sales: 0, profit: 0 }));
        chartRevenueProfit.redraw();
      }, 0);
    });

    // --- Bar: Đơn hàng & Số lượng ---
    var chartOrdersQty = new Morris.Bar({
      element: 'chart-orders-qty',
      data: [{ period: '—', orders: 0, quantity: 0 }], // placeholder
      xkey: 'period',
      ykeys: ['orders', 'quantity'],
      labels: ['Đơn hàng', 'Số lượng'],
      parseTime: false,
      hideHover: 'auto',
      resize: true
    });
    getJSON("{{ url('/chart/orders-qty') }}", function (data) {
      setTimeout(function () {
        chartOrdersQty.setData(ensureSeriesData(data, { period: '—', orders: 0, quantity: 0 }));
        chartOrdersQty.redraw();
      }, 0);
    });

    // --- Donut: Tỷ trọng Danh mục ---
    var chartCategories = new Morris.Donut({
      element: 'chart-categories-share',
      data: [{ label: 'Đang tải…', value: 1 }], // placeholder
      resize: true,
      formatter: function (y) { return y + ' SP'; }
    });
    getJSON("{{ url('/chart/categories-share') }}",
      function (data) {
        setTimeout(function () {
          chartCategories.setData(ensureDonutData(data));
          chartCategories.redraw();
        }, 0);
      },
      function () {
        chartCategories.setData(ensureDonutData(null, 'Lỗi dữ liệu'));
        chartCategories.redraw();
      }
    );

    // --- Donut: Tỷ trọng Thương hiệu ---
    var chartBrands = new Morris.Donut({
      element: 'chart-brands-share',
      data: [{ label: 'Đang tải…', value: 1 }], // placeholder
      resize: true,
      formatter: function (y) { return y + ' SP'; }
    });
    getJSON("{{ url('/chart/brands-share') }}",
      function (data) {
        setTimeout(function () {
          chartBrands.setData(ensureDonutData(data));
          chartBrands.redraw();
        }, 0);
      },
      function () {
        chartBrands.setData(ensureDonutData(null, 'Lỗi dữ liệu'));
        chartBrands.redraw();
      }
    );

    // --- Bar: Top sản phẩm ---
    var chartTopProducts = new Morris.Bar({
      element: 'chart-top-products',
      data: [{ product: '—', qty: 0 }], // placeholder
      xkey: 'product',
      ykeys: ['qty'],
      labels: ['Số lượng'],
      hideHover: 'auto',
      parseTime: false,
      resize: true,
      xLabelAngle: 35
    });
    getJSON("{{ url('/chart/top-products') }}", function (data) {
      setTimeout(function () {
        chartTopProducts.setData(ensureSeriesData(data, { product: '—', qty: 0 }));
        chartTopProducts.redraw();
      }, 0);
    });

    // Redraw khi sidebar toggle / resize (đề phòng width=0 lúc init)
    $(window).on('resize', function () {
      chartRevenueProfit.redraw();
      chartOrdersQty.redraw();
      chartCategories.redraw();
      chartBrands.redraw();
      chartTopProducts.redraw();
    });

  });
})(jQuery);
</script>

@endpush
