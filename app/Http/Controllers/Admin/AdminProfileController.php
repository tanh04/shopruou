<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\User;

class AdminProfileController extends Controller
{
        public function edit()
    {
        $user = Auth::user();
        abort_if(!$user || $user->role !== User::ROLE_ADMIN, 403);

        return view('Auth.admin_infor', compact('user'));
    }

    public function update(Request $request)
    { 
        $user = Auth::user();
        abort_if(!$user || $user->role !== User::ROLE_ADMIN, 403);

        $validated = $request->validate([
            'name'     => ['required','string','min:3','max:50'],
            'email'    => ['required','email', Rule::unique('users','email')->ignore($user->id)],
            'phone'    => ['nullable','regex:/^(0|\+84)\d{9,10}$/'],
            'address'  => ['nullable','string','min:5','max:255'],
            'password' => ['nullable','string','min:8','confirmed'],
        ],[
            'name.min'       => 'Họ tên phải từ 3 ký tự.',
            'address.min'    => 'Địa chỉ phải từ 5 ký tự.',
            'password.min'   => 'Mật khẩu tối thiểu 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'phone.regex'    => 'SĐT bắt đầu bằng 0 hoặc +84 và dài 10-11 số.',
        ]);

        $user->name    = $validated['name'];
        $user->email   = $validated['email'];
        $user->phone   = $validated['phone']   ?? null;
        $user->address = $validated['address'] ?? null;

        // Model User của bạn có casts 'password' => 'hashed', set thẳng là tự hash
        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return back()->with('success', 'Cập nhật hồ sơ quản trị thành công.');
    }
}
