{{-- resources/views/partials/sidebar.blade.php --}}
@php
    
    $priceRanges  = $priceRanges  ?? [
        ['key' => 'lt_500k',  'label' => 'Dưới 500K'],
        ['key' => '500k_1m',  'label' => 'Từ 500K - 1 triệu'],
        ['key' => '1m_2m',    'label' => 'Từ 1 triệu - 2 triệu'],
        ['key' => '2m_3m',    'label' => 'Từ 2 triệu - 3 triệu'],
        ['key' => '3m_5m',    'label' => 'Từ 3 triệu - 5 triệu'],
        ['key' => '5m_7m',    'label' => 'Từ 5 triệu - 7 triệu'],
        ['key' => '7m_10m',   'label' => 'Từ 7 triệu - 10 triệu'],
        ['key' => '10m_15m',  'label' => 'Từ 10 triệu - 15 triệu'],
        ['key' => '15m_20m',  'label' => 'Từ 15 triệu - 20 triệu'],
        ['key' => 'gt_20m',   'label' => 'Trên 20 triệu'],
    ];
    $rangeCounts  = $rangeCounts  ?? collect($priceRanges)->mapWithKeys(fn($r) => [$r['key'] => 0])->toArray();
    $selectedKeys = $selectedKeys ?? (array) request('ranges', []);
    $categories   = $categories   ?? collect();
    $brands       = $brands       ?? collect();
@endphp

<div class="left-sidebar">

@if(!isset($hideSidebar) || !$hideSidebar)
    {{-- LỌC THEO KHOẢNG GIÁ --}}
    <div class="panel-group" style="margin-bottom:15px;">
        <div class="panel panel-default">
            <div class="panel-heading" style="cursor:pointer" data-toggle="collapse" data-target="#price-collapse">
                <h4 class="panel-title" style="display:flex;justify-content:space-between;align-items:center;">
                    <span>LỌC THEO KHOẢNG GIÁ</span>
                    <span class="caret"></span>
                </h4>
            </div>

            <div id="price-collapse" class="panel-collapse collapse in">
                <div class="panel-body" style="padding:10px 15px;">

                    <form action="{{ route('product.filter_price') }}" method="GET" id="filters-form">
                        {{-- giữ tham số khác khi lọc --}}
                        @foreach(request()->except(['ranges','page']) as $k => $v)
                            @if(is_array($v))
                                @foreach($v as $vv)
                                    <input type="hidden" name="{{ $k }}[]" value="{{ $vv }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                            @endif
                        @endforeach

                        <ul class="list-unstyled" style="margin:0;">
                            @foreach($priceRanges as $r)
                                @php
                                    $checked = in_array($r['key'], $selectedKeys);
                                    $count   = $rangeCounts[$r['key']] ?? 0;
                                @endphp
                                <li style="display:flex;align-items:center;justify-content:space-between;padding:6px 0;border-bottom:1px solid #eee;">
                                    <label style="margin:0;display:flex;align-items:center;gap:8px;flex:1;">
                                        <input type="checkbox" name="ranges[]" value="{{ $r['key'] }}" {{ $checked ? 'checked' : '' }}>
                                        <span>{{ $r['label'] }}</span>
                                    </label>
                                    <span style="color:#c0392b;">({{ number_format($count,0,',','.') }})</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="text-right" style="margin-top:10px; display:flex; gap:5px; justify-content:flex-end;">
                            <button type="submit" class="btn btn-primary btn-sm">Áp dụng</button>
                            <a href="{{ url()->current() }}" class="btn btn-default btn-sm" style="margin-top: 15px; font-size: 15px">Khôi phục</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    {{-- DANH MỤC --}}
    <div class="panel-group" style="margin-bottom:15px;">
        <div class="panel panel-default">
            {{-- Header: DANH MỤC (click để xổ ra) --}}
            <div class="panel-heading" style="cursor:pointer" data-toggle="collapse" data-target="#category-collapse">
                <h4 class="panel-title d-flex justify-content-between align-items-center">
                    <span>DANH MỤC</span>
                    <span class="caret"></span>
                </h4>
            </div>

            {{-- Collapse body --}}
            <div id="category-collapse" class="panel-collapse collapse in">
                <div class="panel-body" style="padding:10px 15px;">
                    <div class="panel-group category-products" id="accordian"><!-- category-products -->
                        @foreach($parents as $parent)
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        @if($parent->children->isNotEmpty())
                                            {{-- Có con: click mở/đóng --}}
                                            <a data-toggle="collapse" data-parent="#accordian" href="#cat-{{ $parent->category_id }}">
                                                <span class="badge pull-right"><i class="fa fa-plus"></i></span>
                                                {{ $parent->category_name }}
                                            </a>
                                        @else
                                            {{-- Không có con: link thẳng --}}
                                            <a href="{{ url('category/'.$parent->category_id) }}">
                                                {{ $parent->category_name }}
                                            </a>
                                        @endif
                                    </h4>
                                </div>

                                @if($parent->children->isNotEmpty())
                                    <div id="cat-{{ $parent->category_id }}" class="panel-collapse collapse">
                                        <div class="panel-body">
                                            <ul>
                                                @foreach($parent->children as $child)
                                                    <li>
                                                        <a href="{{ url('category/'.$child->category_id) }}">
                                                            {{ $child->category_name }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div><!-- /category-products -->
                </div>
            </div>
        </div>
    </div>

    {{-- THƯƠNG HIỆU --}}
    <div class="panel-group" style="margin-bottom:15px;">
        <div class="panel panel-default">
            <div class="panel-heading" style="cursor:pointer" data-toggle="collapse" data-target="#brand-collapse">
                <h4 class="panel-title d-flex justify-content-between align-items-center">
                    <span>QUỐC GIA</span>
                    <span class="caret"></span>
                </h4>
            </div>
            <div id="brand-collapse" class="panel-collapse collapse in">
                <div class="panel-body" style="padding:10px 15px;">
                    <ul class="list-unstyled">
                        @foreach ($brands as $brand)
                            <li style="padding:6px 0;border-bottom:1px solid #eee;">
                                <a href="{{ url('brand/' . $brand->brand_id) }}">
                                    {{ $brand->brand_name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
