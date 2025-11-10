<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CustomerProfileController extends Controller
{
    public function profile()
    {
        return view('Auth.customer_infor', [
            'user'        => Auth::user(),
            'hideSlider'  => true,   // Ẩn slider theo layout bạn đang dùng
            'hideSidebar' => true,   // Ẩn sidebar
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'    => 'required|string|min:3|max:50',
            'email'   => 'required|email|unique:users,email,'.$user->id,
            'phone'   => ['nullable','regex:/^(0|\+84)\d{9,10}$/'],
            'address' => 'nullable|string|min:5|max:255',
            'password'=> ['nullable','confirmed','min:8'], // nếu có đổi mật khẩu
        ]);

        // Nếu không đổi mật khẩu thì bỏ ra:
        if (empty($validated['password'])) unset($validated['password']);

        $user->update($validated);

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

}
