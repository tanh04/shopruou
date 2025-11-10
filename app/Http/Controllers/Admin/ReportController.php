<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\User;
use App\Models\OrderItems;
use App\Models\Product;
use App\Models\Payment;
use App\Models\Category;
use App\Models\Brand;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Lấy filter (null nếu không có)
        $dateFrom = $request->input('date_from') ?: null;
        $dateTo   = $request->input('date_to')   ?: null;

        // Trạng thái mặc định (nếu bạn muốn khác thì đổi ở đây)
        $paidStatuses = $request->filled('status')
            ? (array) $request->input('status')
            : ['Hoàn thành'];

        // ==== Tổng số đơn (áp dụng filter ngày nếu có) ====
        $ordersBase = Order::query()
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo));

        $totalOrders = (clone $ordersBase)->count();

        // ==== Tổng khách hàng: hỗ trợ role là số (1) hoặc chuỗi ('customer') để an toàn ====
        $totalCustomers = User::where(function ($q) {
                $q->where('role', 1)
                  ->orWhere('role', 'customer');
            })
            // nếu muốn lọc số khách theo ngày tạo tài khoản uncomment 2 dòng dưới
            // ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            // ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->count();

        // ==== Doanh thu theo ngày ====
        $revenueByDate = Order::query()
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->when($paidStatuses, fn($q) => $q->whereIn('status', $paidStatuses))
            ->selectRaw('DATE(created_at) AS date, SUM(total_price) AS total_revenue, COUNT(*) AS order_count')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // ==== Doanh thu theo tháng (YYYY-MM) ====
        $revenueByMonth = Order::query()
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->when($paidStatuses, fn($q) => $q->whereIn('status', $paidStatuses))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") AS month, SUM(total_price) AS total_revenue, COUNT(*) AS order_count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // ==== Doanh thu theo năm ====
        $revenueByYear = Order::query()
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->when($paidStatuses, fn($q) => $q->whereIn('status', $paidStatuses))
            ->selectRaw('YEAR(created_at) AS year, SUM(total_price) AS total_revenue, COUNT(*) AS order_count')
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        // ==== Doanh thu theo danh mục (join order_items -> products) ====
        $categoryRevenue = OrderItems::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.product_id')
            ->join('categories', 'products.category_id', '=', 'categories.category_id')
            ->when($dateFrom, fn($q) => $q->whereDate('orders.created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('orders.created_at', '<=', $dateTo))
            ->when($paidStatuses, fn($q) => $q->whereIn('orders.status', $paidStatuses))
            ->select(
                'categories.category_id',
                'categories.category_name',
                DB::raw('SUM(order_items.price * order_items.quantity) AS total_revenue'),
                DB::raw('SUM(order_items.quantity) AS total_qty')
            )
            ->groupBy('categories.category_id', 'categories.category_name')
            ->orderByDesc('total_revenue')
            ->get();


        // ==== Doanh thu theo thương hiệu (join order_items -> products) ====
        $brandRevenue = OrderItems::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.product_id')
            ->join('brands', 'products.brand_id', '=', 'brands.brand_id')
            ->when($dateFrom, fn($q) => $q->whereDate('orders.created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('orders.created_at', '<=', $dateTo))
            ->when($paidStatuses, fn($q) => $q->whereIn('orders.status', $paidStatuses))
            ->select(
                'brands.brand_id',
                'brands.brand_name',
                DB::raw('SUM(order_items.price * order_items.quantity) AS total_revenue'),
                DB::raw('SUM(order_items.quantity) AS total_qty')
            )
            ->groupBy('brands.brand_id', 'brands.brand_name')
            ->orderByDesc('total_revenue')
            ->get();

        // ==== Doanh thu theo sản phẩm (join order_items -> products) ====
        $productRevenue = OrderItems::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.product_id')
            ->when($dateFrom, fn($q) => $q->whereDate('orders.created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('orders.created_at', '<=', $dateTo))
            ->when($paidStatuses, fn($q) => $q->whereIn('orders.status', $paidStatuses))
            ->select(
                'products.product_id',
                'products.product_name',
                DB::raw('SUM(order_items.price * order_items.quantity) AS total_revenue'),
                DB::raw('SUM(order_items.quantity) AS total_qty')
            )
            ->groupBy('products.product_id', 'products.product_name')
            ->orderByDesc('total_revenue')
            ->get();

        // ==== Phân bổ theo phương thức thanh toán (nếu có bảng payments) ====
        $paymentBreakdown = Order::query()
            ->leftJoin('payments', 'orders.payment_id', '=', 'payments.payment_id')
            ->when($dateFrom, fn($q) => $q->whereDate('orders.created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('orders.created_at', '<=', $dateTo))
            ->when($paidStatuses, fn($q) => $q->whereIn('orders.status', $paidStatuses))
            ->select('payments.payment_method', DB::raw('COUNT(*) AS order_count'), DB::raw('SUM(orders.total_price) AS total_revenue'))
            ->groupBy('payments.payment_method')
            ->orderByDesc('total_revenue')
            ->get();

        // ==== Trả về view: CHẮC CHẮN truyền tất cả biến view cần dùng ====
        return view('admin.revenue.reports', compact(
            'categoryRevenue',
            'brandRevenue',
            'productRevenue',
            'totalOrders',
            'totalCustomers',
            'revenueByDate',
            'revenueByMonth',
            'revenueByYear',
            'paymentBreakdown',
            'paidStatuses',
            'dateFrom',
            'dateTo'
        ));
    }
    public function stocksReport()
    {
        // Thống kê theo danh mục
        $categories = Category::select('categories.category_name as name', DB::raw('COUNT(products.product_id) as total'))
            ->leftJoin('products', 'categories.category_id', '=', 'products.category_id')
            ->groupBy('categories.category_id', 'categories.category_name')
            ->get();

        // Thống kê theo thương hiệu
        $brands = Brand::select('brands.brand_name as name', DB::raw('COUNT(products.product_id) as total'))
            ->leftJoin('products', 'brands.brand_id', '=', 'products.brand_id')
            ->groupBy('brands.brand_id', 'brands.brand_name')
            ->get();

        return view('admin.revenue.stocks_report', compact('categories', 'brands'));
    }
    // // ReportController
    // public function topCategoryBrand()
    // {
    //     $categories = Category::withCount('products')->get();
    //     $brands     = Brand::withCount('products')->get();
    //     return view('admin.report.top_category_brand', compact('categories','brands'));
    // }
}
