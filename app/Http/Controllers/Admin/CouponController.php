<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\Order;
use Carbon\Carbon;

class CouponController extends Controller
{
    // Danh sách tất cả coupon
    public function all_coupons(Request $request)
    {
        $today = Carbon::today('Asia/Ho_Chi_Minh');

        // Auto tắt coupon hết hạn
        Coupon::where('status', 1)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', $today)
            ->update(['status' => 0]);

        // Lọc + tìm kiếm
        $keyword = $request->input('keyword');
        $status  = $request->input('status');

        $query = Coupon::orderByDesc('coupon_id');

        if (!empty($keyword)) {
            $query->where('coupon_code', 'like', "%$keyword%");
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        $all_coupons = $query->paginate(7)->appends($request->query());

        return view('admin.all_coupons', compact('all_coupons'));
    }

    // Hiển thị form thêm coupon
    public function create_coupon()
    {
        return view('admin.create_coupon');
    }

    // Lưu coupon mới (KHÔNG dùng coupon_condition)
    public function save_coupon(Request $request)
    {
        $request->validate([
            'coupon_code'      => 'required|unique:coupons,coupon_code|max:50',
            'coupon_quantity'  => 'required|integer|min:1',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount'  => 'nullable|numeric|min:0',
            'min_order_value'  => 'nullable|numeric|min:0',
            'status'           => 'required|in:0,1',
        ]);

        // Chuẩn hoá giá trị giảm: chỉ 1 loại có hiệu lực
        $percent = (float)($request->discount_percent ?? 0);
        $amount  = (float)($request->discount_amount  ?? 0);

        if ($percent > 0 && $amount > 0) {
            return back()->withInput()->withErrors([
                'discount_percent' => 'Chỉ nhập giảm % hoặc giảm số tiền, không phải cả hai.',
            ]);
        }
        if ($percent <= 0 && $amount <= 0) {
            return back()->withInput()->withErrors([
                'discount_percent' => 'Hãy nhập giảm % (0–100) hoặc giảm số tiền (> 0).',
            ]);
        }

        // Chỉ giữ 1 loại
        if ($percent > 0) { $amount = 0; } else { $percent = 0; }

        Coupon::create([
            'coupon_code'      => $request->coupon_code,
            'coupon_quantity'  => (int) $request->coupon_quantity,
            'start_date'       => $request->start_date,
            'end_date'         => $request->end_date,
            'discount_percent' => $percent,
            'discount_amount'  => $amount,
            'min_order_value'  => (float) ($request->min_order_value ?? 0),
            'status'           => (int) $request->status,
        ]);

        return redirect('all-coupons')->with('message', 'Thêm mã giảm giá thành công!');
    }

    // Hiển thị form sửa coupon
    public function edit_coupon($coupon_id)
    {
        $coupon = Coupon::findOrFail($coupon_id);
        return view('admin.edit_coupon', compact('coupon'));
    }

    // Cập nhật coupon (KHÔNG dùng coupon_condition)
    public function update_coupon(Request $request, $coupon_id)
    {
        $coupon = Coupon::findOrFail($coupon_id);

        $request->validate([
            'coupon_code'      => 'required|max:50|unique:coupons,coupon_code,' . $coupon_id . ',coupon_id',
            'coupon_quantity'  => 'required|integer|min:1',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount'  => 'nullable|numeric|min:0',
            'min_order_value'  => 'nullable|numeric|min:0',
            'status'           => 'nullable|in:0,1',
        ]);

        $percent = (float)($request->discount_percent ?? 0);
        $amount  = (float)($request->discount_amount  ?? 0);

        if ($percent > 0 && $amount > 0) {
            return back()->withInput()->withErrors([
                'discount_percent' => 'Chỉ nhập giảm % hoặc giảm số tiền, không phải cả hai.',
            ]);
        }
        if ($percent <= 0 && $amount <= 0) {
            return back()->withInput()->withErrors([
                'discount_percent' => 'Hãy nhập giảm % (0–100) hoặc giảm số tiền (> 0).',
            ]);
        }

        if ($percent > 0) { $amount = 0; } else { $percent = 0; }

        $coupon->update([
            'coupon_code'      => $request->coupon_code,
            'coupon_quantity'  => (int) $request->coupon_quantity,
            'start_date'       => $request->start_date ?? $coupon->start_date,
            'end_date'         => $request->end_date   ?? $coupon->end_date,
            'discount_percent' => $percent,
            'discount_amount'  => $amount,
            'min_order_value'  => (float) ($request->min_order_value ?? 0),
            // Nếu form edit có trường status thì cập nhật, còn không thì giữ nguyên
            'status'           => $request->filled('status') ? (int) $request->status : $coupon->status,
        ]);

        return redirect('all-coupons')->with('message', 'Cập nhật mã giảm giá thành công!');
    }

    // Xóa coupon
    public function delete_coupon($coupon_id)
    {
        $coupon = Coupon::findOrFail($coupon_id);

        // Kiểm tra có đơn hàng nào dùng coupon không
        $orderCount = Order::where('coupon_id', $coupon_id)->count();
        if ($orderCount > 0) {
            return redirect('all-coupons')->with('message', 'Không thể xóa! Đã có đơn hàng sử dụng mã này.');
        }

        $coupon->delete();
        return redirect('all-coupons')->with('message', 'Xóa mã giảm giá thành công!');
    }

    // Kích hoạt coupon
    public function active_coupon($coupon_id)
    {
        $today = Carbon::today('Asia/Ho_Chi_Minh');
        $coupon = Coupon::findOrFail($coupon_id);

        // Nếu coupon đã hết hạn thì không cho kích hoạt
        if ($coupon->end_date && Carbon::parse($coupon->end_date)->lt($today)) {
            return redirect('all-coupons')->with('error', 'Không thể kích hoạt! Mã giảm giá đã hết hạn.');
        }

        $coupon->status = 1;
        $coupon->save();

        return redirect('all-coupons')->with('message', 'Đã kích hoạt mã giảm giá!');
    }

    // Hủy kích hoạt coupon
    public function unactive_coupon($coupon_id)
    {
        $coupon = Coupon::findOrFail($coupon_id);
        $coupon->status = 0;
        $coupon->save();

        return redirect('all-coupons')->with('message', 'Đã hủy kích hoạt mã giảm giá!');
    }

}
