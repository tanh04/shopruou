<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShippingFee;

class ShippingFeeController extends Controller
{
    public function index() {
        $fees = ShippingFee::all();
        return view('admin.shipping.index', compact('fees'));
    }

    public function store(Request $request) {
        $request->validate([
            'province' => 'required',
            'district' => 'required',
            'fee' => 'required|numeric|min:0'
        ]);

        ShippingFee::create($request->all());

        return redirect()->back()->with('success', 'Đã thêm phí ship');
    }

    public function update(Request $request, ShippingFee $shippingFee) {
        $shippingFee->update($request->all());
        return redirect()->back()->with('success', 'Cập nhật phí ship thành công');
    }

    public function destroy(ShippingFee $shippingFee) {
        $shippingFee->delete();
        return redirect()->back()->with('success', 'Xóa phí ship thành công');
    }
}
