<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Hiển thị form tạo user
    public function create_user()
    {
        return view('admin.create_user');
    }

    // Lưu user mới
    public function save_user(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email:rfc,dns|unique:users,email',
            'password'  => 'required|string|min:6|confirmed',
            'address'   => 'nullable|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'role'      => ['required', Rule::in(['1','0'])], //role 1 = customer, 0 = admin
        ]);

        User::create([
            'name'      => $request->input('name'),
            'email'     => $request->input('email'),
            'password'  => Hash::make($request->input('password')),
            'address'   => $request->input('address'),
            'phone'     => $request->input('phone'),
            'role'      => $request->input('role', '1'), // mặc định là customer (1)
        ]);

        return redirect()->route('admin.users.create')->with('message', 'Tạo tài khoản thành công!');
    }

    public function all_users(Request $request)
    {
        $kw   = $request->input('q');   // đổi từ keyword -> q
        $role = $request->input('role');

        $query = User::query();

        if (!empty($kw)) {
            $query->where(function($q) use ($kw) {
                $q->where('id', 'LIKE', "%{$kw}%")
                ->orWhere('name', 'LIKE', "%{$kw}%")
                ->orWhere('email', 'LIKE', "%{$kw}%")
                ->orWhere('phone', 'LIKE', "%{$kw}%")
                ->orWhere('address', 'LIKE', "%{$kw}%");
            });
        }

        if ($role !== null && $role !== '') {
            $query->where('role', $role);
        }

        $users = $query->orderByDesc('id')
                    ->paginate(7)
                    ->appends($request->query());

        return view('admin.all_users', compact('users'));
    }


    public function edit_user($id)
    {
        $user = User::findOrFail($id);
        return view('admin.edit_user', compact('user'));
    }

    public function update_user(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name'    => 'required|string|max:255',
            'email'   => ['required','email', Rule::unique('users','email')->ignore($user->id)],
            'address' => 'nullable|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'role'    => ['required', Rule::in(['1','0'])], // ✅ thống nhất giống create
        ];
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6|confirmed';
        }
        $request->validate($rules);

        $user->fill([
            'name'    => $request->name,
            'email'   => $request->email,
            'address' => $request->address,
            'phone'   => $request->phone,
            'role'    => $request->role,
        ]);
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return redirect()->route('admin.users.index')->with('message', 'Cập nhật người dùng thành công!');
    }

    public function delete_user($id)
    {
        $user = User::findOrFail($id);

        // Không cho xoá chính mình
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Bạn không thể xoá tài khoản của chính mình.');
        }

        // Không cho xoá tài khoản quản trị
        if ((int) $user->role === User::ROLE_ADMIN) {
            return back()->with('error', 'Không thể xoá tài khoản quản trị.');
        }

        $user->delete();

        return back()->with('success', 'Đã xoá người dùng.');
    }
}
