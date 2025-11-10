<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Cart;
use App\Models\Conversation;

class AuthController extends Controller
{
    // Hiển thị form đăng ký
    public function showRegister()
    {
        return view('auth.register');
    }

    // Xử lý đăng ký người dùng
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 1, // mặc định là customer
            'address' => $request->address,
            'phone' => $request->phone,
        ]);

        // Gửi email xác thực
        $user->sendEmailVerificationNotification();
        
        Auth::login($user);
        return redirect()->route('verification.notice') ->with('success', 'Vui lòng kiểm tra email để xác thực tài khoản.');
    }

    // Hiển thị form đăng nhập
    public function showLogin()
    {
        return view('auth.login');
    }

    // Xử lý đăng nhập người dùng
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            $sessionId = $request->session()->getId();

            // Lấy cart theo session (khi chưa login)
            $sessionCart = Cart::where('session_id', $sessionId)
                                ->where('status', 'active')
                                ->first();

            // Lấy cart theo user
            $userCart = Cart::where('user_id', $user->id)
                            ->where('status', 'active')
                            ->first();

            if ($sessionCart) {
                if ($userCart) {
                    // Nếu user đã có giỏ → gộp sản phẩm từ session vào
                    foreach ($sessionCart->items as $item) {
                        $existingItem = $userCart->items()
                            ->where('product_id', $item->product_id)
                            ->first();

                        if ($existingItem) {
                            $existingItem->quantity += $item->quantity;
                            $existingItem->save();
                        } else {
                            $userCart->items()->create([
                                'product_id' => $item->product_id,
                                'quantity'   => $item->quantity,
                            ]);
                        }
                    }
                    // Xóa giỏ session
                    $sessionCart->items()->delete();
                    $sessionCart->delete();
                } else {
                    // Nếu user chưa có giỏ → gán luôn giỏ session cho user
                    $sessionCart->user_id = $user->id;
                    $sessionCart->session_id = null;
                    $sessionCart->save();
                }
            } else {
                // Nếu không có giỏ session và user cũng chưa có giỏ → tạo giỏ mới
                if (!$userCart) {
                    Cart::create([
                        'user_id' => $user->id,
                        'status' => 'active',
                    ]);
                }
            }

            // Phân quyền
            if ($user->role == 0) {
                return redirect()->route('dashboard');
            }

            return redirect()->intended('home')->with('success', 'Đăng nhập thành công!');
        }

        return back()->withErrors([
            'email' => 'Email hoặc mật khẩu không đúng.',
        ])->onlyInput('email');
    }
    // Xử lý đăng xuất người dùng
    public function logout(Request $request)
    {
        // Đóng hội thoại gắn với session hiện tại (nếu có)
        if ($request->session()->has('lc.conversation_id')) {
            Conversation::where('id', $request->session()->get('lc.conversation_id'))
                ->update(['status' => 'closed']);
        }

        Auth::logout();

        // Huỷ toàn bộ session -> sau logout mở site lại là 1 session mới ⇒ chat trống
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Đã đăng xuất');
    }

}
