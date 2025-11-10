<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Visitor;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

class DashboardController extends Controller
{
    public function index()
    {
        $now = now();

        // Visitors đang online trong 10 phút gần nhất
        $visitors_online = Visitor::where('visited_at', '>=', $now->copy()->subMinutes(10))
            ->distinct('session_id')->count('session_id');

        // Tổng user
        $users_total = User::count();

        // KPI 30 ngày: doanh thu & số đơn hoàn tất
        $from = $now->copy()->subDays(30)->startOfDay();
        $to   = $now->copy()->endOfDay();

        $agg = DB::table('orders as o')
            ->join('order_items as oi', 'oi.order_id', '=', 'o.order_id')
            ->where('o.status', Order::STATUS_COMPLETED) // nếu không có constant, thay bằng 'completed'
            ->whereBetween('o.created_at', [$from, $to])
            ->selectRaw('SUM(oi.quantity * oi.price) as sales')
            ->selectRaw('COUNT(DISTINCT o.order_id) as orders')
            ->first();

        $kpi = [
            'visitors' => (int) $visitors_online,
            'users'    => (int) $users_total,
            'sales'    => (int) ($agg->sales ?? 0),
            'orders'   => (int) ($agg->orders ?? 0),
        ];

        return view('admin.dashboard', compact('kpi'));
    }

    /** Area chart: Doanh thu & Lợi nhuận (30 ngày) sau thuế 5% */
    public function chartRevenueProfit()
    {
        $from = Carbon::now()->subDays(30)->startOfDay();
        $to   = Carbon::now()->endOfDay();

        $rows = DB::table('orders as o')
            ->join('order_items as oi', 'oi.order_id', '=', 'o.order_id')
            ->join('products as p', 'p.product_id', '=', 'oi.product_id')
            ->where('o.status', Order::STATUS_COMPLETED) // chỉ tính đơn hoàn tất
            ->whereBetween('o.created_at', [$from, $to])
            ->selectRaw('DATE(o.created_at) as d')
            ->selectRaw('SUM(oi.quantity * oi.price) * 1.05 as sales') // doanh thu sau thuế 5%
            ->selectRaw('SUM(oi.quantity * p.cost_price) as cost')     // tổng giá vốn
            ->selectRaw('SUM(oi.quantity * oi.price) * 1.05 - SUM(oi.quantity * p.cost_price) as profit') // lợi nhuận sau thuế
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        // map dữ liệu sang format Morris.Area chart
        $data = $rows->map(fn($r) => [
            'period' => $r->d,
            'sales'  => (int) $r->sales,
            'profit' => (int) $r->profit,
        ]);

        return response()->json($data);
    }



    /** Bar chart: Đơn hàng & Số lượng (30 ngày) */
    public function chartOrdersQty()
    {
        $from = Carbon::now()->subDays(30)->startOfDay();
        $to   = Carbon::now()->endOfDay();

        $rows = DB::table('orders as o')
            ->join('order_items as oi', 'oi.order_id', '=', 'o.order_id')
            ->where('o.status', Order::STATUS_COMPLETED)
            ->whereBetween('o.created_at', [$from, $to])
            ->selectRaw('DATE(o.created_at) as d')
            ->selectRaw('COUNT(DISTINCT o.order_id) as orders')
            ->selectRaw('SUM(oi.quantity) as quantity')
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        $data = $rows->map(fn($r) => [
            'period'   => $r->d,
            'orders'   => (int) $r->orders,
            'quantity' => (int) $r->quantity,
        ]);

        return response()->json($data);
    }

    /** Donut: Tỷ trọng danh mục theo số sản phẩm */
public function chartCategoriesShare()
{
    $rows = Category::query()
        ->leftJoin('products', 'products.category_id', '=', 'categories.category_id')
        // Nếu chỉ muốn đếm sản phẩm đang hiển thị: bỏ comment dòng dưới
        // ->where('products.product_status', 1)
        ->select('categories.category_name')
        ->selectRaw('COUNT(products.product_id) as total')
        ->groupBy('categories.category_id', 'categories.category_name')
        ->orderByDesc('total')
        ->get();

    // Trả đúng format Morris.Donut: [{label, value}, ...]
    $data = $rows->map(fn($r) => [
        'label' => (string) $r->category_name,
        'value' => (int) $r->total,
    ]);

    return response()->json($data->values());
}

public function chartBrandsShare()
{
    $rows = Brand::query()
        ->leftJoin('products', 'products.brand_id', '=', 'brands.brand_id')
        // Nếu chỉ muốn đếm sản phẩm đang hiển thị: bỏ comment dòng dưới
        // ->where('products.product_status', 1)
        ->select('brands.brand_name')
        ->selectRaw('COUNT(products.product_id) as total')
        ->groupBy('brands.brand_id', 'brands.brand_name')
        ->orderByDesc('total')
        ->get();

    $data = $rows->map(fn($r) => [
        'label' => (string) $r->brand_name,
        'value' => (int) $r->total,
    ]);

    return response()->json($data->values());
}


    /** Bar: Top 10 sản phẩm bán chạy (30 ngày) theo số lượng */
    public function chartTopProducts()
    {
        $from = Carbon::now()->subDays(30)->startOfDay();
        $to   = Carbon::now()->endOfDay();

        $rows = DB::table('order_items as oi')
            ->join('orders as o', 'o.order_id', '=', 'oi.order_id')
            ->join('products as p', 'p.product_id', '=', 'oi.product_id')
            ->where('o.status', Order::STATUS_COMPLETED)
            ->whereBetween('o.created_at', [$from, $to])
            ->groupBy('oi.product_id', 'p.product_name')
            ->select('p.product_name', DB::raw('SUM(oi.quantity) as qty'))
            ->orderByDesc('qty')
            ->limit(10)
            ->get();

        $data = $rows->map(fn($r) => ['product' => $r->product_name, 'qty' => (int) $r->qty]);

        return response()->json($data);
    }
}
