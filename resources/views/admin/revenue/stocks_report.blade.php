@extends('admin_layout')
@section('title', 'Báo cáo tồn kho')
@section('admin_content')

<div class="row">
    <!-- DANH MỤC -->
    <div class="col-lg-6">
        <section class="panel">
            <header class="panel-heading">DANH MỤC (THEO SỐ SẢN PHẨM)</header>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Danh mục</th>
                                <th>Số sản phẩm</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $c)
                                <tr>
                                    <td>{{ $c->name }}</td>
                                    <td>{{ $c->total }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <canvas id="categoryChart" style="height: 400px;"></canvas>
            </div>
        </section>
    </div>

    <!-- THƯƠNG HIỆU -->
    <div class="col-lg-6">
        <section class="panel">
            <header class="panel-heading">THƯƠNG HIỆU (THEO SỐ SẢN PHẨM)</header>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Thương hiệu</th>
                                <th>Số sản phẩm</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($brands as $b)
                                <tr>
                                    <td>{{ $b->name }}</td>
                                    <td>{{ $b->total }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <canvas id="brandChart" style="height: 400px;"></canvas>
            </div>
        </section>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const categoryLabels = {!! json_encode($categories->pluck('name')) !!};
    const categoryCounts = {!! json_encode($categories->pluck('total')) !!};

    const brandLabels = {!! json_encode($brands->pluck('name')) !!};
    const brandCounts = {!! json_encode($brands->pluck('total')) !!};

    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: categoryLabels,
            datasets: [{
                label: 'Số sản phẩm',
                data: categoryCounts,
                backgroundColor: '#4f46e5'
            }]
        }
    });

    new Chart(document.getElementById('brandChart'), {
        type: 'bar',
        data: {
            labels: brandLabels,
            datasets: [{
                label: 'Số sản phẩm',
                data: brandCounts,
                backgroundColor: '#f43f5e'
            }]
        }
    });
</script>
@endsection
