@extends('admin_layout')
@section('title', 'Báo cáo')
@section('admin_content')
<div class="container-fluid">
    <style type="text/css">
        .title_thongke{ text-align:center; font-size:20px; font-weight:bold; }
        .list_views{ list-style: disc; padding-left: 16px; }
        table.table.table-bordered.table-dark { background:#32383e; }
        table.table.table-bordered.table-dark tr th, 
        table.table.table-bordered.table-dark tr td { color:#fff; }
    </style>

    <div class="row">
        <p class="title_thongke">Thống kê đơn hàng doanh số</p>

        <form id="form-filter" autocomplete="off" class="row g-2">
            @csrf
            <div class="col-md-2">
                <p>Từ ngày:
                    <input type="text" id="datepicker" class="form-control" placeholder="YYYY-MM-DD">
                </p>
            </div>
            <div class="col-md-2">
                <p>Đến ngày:
                    <input type="text" id="datepicker2" class="form-control" placeholder="YYYY-MM-DD">
                </p>
            </div>
            <div class="col-md-2">
                <p>Lọc theo:
                    <select id="dashboard-filter" class="dashboard-filter form-control">
                        <option value="">--Chọn--</option>
                        <option value="7ngay">7 ngày qua</option>
                        <option value="thangtruoc">Tháng trước</option>
                        <option value="thangnay">Tháng này</option>
                        <option value="365ngayqua">365 ngày qua</option>
                    </select>
                </p>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <input type="button" id="btn-dashboard-filter" class="btn btn-primary btn-sm" value="Lọc kết quả">
            </div>
        </form>

        <div class="col-md-12">
            <div id="myfirstchart" style="height: 250px;"></div>
        </div>
    </div>

    <div class="row mt-4">
        <p class="title_thongke">Thống kê truy cập</p>
        <table class="table table-bordered table-dark">
            <thead>
                <tr>
                    <th scope="col">Đang online</th>
                    <th scope="col">Tổng tháng trước</th>
                    <th scope="col">Tổng tháng này</th>
                    <th scope="col">Tổng một năm</th>
                    <th scope="col">Tổng truy cập</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $visitor_count }}</td>
                    <td>{{ $visitor_last_month }}</td>
                    <td>{{ $visitor_this_month }}</td>
                    <td>{{ $visitor_year_count }}</td>
                    <td>{{ $visitors_total }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="row mt-3">
        <div class="col-md-4 col-xs-12">
            <p class="title_thongke">Thống kê tổng sản phẩm bài viết đơn hàng</p>
            <div id="donut" class="morris-donut-inverse"></div>
        </div>

        <div class="col-md-4 col-xs-12">
            <h3>Bài viết xem nhiều</h3>
            <ol class="list_views">
                @foreach($post_views as $post)
                    <li>
                        <a target="_blank" href="{{ url('/bai-viet/'.$post->post_slug) }}" style="color:black">
                            {{ $post->post_title }} |
                            <span style="color:red">{{ $post->post_views }}</span>
                        </a>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- jQuery + jQuery UI datepicker + Raphael + Morris --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link  href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" rel="stylesheet">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
    <link  href="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css" rel="stylesheet"/>

    <script>
        $(function () {
            // Datepicker
            $("#datepicker, #datepicker2").datepicker({
                dateFormat: "yy-mm-dd"
            });

            // CSRF
            $.ajaxSetup({
                headers: {'X-CSRF-TOKEN': $('input[name="_token"]').val()}
            });

            // Khởi tạo chart
            var chart = new Morris.Area({
                element: 'myfirstchart',
                parseTime: false,
                lineColors: ['#0b62a4','#7a92a3','#4da74d','#d58665'],
                xkey: 'period',
                ykeys: ['order','sales','profit','quantity'],
                labels: ['Đơn','Doanh thu','Lợi nhuận','Số lượng']
            });

            // Load mặc định 30 ngày gần nhất
            loadDaysOrder();

            function loadDaysOrder(){
                $.post('{{ route("admin.daysOrder") }}', {}, function(res){
                    chart.setData(res);
                });
            }

            // Lọc theo khoảng ngày
            $('#btn-dashboard-filter').on('click', function(){
                let from = $('#datepicker').val();
                let to   = $('#datepicker2').val();
                if(!from || !to){ alert('Chọn đầy đủ từ ngày / đến ngày'); return; }

                $.post('{{ route("admin.filterByDate") }}', {from_date: from, to_date: to}, function(res){
                    chart.setData(res);
                });
            });

            // Lọc nhanh (7 ngày / tháng này / tháng trước / 365 ngày)
            $('#dashboard-filter').on('change', function(){
                var v = $(this).val();
                if(!v) return;

                $.post('{{ route("admin.dashboardFilter") }}', {dashboard_value: v}, function(res){
                    chart.setData(res);
                });
            });

            // Donut totals
            $.post('{{ route("admin.totalsDonut") }}', {}, function(res){
                new Morris.Donut({
                    element: 'donut',
                    data: res,
                    formatter: function (x) { return x }
                });
            });
        });
    </script>
@endpush
